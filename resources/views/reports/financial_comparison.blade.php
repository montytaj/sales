<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => 'مقارنة الفترات المالية']
            ];
        @endphp
    </x-slot>

    <x-page-header title="شاشة مقارنة الفترات المالية" description="مقارنة الأداء المالي، المبيعات والمصروفات والربحية بين فترتين ماليتين مختلفتين">
        <x-slot name="actions">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('reports.financial-comparison', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير CSV
                </a>
                <a href="{{ route('reports.financial-comparison', array_merge(request()->all(), ['export' => 'print'])) }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i> طباعة التقرير
                </a>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Filters Form Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.financial-comparison') }}" class="row g-3 align-items-end">
                <!-- Period 1 -->
                <div class="col-12 col-md-5 bg-slate-50 p-3 rounded-3 border border-slate-200">
                    <h6 class="font-bold text-primary mb-2 fs-7"><i class="bi bi-calendar-range me-1"></i> الفترة الأولى (الفترة المرجعية / السابقة)</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fs-8 text-slate-600 mb-1">من تاريخ</label>
                            <input type="date" name="p1_from" value="{{ $p1_from }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-8 text-slate-600 mb-1">إلى تاريخ</label>
                            <input type="date" name="p1_to" value="{{ $p1_to }}" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <!-- Period 2 -->
                <div class="col-12 col-md-5 bg-primary-subtle p-3 rounded-3 border border-primary-subtle">
                    <h6 class="font-bold text-emerald-700 mb-2 fs-7"><i class="bi bi-calendar-check me-1"></i> الفترة الثانية (فترة المقارنة / الحالية)</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fs-8 text-slate-600 mb-1">من تاريخ</label>
                            <input type="date" name="p2_from" value="{{ $p2_from }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-8 text-slate-600 mb-1">إلى تاريخ</label>
                            <input type="date" name="p2_to" value="{{ $p2_to }}" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <!-- Branch & Actions -->
                <div class="col-12 col-md-2 d-flex flex-column gap-2">
                    @if(count($branches) > 0)
                        <div>
                            <label class="form-label fs-8 text-slate-600 mb-1">تصفية حسب الفرع</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">كافة الفروع</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (string)$branch_id === (string)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="d-flex gap-1 mt-auto">
                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">
                            <i class="bi bi-arrow-repeat me-1"></i> مقارنة
                        </button>
                        <a href="{{ route('reports.financial-comparison') }}" class="btn btn-light btn-sm text-slate-600 rounded-3" title="إعادة ضبط">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Variance Cards -->
    <div class="row g-3 mb-4">
        @foreach($metrics as $key => $m)
            @php
                $var = $m['variance'];
                $isIncrease = $var['diff'] >= 0;
                $isPositiveTrend = ($m['positive_is_good'] && $isIncrease) || (!$m['positive_is_good'] && !$isIncrease);
                $badgeBg = $isPositiveTrend ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle';
                $icon = $isIncrease ? 'bi-arrow-up-right' : 'bi-arrow-down-right';
            @endphp
            <div class="col-12 col-sm-6 col-lg">
                <div class="card card-custom h-100 border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fs-8 font-bold text-slate-500 uppercase">{{ $m['title'] }}</span>
                        <span class="badge border {{ $badgeBg }} rounded-pill px-2 py-1 fs-8 font-medium">
                            <i class="bi {{ $icon }} me-1"></i> {{ $var['percentage'] >= 0 ? '+' : '' }}{{ number_format($var['percentage'], 1) }}%
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <h4 class="font-bold text-slate-900 mb-0 fs-5">{{ number_format($m['p2'], 2) }} <small class="fs-8 text-slate-400">ر.س</small></h4>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-slate-100 fs-8 text-slate-500">
                        <span>السابقة: {{ number_format($m['p1'], 2) }} ر.س</span>
                        <span class="font-medium {{ $var['diff'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $var['diff'] >= 0 ? '+' : '' }}{{ number_format($var['diff'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Comparative Chart & Data Table -->
    <div class="row g-4 mb-5">
        <!-- Chart -->
        <div class="col-12 col-lg-7">
            <div class="card card-custom border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="font-bold text-slate-900 mb-0"><i class="bi bi-bar-chart-line text-primary me-2"></i> الرسم البياني المقارن</h6>
                        <small class="text-slate-500">مقارنة بصرية بين الفترة الأولى والثانية</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div style="height: 320px; position: relative;">
                        <canvas id="financialComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-12 col-lg-5">
            <div class="card card-custom border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h6 class="font-bold text-slate-900 mb-0"><i class="bi bi-table text-primary me-2"></i> تفاصيل الفروقات والنسب</h6>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-slate-100 fs-7">
                            <thead class="bg-slate-50 text-slate-700 font-semibold border-bottom">
                                <tr>
                                    <th class="ps-4">المؤشر</th>
                                    <th>الفترة 1</th>
                                    <th>الفترة 2</th>
                                    <th class="pe-4 text-end">التغير %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics as $m)
                                    @php
                                        $var = $m['variance'];
                                        $isIncrease = $var['diff'] >= 0;
                                        $isPositiveTrend = ($m['positive_is_good'] && $isIncrease) || (!$m['positive_is_good'] && !$isIncrease);
                                        $textColor = $isPositiveTrend ? 'text-success font-bold' : 'text-danger font-bold';
                                    @endphp
                                    <tr>
                                        <td class="ps-4 font-bold text-slate-800">{{ $m['title'] }}</td>
                                        <td class="text-slate-600">{{ number_format($m['p1'], 2) }}</td>
                                        <td class="font-bold text-slate-900">{{ number_format($m['p2'], 2) }}</td>
                                        <td class="pe-4 text-end {{ $textColor }}">
                                            {{ $var['percentage'] >= 0 ? '+' : '' }}{{ number_format($var['percentage'], 1) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('financialComparisonChart');
            if (!ctx) return;

            const labels = [
                'المبيعات',
                'المشتريات',
                'المصروفات',
                'مجمل الربح',
                'صافي الربح'
            ];

            const p1Values = [
                {{ $metrics['sales']['p1'] }},
                {{ $metrics['purchases']['p1'] }},
                {{ $metrics['expenses']['p1'] }},
                {{ $metrics['gross_profit']['p1'] }},
                {{ $metrics['net_profit']['p1'] }}
            ];

            const p2Values = [
                {{ $metrics['sales']['p2'] }},
                {{ $metrics['purchases']['p2'] }},
                {{ $metrics['expenses']['p2'] }},
                {{ $metrics['gross_profit']['p2'] }},
                {{ $metrics['net_profit']['p2'] }}
            ];

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'الفترة الأولى ({{ $p1_from }} إلى {{ $p1_to }})',
                            data: p1Values,
                            backgroundColor: 'rgba(148, 163, 184, 0.7)',
                            borderColor: '#64748b',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        {
                            label: 'الفترة الثانية ({{ $p2_from }} إلى {{ $p2_to }})',
                            data: p2Values,
                            backgroundColor: 'rgba(16, 185, 129, 0.75)',
                            borderColor: '#059669',
                            borderWidth: 1,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { family: 'Tajawal, sans-serif', size: 12 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw.toLocaleString('ar-SA') + ' ر.س';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
