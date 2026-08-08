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
        <div class="col-12 col-md-3">
            <label for="customer_id" class="form-label font-semibold fs-7 text-slate-700">العميل</label>
            <select name="customer_id" id="customer_id" class="form-select fs-7">
                <option value="">-- جميع العملاء --</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" {{ (string)request('customer_id') === (string)$c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-report-filter-bar>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="إجمالي الفواتير" 
                :value="number_format($total_invoices)" 
                icon="bi-receipt" 
                color="primary" 
                subtitle="عدد المستندات الصادرة" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="صافي المبيعات" 
                :value="number_format($total_net, 2)" 
                :currency="setting('currency', 'SAR')" 
                icon="bi-graph-up-arrow" 
                color="emerald" 
                subtitle="قبل الخصم والمستحق" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="ضريبة القيمة المضافة" 
                :value="number_format($total_tax, 2)" 
                :currency="setting('currency', 'SAR')" 
                icon="bi-percent" 
                color="info" 
                subtitle="الضريبة المستحقة" />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                title="المبالغ المتبقية" 
                :value="number_format($total_remaining, 2)" 
                :currency="setting('currency', 'SAR')" 
                icon="bi-exclamation-circle" 
                color="warning" 
                subtitle="غير مسددة" />
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
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الفرع</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">قبل الضريبة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">الضريبة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">الصافي</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">المدفوع</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الحالة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 pe-3">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 fs-7">
                    @forelse ($invoices as $inv)
                        <tr>
                            <td class="ps-3 font-mono font-bold text-slate-900">
                                <a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none hover-primary">{{ $inv->invoice_number }}</a>
                            </td>
                            <td class="font-semibold text-slate-800">{{ $inv->customer?->name }}</td>
                            <td class="text-slate-600">{{ $inv->branch?->name }}</td>
                            <td class="text-end font-mono text-slate-700 dir-ltr">{{ number_format($inv->subtotal, 2) }}</td>
                            <td class="text-end font-mono text-slate-700 dir-ltr">{{ number_format($inv->tax_amount, 2) }}</td>
                            <td class="text-end font-mono font-bold text-slate-900 dir-ltr">{{ number_format($inv->net_amount, 2) }}</td>
                            <td class="text-end font-mono text-success font-semibold dir-ltr">{{ number_format($inv->paid_amount, 2) }}</td>
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
