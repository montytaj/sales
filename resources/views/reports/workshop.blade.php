<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => __('reports.workshop') ?? 'تقارير الورشة وCNC']
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('reports.workshop_report') ?? 'تقرير تشغيل الورشة وماكينات CNC'" :description="__('reports.workshop_desc') ?? 'تحليل أوامر التشغيل، عدد الألواح القص، القطع الصالحة، ومعدلات الهالك'">
        <x-slot name="actions">
            <div class="d-flex gap-2">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-emerald-custom font-semibold shadow-sm fs-7">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> تصدير Excel
                </a>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Filter Bar -->
    <x-report-filter-bar 
        :action="route('reports.workshop')" 
        :branches="$branches"
        :statuses="[
            'pending' => 'بانتظار الموافقة',
            'authorized_to_start' => 'مصرح بالبدء',
            'in_progress' => 'قيد القص والتشغيل',
            'completed' => 'مكتمل العمل',
            'delivered' => 'تم التسليم'
        ]">
    </x-report-filter-bar>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <x-kpi-card title="إجمالي أوامر العمل" :value="$total_count" icon="bi-cpu" color="warning" subtitle="أوامر تشغيل" />
        </div>
        <div class="col-6 col-md-3">
            <x-kpi-card title="إجمالي الألواح المستخدمة" :value="$total_sheets" icon="bi-stack" color="primary" subtitle="ألواح خشب / مادة" />
        </div>
        <div class="col-6 col-md-3">
            <x-kpi-card title="القطع الصالحة" :value="$good_pieces" icon="bi-check-circle" color="emerald" subtitle="منتج نهائي" />
        </div>
        <div class="col-6 col-md-3">
            <x-kpi-card title="نسبة الهالك / التالف" :value="$waste_rate . '%'" icon="bi-trash" color="danger" :subtitle="$waste_pieces . ' قطعة تالفة'" />
        </div>
    </div>

    <!-- Workshop Analytics Chart Section -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card card-custom p-4 h-100 border-0 shadow-sm">
                <h6 class="font-bold text-slate-800 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-line-fill text-warning"></i>
                    تحليل كفاءة الإنتاج والقطع الناجحة مقابل الهالك
                </h6>
                <div class="position-relative" style="height: 200px;">
                    <canvas id="workshopEfficiencyChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card card-custom p-4 h-100 border-0 shadow-sm">
                <h6 class="font-bold text-slate-800 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart-fill text-info"></i>
                    نسبة الهالك الإجمالية
                </h6>
                <div class="position-relative d-flex align-items-center justify-content-center" style="height: 200px;">
                    <canvas id="workshopWastePieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx1 = document.getElementById('workshopEfficiencyChart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: ['القطع الناجحة', 'الهالك / التالف'],
                        datasets: [{
                            label: 'عدد القطع',
                            data: [{{ $good_pieces }}, {{ $waste_pieces }}],
                            backgroundColor: ['#10b981', '#ef4444'],
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

            const ctx2 = document.getElementById('workshopWastePieChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['ناجح', 'هالك'],
                        datasets: [{
                            data: [{{ max(1, $good_pieces) }}, {{ $waste_pieces }}],
                            backgroundColor: ['#10b981', '#ef4444'],
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

    <!-- Workshop Orders Table Card -->
    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-slate-50 border-bottom border-slate-200">
                    <tr>
                        <th scope="col" class="ps-3 text-slate-600 font-semibold fs-7">رقم أمر العمل</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">العميل</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الفرع</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">عدد الألواح</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">القطع الناجحة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الهالك / التالف</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الأولوية</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الحالة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 pe-3">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 fs-7">
                    @forelse ($workOrders as $order)
                        <tr>
                            <td class="ps-3 font-mono font-bold text-slate-900">
                                <a href="{{ route('work-orders.show', $order) }}" class="text-decoration-none hover-primary">{{ $order->work_order_number }}</a>
                            </td>
                            <td class="font-semibold text-slate-800">{{ $order->customer?->name }}</td>
                            <td class="text-slate-600">{{ $order->branch?->name }}</td>
                            <td class="font-mono text-slate-800 font-bold">{{ $order->sheet_count }}</td>
                            <td class="font-mono text-success font-bold">{{ $order->good_pieces ?? 0 }}</td>
                            <td class="font-mono text-danger font-bold">{{ $order->waste_pieces ?? 0 }}</td>
                            <td>
                                <span class="badge {{ $order->priority === 'urgent' ? 'bg-danger text-white' : 'bg-info-subtle text-info-emphasis' }}">
                                    {{ __('workshop.priorities.' . $order->priority) }}
                                </span>
                            </td>
                            <td>
                                <x-status-badge :status="$order->status" />
                            </td>
                            <td class="pe-3 font-mono text-slate-500 dir-ltr text-end text-md-start">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-0">
                                <x-empty-state icon="bi-cpu" title="لا توجد أوامر عمل" description="لم يتم العثور على أوامر تشغيل ورشة تطابق الفلترة المحددة." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($workOrders->hasPages())
            <div class="card-footer bg-white border-top border-slate-100 py-3">
                {{ $workOrders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
