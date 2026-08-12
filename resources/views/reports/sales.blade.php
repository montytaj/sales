<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => __('reports.sales') ?? 'تقرير المبيعات']
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('reports.sales_report') ?? 'تقرير المبيعات التفصيلي والضرائب'" :description="__('reports.sales_desc') ?? 'تحليل إجمالي فواتير المبيعات، الصافي، الضريبة المضافة، وحالات التحصيل'">
        <x-slot name="actions">
            <div class="d-flex gap-2">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-emerald-custom font-semibold shadow-sm fs-7">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> تصدير Excel
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'print']) }}" target="_blank" class="btn btn-secondary-custom font-semibold shadow-sm fs-7">
                    <i class="bi bi-printer-fill me-1"></i> طباعة / PDF
                </a>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Filter Bar -->
    <x-report-filter-bar 
        :action="route('reports.sales')" 
        :branches="$branches"
        :statuses="[
            'paid' => 'مسدد بالكامل',
            'partial' => 'مسدد جزئياً',
            'unpaid' => 'غير مسدد',
            'draft' => 'مسودة'
        ]">
        <div class="col-12 col-md-5">
            <label for="customer_id" class="form-label font-semibold fs-7 text-slate-700 mb-1">
                <i class="bi bi-person me-1 text-primary"></i> العميل
            </label>
            <select name="customer_id" id="customer_id" class="form-select fs-7 py-2 rounded-3 border-slate-300">
                <option value="">-- جميع العملاء --</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" {{ (string)request('customer_id') === (string)$c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-report-filter-bar>

    <!-- KPI Summary Row (3 Elements with col-12 col-md-4 spanning full 100% width) -->
    <div class="row g-3 mb-4 justify-content-center align-items-stretch">
        <div class="col-12 col-md-4">
            <x-kpi-card 
                title="تحصيل نقدي (كاش)" 
                :value="number_format($total_cash, 2)" 
                :currency="setting('currency', 'SAR')" 
                icon="bi-cash-stack" 
                color="emerald" 
                subtitle="مستلم بالخزائن" />
        </div>
        <div class="col-12 col-md-4">
            <x-kpi-card 
                title="تحصيل بنكي / شبكة" 
                :value="number_format($total_bank, 2)" 
                :currency="setting('currency', 'SAR')" 
                icon="bi-bank" 
                color="info" 
                subtitle="مستلم بالبنوك" />
        </div>
        <div class="col-12 col-md-4">
            <x-kpi-card 
                title="المبالغ الآجلة (المتبقية)" 
                :value="number_format($total_due, 2)" 
                :currency="setting('currency', 'SAR')" 
                icon="bi-exclamation-circle" 
                color="warning" 
                subtitle="مستحقة على العملاء" />
        </div>
    </div>

    <!-- Visual Analytics Chart Section -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card card-custom p-4 h-100 border-0 shadow-sm">
                <h6 class="font-bold text-slate-800 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-fill text-primary"></i>
                    توزيع قيم الفواتير والمحصل والمستحق
                </h6>
                <div class="position-relative" style="height: 220px;">
                    <canvas id="salesReportChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card card-custom p-4 h-100 border-0 shadow-sm">
                <h6 class="font-bold text-slate-800 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart-fill text-success"></i>
                    تراكم التحصيل
                </h6>
                <div class="position-relative d-flex align-items-center justify-content-center" style="height: 220px;">
                    <canvas id="salesCollectionPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx1 = document.getElementById('salesReportChart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: ['إجمالي الصافي', 'المحصول فعلياً', 'المتبقي المستحق'],
                        datasets: [{
                            label: 'المبلغ ({{ setting('currency', 'SAR') }})',
                            data: [{{ $total_net }}, {{ $total_net - $total_remaining }}, {{ $total_remaining }}],
                            backgroundColor: ['#2563eb', '#10b981', '#f59e0b'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            const ctx2 = document.getElementById('salesCollectionPieChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['المحصل', 'المتبقي'],
                        datasets: [{
                            data: [{{ max(0, $total_net - $total_remaining) }}, {{ $total_remaining }}],
                            backgroundColor: ['#10b981', '#f59e0b'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        cutout: '70%'
                    }
                });
            }
        });
    </script>

    <!-- Sales Table Card -->
    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable mb-0">
                <thead class="bg-slate-50 border-bottom border-slate-200">
                    <tr>
                        <th scope="col" class="ps-3 text-slate-600 font-semibold fs-7">رقم الفاتورة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">العميل</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">إجمالي الفاتورة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">كاش</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">بنك / شبكة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">آجل (متبقي)</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">طريقة الدفع والحسابات</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الحالة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 pe-3">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 fs-7">
                    @forelse ($invoices as $inv)
                        @php
                            $cAmt = $inv->payment_type === 'cash' ? ($inv->cash_amount > 0 ? $inv->cash_amount : $inv->total_amount) : ($inv->payment_type === 'split' ? $inv->cash_amount : 0);
                            $bAmt = $inv->payment_type === 'bank' ? ($inv->bank_amount > 0 ? $inv->bank_amount : $inv->total_amount) : ($inv->payment_type === 'split' ? $inv->bank_amount : 0);
                            $dAmt = $inv->payment_type === 'credit' ? ($inv->due_amount > 0 ? $inv->due_amount : $inv->total_amount) : ($inv->payment_type === 'split' ? $inv->due_amount : 0);
                        @endphp
                        <tr>
                            <td class="ps-3 font-mono font-bold text-slate-900">
                                <a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none hover-primary">{{ $inv->invoice_number }}</a>
                            </td>
                            <td class="font-semibold text-slate-800">{{ $inv->customer?->name }}</td>
                            <td class="text-end font-mono font-bold text-slate-900 dir-ltr">{{ number_format($inv->total_amount, 2) }}</td>
                            <td class="text-end font-mono text-success font-semibold dir-ltr">{{ number_format($cAmt, 2) }}</td>
                            <td class="text-end font-mono text-primary font-semibold dir-ltr">{{ number_format($bAmt, 2) }}</td>
                            <td class="text-end font-mono text-danger font-semibold dir-ltr">{{ number_format($dAmt, 2) }}</td>
                            <td>
                                @if($inv->payment_type === 'split')
                                    <span class="badge bg-info-subtle text-info border">مختلط (كاش + بنك + أجل)</span>
                                @elseif($inv->payment_type === 'cash')
                                    <span class="badge bg-success-subtle text-success border">كاش</span>
                                @elseif($inv->payment_type === 'bank')
                                    <span class="badge bg-primary-subtle text-primary border">بنك</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border">آجل</span>
                                @endif
                                @if($inv->cashAccount)
                                    <small class="d-block text-muted font-mono"><i class="bi bi-wallet2 me-1"></i>{{ $inv->cashAccount->name }}</small>
                                @endif
                                @if($inv->bankAccount)
                                    <small class="d-block text-muted font-mono"><i class="bi bi-bank me-1"></i>{{ $inv->bankAccount->name }}</small>
                                @endif
                            </td>
                            <td>
                                <x-status-badge :status="$inv->status" />
                            </td>
                            <td class="pe-3 font-mono text-slate-500 dir-ltr text-end text-md-start">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-0">
                                <x-empty-state icon="bi-receipt" title="لا توجد فواتير مبيعات" description="لم يتم العثور على فواتير مبيعات تطابق خيارات الفلترة المحددة." />
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="card-footer bg-white border-top border-slate-100 py-3">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
