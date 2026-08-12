<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => __('reports.projects') ?? 'تقارير المشاريع والعقود']
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('reports.projects_report') ?? 'تقرير إنجاز ومصروفات المشاريع والعقود'" :description="__('reports.projects_desc') ?? 'متابعة الميزانيات التقديرية، المصروفات الفعلية، ونسب الإنجاز لكل مشروع'">
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
        :action="route('reports.projects')" 
        :branches="$branches"
        :statuses="[
            'planning' => 'التخطيط والتحضير',
            'in_progress' => 'قيد التنفيذ الجاري',
            'completed' => 'مكتمل ومسلم',
            'on_hold' => 'متوقف مؤقتاً'
        ]">
    </x-report-filter-bar>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <x-kpi-card title="إجمالي المشاريع" :value="$total_projects" icon="bi-diagram-3" color="indigo" subtitle="مشروع مسجل" />
        </div>
        <div class="col-6 col-md-3">
            <x-kpi-card title="إجمالي الميزانيات" :value="number_format($total_budget, 2) . ' ' . setting('currency', 'SDG')" icon="bi-cash-stack" color="primary" subtitle="القيمة التعاقدية" />
        </div>
        <div class="col-6 col-md-3">
            <x-kpi-card title="إجمالي المصروفات الفعلية" :value="number_format($total_expenses, 2) . ' ' . setting('currency', 'SDG')" icon="bi-wallet2" color="warning" subtitle="التكاليف المسجلة" />
        </div>
        <div class="col-6 col-md-3">
            <x-kpi-card title="متوسط نسبة الإنجاز" :value="$avg_completion . '%'" icon="bi-speedometer2" color="emerald" subtitle="تقدم الأعمال" />
        </div>
    </div>

    <!-- Projects Analytics Chart Section -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card card-custom p-4 h-100 border-0 shadow-sm">
                <h6 class="font-bold text-slate-800 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-fill text-indigo"></i>
                    الميزانيات المعتمدة مقابل المصروفات التراكمية
                </h6>
                <div class="position-relative" style="height: 200px;">
                    <canvas id="projectsBudgetChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card card-custom p-4 h-100 border-0 shadow-sm">
                <h6 class="font-bold text-slate-800 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-speedometer text-success"></i>
                    مؤشر نسبة الإنجاز
                </h6>
                <div class="position-relative d-flex align-items-center justify-content-center" style="height: 200px;">
                    <canvas id="projectsCompletionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx1 = document.getElementById('projectsBudgetChart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: ['إجمالي الميزانيات التقديرية', 'المصروفات الفعلية المسجلة'],
                        datasets: [{
                            label: 'المبلغ ({{ setting('currency', 'SDG') }})',
                            data: [{{ $total_budget }}, {{ $total_expenses }}],
                            backgroundColor: ['#6366f1', '#f59e0b'],
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

            const ctx2 = document.getElementById('projectsCompletionChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['منجز', 'متبقي'],
                        datasets: [{
                            data: [{{ $avg_completion }}, {{ max(0, 100 - $avg_completion) }}],
                            backgroundColor: ['#10b981', '#e2e8f0'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        cutout: '75%'
                    }
                });
            }
        });
    </script>

    <!-- Projects Table Card -->
    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable mb-0">
                <thead class="bg-slate-50 border-bottom border-slate-200">
                    <tr>
                        <th scope="col" class="ps-3 text-slate-600 font-semibold fs-7">المشروع</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">العميل</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">الميزانية</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">المصروفات</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الإنجاز</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الحالة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 pe-3">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 fs-7">
                    @forelse ($projects as $proj)
                        <tr>
                            <td class="ps-3 font-bold text-slate-900">
                                <a href="{{ route('projects.show', $proj) }}" class="text-decoration-none hover-primary">{{ $proj->name }}</a>
                            </td>
                            <td class="font-semibold text-slate-800">{{ $proj->customer?->name }}</td>
                            <td class="text-end font-mono text-slate-800 font-bold dir-ltr">{{ number_format($proj->budget, 2) }}</td>
                            <td class="text-end font-mono text-warning font-semibold dir-ltr">{{ number_format($proj->total_expenses, 2) }}</td>
                            <td style="width: 140px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $proj->completion_percentage }}%"></div>
                                    </div>
                                    <span class="font-mono text-slate-700 font-semibold fs-8">{{ $proj->completion_percentage }}%</span>
                                </div>
                            </td>
                            <td>
                                <x-status-badge :status="$proj->status" />
                            </td>
                            <td class="pe-3 font-mono text-slate-500 dir-ltr text-end text-md-start">{{ $proj->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0">
                                <x-empty-state icon="bi-diagram-3" title="لا توجد مشاريع" description="لم يتم العثور على مشاريع تطابق الفلترة المحددة." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($projects->hasPages())
            <div class="card-footer bg-white border-top border-slate-100 py-3">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
