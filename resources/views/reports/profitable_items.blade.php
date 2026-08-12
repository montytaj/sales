<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => 'تقرير الأصناف الأكثر ربحية']
            ];
        @endphp
    </x-slot>

    <x-page-header title="تقرير الأصناف الأكثر ربحية" description="تحليل تفصيلي لربحية المنتجات والخدمات ونسب هامش الربح لإرشاد القرارات الإدارية والتسويقية">
        <x-slot name="actions">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('reports.profitable-items', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير CSV
                </a>
                <a href="{{ route('reports.profitable-items', array_merge(request()->all(), ['export' => 'print'])) }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i> طباعة التقرير
                </a>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Filter Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.profitable-items') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label fs-8 text-slate-600 mb-1">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fs-8 text-slate-600 mb-1">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm">
                </div>

                @if(isset($categories) && count($categories) > 0)
                    <div class="col-12 col-md-2">
                        <label class="form-label fs-8 text-slate-600 mb-1">التصنيف</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">جميع التصنيفات</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)request('category_id') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 col-md-2">
                    <label class="form-label fs-8 text-slate-600 mb-1">الترتيب حسب</label>
                    <select name="sort_by" class="form-select form-select-sm font-medium text-primary">
                        <option value="profit" {{ request('sort_by') == 'profit' ? 'selected' : '' }}>إجمالي الربح (أعلى ربح)</option>
                        <option value="margin" {{ request('sort_by') == 'margin' ? 'selected' : '' }}>نسبة هامش الربح %</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">
                        <i class="bi bi-filter me-1"></i> تصفية
                    </button>
                    <a href="{{ route('reports.profitable-items') }}" class="btn btn-light btn-sm text-slate-600 rounded-3">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card card-custom border-0 shadow-sm p-3">
                <span class="fs-8 font-bold text-slate-500 uppercase">إجمالي عدد الأصناف المباعة</span>
                <h4 class="font-bold text-slate-900 mb-0 mt-2 fs-4">{{ number_format($total_items_count) }}</h4>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card card-custom border-0 shadow-sm p-3">
                <span class="fs-8 font-bold text-slate-500 uppercase">إجمالي إيراد المبيعات</span>
                <h4 class="font-bold text-primary mb-0 mt-2 fs-4">{{ number_format($total_revenue, 2) }} <small class="fs-8">ر.س</small></h4>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card card-custom border-0 shadow-sm p-3">
                <span class="fs-8 font-bold text-slate-500 uppercase">إجمالي صافي أرباح الأصناف</span>
                <h4 class="font-bold text-emerald-600 mb-0 mt-2 fs-4">{{ number_format($total_profit, 2) }} <small class="fs-8">ر.س</small></h4>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card card-custom border-0 shadow-sm p-3">
                <span class="fs-8 font-bold text-slate-500 uppercase">متوسط هامش الربحية العام</span>
                <h4 class="font-bold text-amber-600 mb-0 mt-2 fs-4">%{{ number_format($avg_profit_margin, 1) }}</h4>
            </div>
        </div>
    </div>

    <!-- Top 10 Chart -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
            <h6 class="font-bold text-slate-900 mb-0"><i class="bi bi-trophy text-amber-500 me-2"></i> أعلى 10 أصناف تحقيقاً للأرباح</h6>
        </div>
        <div class="card-body p-4">
            <div style="height: 300px; position: relative;">
                <canvas id="topProfitableItemsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card card-custom border-0 shadow-sm mb-5">
        <div class="card-header bg-transparent border-bottom border-slate-100 py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="font-bold text-slate-900 mb-0"><i class="bi bi-list-stars text-primary me-2"></i> قائمة تحليل ربحية الأصناف التفصيلية</h6>
            <span class="badge bg-slate-100 text-slate-700 rounded-pill px-3 py-1 font-medium fs-8">مرتبة تنازلياً حسب الربحية</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border-slate-100 fs-7">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-bottom">
                        <tr>
                            <th class="ps-4">الصنف</th>
                            <th>التصنيف</th>
                            <th class="text-center">الكمية المباعة</th>
                            <th class="text-center">متوسط البيع</th>
                            <th class="text-center">التكلفة</th>
                            <th>الإيراد</th>
                            <th>التكلفة الإجمالية</th>
                            <th class="text-emerald-600 font-bold">الربح</th>
                            <th class="pe-4 text-end">الهامش %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                            @php
                                $margin = $row['profit_margin'];
                                $badgeClass = $margin >= 30 ? 'bg-success-subtle text-success border-success-subtle' : ($margin >= 10 ? 'bg-warning-subtle text-warning border-warning-subtle' : 'bg-danger-subtle text-danger border-danger-subtle');
                            @endphp
                            <tr>
                                <td class="ps-4 font-bold text-slate-900">{{ $row['item_name'] }}</td>
                                <td class="text-slate-600"><span class="badge bg-slate-100 text-slate-700 fs-8">{{ $row['category'] }}</span></td>
                                <td class="text-center font-medium">{{ number_format($row['sold_qty'], 1) }} <small class="fs-8 text-slate-400">{{ $row['unit_name'] }}</small></td>
                                <td class="text-center">{{ number_format($row['avg_selling_price'], 2) }}</td>
                                <td class="text-center text-slate-500">{{ number_format($row['cost_price'], 2) }}</td>
                                <td class="font-medium text-slate-800">{{ number_format($row['total_revenue'], 2) }} ر.س</td>
                                <td class="text-slate-500">{{ number_format($row['total_cost'], 2) }} ر.س</td>
                                <td class="font-bold text-emerald-600 fs-6">{{ number_format($row['profit'], 2) }} ر.س</td>
                                <td class="pe-4 text-end">
                                    <span class="badge border {{ $badgeClass }} rounded-pill px-2.5 py-1 font-bold">
                                        %{{ number_format($margin, 1) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-slate-400">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    لا توجد مبيعات أصناف مسجلة خلال الفترة المحددة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('topProfitableItemsChart');
            if (!ctx) return;

            const topItems = @json($top10);
            const labels = topItems.map(i => i.item_name);
            const profitValues = topItems.map(i => i.profit);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'إجمالي الربح (ر.س)',
                        data: profitValues,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: '#059669',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'الربح: ' + context.raw.toLocaleString('ar-SA') + ' ر.س';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        y: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
