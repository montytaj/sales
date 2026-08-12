<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => __('reports.inventory') ?? 'تقارير المخزون والمشتريات']
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('reports.inventory_report') ?? 'تقرير أرصدة وحركات المخزون والمشتريات'" :description="__('reports.inventory_desc') ?? 'متابعة كميات الأصناف بالمخازن، المواد منخفضة الرصيد، وحركات الإضافة والخصم'">
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
        :action="route('reports.inventory')" 
        :showDateRange="false"
        :showBranch="false">
        <div class="col-12 col-md-4">
            <label for="search" class="form-label font-semibold fs-7 text-slate-700">البحث</label>
            <input type="text" name="search" id="search" class="form-control fs-7" placeholder="اسم الصنف أو كود الصنف..." value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-3">
            <label for="category_id" class="form-label font-semibold fs-7 text-slate-700">تصنيف الصنف</label>
            <select name="category_id" id="category_id" class="form-select fs-7">
                <option value="">-- جميع التصنيفات --</option>
                @foreach(\App\Models\ItemCategory::where('is_active', true)->get() as $cat)
                    <option value="{{ $cat->id }}" {{ (string)request('category_id') === (string)$cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-report-filter-bar>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4 justify-content-center align-items-stretch">
        <div class="col-12 col-sm-6 col-lg-4">
            <x-kpi-card title="إجمالي الأصناف المسجلة" :value="$total_items" icon="bi-boxes" color="cyan" subtitle="أصناف مخزنية" />
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <x-kpi-card title="إجمالي الكميات المتوفرة" :value="number_format($total_stock_qty, 0)" icon="bi-stack" color="primary" subtitle="كمية رصيد المخزن الإجمالي" />
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <x-kpi-card title="أصناف قرب حد الخطر" :value="$low_stock_count" icon="bi-exclamation-triangle" color="danger" subtitle="تتطلب طلب شراء جديد" />
        </div>
    </div>

    <!-- Stock Table Card -->
    <div class="card card-custom overflow-hidden shadow-sm border-0 rounded-4 mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable mb-0">
                <thead class="bg-slate-50 border-bottom border-slate-200">
                    <tr>
                        <th scope="col" class="ps-3 text-slate-600 font-semibold fs-7">الصنف</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">التصنيف</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7">الوحدة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">الكمية الحالية</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">حد الطلب الأدنى</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 text-end">تكلفة الوحدة</th>
                        <th scope="col" class="text-slate-600 font-semibold fs-7 pe-3 text-center">حالة الرصيد</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 fs-7">
                    @forelse ($items as $item)
                        @php
                            $qty = max(0, (float)$item->warehouseItems->sum('qty_in_base_units'));
                            $minQty = (float)($item->min_stock_alert ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-3 font-bold text-slate-900">{{ $item->name }}</td>
                            <td><span class="badge bg-slate-100 text-slate-700">{{ $item->category?->name ?? '-' }}</span></td>
                            <td class="text-slate-600">{{ $item->baseUnit?->name ?? 'قطعة' }}</td>
                            <td class="text-end font-mono font-bold text-slate-900 dir-ltr">{{ number_format($qty, 0) }}</td>
                            <td class="text-end font-mono text-slate-500 dir-ltr">{{ number_format($minQty, 0) }}</td>
                            <td class="text-end font-mono text-slate-700 dir-ltr">{{ number_format($item->cost_price ?? 0, 2) }} ر.س</td>
                            <td class="pe-3 text-center">
                                @if ($qty <= $minQty)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-exclamation-triangle me-1"></i>منخفض المخزون</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>رصيد آمن</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0 text-center py-4">
                                <span class="text-muted">لم يتم العثور على أصناف مخزنية.</span>
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
