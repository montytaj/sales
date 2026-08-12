<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => 'تقرير جرد المخزن']
            ];
        @endphp
    </x-slot>

    <x-page-header title="تقرير جرد المخزن التفصيلي" description="تقرير شامل لحركة وجرد الأصناف بالفرق بين الرصيد الافتتاحي والوارد والمنصرف والقيمة الإجمالية للمخزون">
        <x-slot name="actions">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('reports.warehouse-inventory', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-emerald-custom font-semibold shadow-sm fs-7 rounded-3">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> تصدير Excel
                </a>
                <a href="{{ route('reports.warehouse-inventory', array_merge(request()->all(), ['export' => 'print'])) }}" target="_blank" class="btn btn-primary-custom font-semibold shadow-sm fs-7 rounded-3">
                    <i class="bi bi-printer-fill me-1"></i> طباعة التقرير
                </a>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Filter Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.warehouse-inventory') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="warehouse_id" class="form-label font-semibold fs-7 text-slate-700">
                        <i class="bi bi-building me-1 text-primary"></i> اختر المخزن
                    </label>
                    <select name="warehouse_id" id="warehouse_id" class="form-select fs-7">
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)$selected_warehouse_id === (string)$wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} {{ $wh->code ? "({$wh->code})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label for="from_date" class="form-label font-semibold fs-7 text-slate-700">
                        <i class="bi bi-calendar-event me-1 text-primary"></i> من تاريخ (بداية النظام)
                    </label>
                    <input type="date" name="from_date" id="from_date" class="form-control fs-7" value="{{ $from_date }}">
                </div>

                <div class="col-12 col-md-2">
                    <label for="to_date" class="form-label font-semibold fs-7 text-slate-700">
                        <i class="bi bi-calendar-check me-1 text-primary"></i> إلى تاريخ (اليوم)
                    </label>
                    <input type="date" name="to_date" id="to_date" class="form-control fs-7" value="{{ $to_date }}">
                </div>

                <div class="col-12 col-md-2">
                    <label for="category_id" class="form-label font-semibold fs-7 text-slate-700">التصنيف</label>
                    <select name="category_id" id="category_id" class="form-select fs-7">
                        <option value="">-- جميع التصنيفات --</option>
                        @foreach (\App\Models\ItemCategory::where('is_active', true)->orderBy('name')->get() as $cat)
                            <option value="{{ $cat->id }}" {{ (string)request('category_id') === (string)$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="search" class="form-label font-semibold fs-7 text-slate-700">البحث بالأسم أو الكود</label>
                    <input type="text" name="search" id="search" class="form-control fs-7" placeholder="اسم الصنف أو كود الصنف..." value="{{ request('search') }}">
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-3 pt-2 border-top border-slate-100">
                    <button type="submit" class="btn btn-primary-custom shadow-sm font-semibold fs-7 px-4">
                        <i class="bi bi-filter me-1"></i> عرض التقرير
                    </button>
                    <a href="{{ route('reports.warehouse-inventory') }}" class="btn btn-secondary-custom font-semibold fs-7 px-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> إعادة ضبط
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Warehouse Info Badge & Date Alert -->
    <div class="alert alert-primary bg-primary-subtle border-primary-subtle d-flex align-items-center justify-content-between mb-4 rounded-3 p-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
            <div>
                <span class="font-bold text-slate-900">المخزن الحالي للجرد:</span>
                <span class="badge bg-primary text-white fs-7 px-3 py-1 ms-1">{{ $selected_warehouse?->name ?? 'جميع المخازن' }}</span>
                <span class="text-slate-600 fs-7 ms-2">| الفترة من: <strong class="text-slate-800">{{ $from_date }}</strong> إلى <strong class="text-slate-800">{{ $to_date }}</strong></span>
            </div>
        </div>
        <small class="text-slate-500 font-medium">التاريخ الافتراضي يبدأ من أول تاريخ حركة بالنظام وحتى اليوم</small>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <x-kpi-card title="عدد أصناف المخزن" :value="$total_items_count" icon="bi-boxes" color="cyan" subtitle="أصناف مسجلة بالمخزن" />
        </div>
        <div class="col-12 col-md-4">
            <x-kpi-card title="إجمالي الكميات المتوفرة" :value="number_format($total_stock_qty, 2)" icon="bi-stack" color="primary" subtitle="مجموع كميات الصافي بالمخزن" />
        </div>
        <div class="col-12 col-md-4">
            <x-kpi-card title="إجمالي تقييم المخزون" :value="number_format($total_valuation, 2) . ' ر.س'" icon="bi-currency-dollar" color="emerald" subtitle="القيمة الإجمالية للكمية المتاحة" />
        </div>
    </div>

    <!-- Stock Table Card -->
    <div class="card card-custom overflow-hidden shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom border-slate-100 d-flex justify-content-between align-items-center">
            <h6 class="font-bold text-slate-800 mb-0">
                <i class="bi bi-clipboard-data text-primary me-2"></i> كشف جرد الأصناف بالمخزن
            </h6>
            <span class="badge bg-slate-100 text-slate-700">عدد النتائج: {{ $items->total() }} صنف</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable mb-0">
                <thead class="bg-slate-50 border-bottom border-slate-200">
                    <tr>
                        <th scope="col" class="ps-3 text-slate-600 font-semibold fs-7" style="min-width: 240px; width: 30%;">اسم الصنف</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">التصنيف</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الوحدة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">الرصيد الافتتاحي</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end text-success">الوارد (+)</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end text-danger">المنصرف (-)</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end font-bold text-primary">الرصيد المتاح</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">تكلفة الوحدة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 pe-3 text-end">إجمالي التقييم</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 fs-7">
                    @forelse ($items as $item)
                        <tr>
                            <td class="ps-3 font-bold text-slate-900">{{ $item['name'] }}</td>
                            <td><span class="badge bg-slate-100 text-slate-700">{{ $item['category_name'] }}</span></td>
                            <td class="text-slate-600">{{ $item['unit_name'] }}</td>
                            <td class="text-end font-mono text-slate-600 dir-ltr">{{ number_format($item['opening_qty'], 2) }}</td>
                            <td class="text-end font-mono text-success font-semibold dir-ltr">{{ number_format($item['in_qty'], 2) }}</td>
                            <td class="text-end font-mono text-danger font-semibold dir-ltr">{{ number_format($item['out_qty'], 2) }}</td>
                            <td class="text-end font-mono font-bold text-slate-900 bg-primary-subtle px-2 py-1 rounded dir-ltr">
                                {{ number_format($item['available_qty'], 2) }}
                            </td>
                            <td class="text-end font-mono text-slate-700 dir-ltr">{{ number_format($item['unit_cost'], 2) }} ر.س</td>
                            <td class="pe-3 text-end font-mono font-bold text-emerald-700 dir-ltr">
                                {{ number_format($item['total_valuation'], 2) }} ر.س
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-slate-400"></i>
                                لم يتم العثور على أي أصناف مخزنية ضمن المعايير المحددة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
