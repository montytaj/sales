<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-card-checklist text-primary me-2"></i>جرد الأصناف وكارت الحركة
                </h2>
                <p class="text-muted fs-7 mb-0">عرض كميات الصنف المتاحة في كل مخزن وسجل حركاته وتفاصيل تقييمه المالي</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary font-bold rounded-3 px-3 py-2 fs-7 shadow-2xs">
                    <i class="bi bi-arrow-right me-1.5"></i>دليل وإدارة الأصناف
                </a>
                @if($selectedItem)
                    <a href="{{ route('inventory.item-card', array_merge(request()->all(), ['export' => 'print'])) }}" target="_blank" class="btn btn-outline-primary font-bold rounded-3 px-3 py-2 fs-7 shadow-2xs">
                        <i class="bi bi-printer me-1.5"></i>طباعة كارت الجرد
                    </a>
                    <a href="{{ route('inventory.item-card', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-outline-success font-bold rounded-3 px-3 py-2 fs-7 shadow-2xs">
                        <i class="bi bi-file-earmark-spreadsheet me-1.5"></i>تصدير CSV
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <!-- Search & Selection Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('inventory.item-card') }}" id="itemCardFilterForm">
                <div class="row g-3 align-items-end">
                    <!-- Item Select -->
                    <div class="col-md-5 col-lg-4">
                        <label class="form-label font-bold text-slate-700 fs-7 mb-1">
                            <i class="bi bi-box-seam me-1 text-primary"></i>اختر الصنف المراد جرده:
                        </label>
                        <select name="item_id" class="form-select form-select-lg font-bold border-slate-300 shadow-xs" onchange="this.form.submit()">
                            <option value="">-- اختر الصنف المراد جرده --</option>
                            @foreach($allItems as $itemOpt)
                                <option value="{{ $itemOpt->id }}" {{ $selectedItemId == $itemOpt->id ? 'selected' : '' }}>
                                    📦 {{ $itemOpt->name }} (كود: {{ $itemOpt->code ?? $itemOpt->item_code ?? $itemOpt->id }}) {{ $itemOpt->barcode ? ' - باركود: ' . $itemOpt->barcode : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Warehouse filter for movements -->
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label font-bold text-slate-700 fs-7 mb-1">المخزن (للحركات):</label>
                        <select name="warehouse_id" class="form-select font-medium shadow-xs" onchange="this.form.submit()">
                            <option value="">جميع المخازن</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    🏢 {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="col-md-2 col-lg-2">
                        <label class="form-label font-bold text-slate-700 fs-7 mb-1">من تاريخ:</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control font-medium shadow-xs" onchange="this.form.submit()">
                    </div>

                    <!-- Date To -->
                    <div class="col-md-2 col-lg-2">
                        <label class="form-label font-bold text-slate-700 fs-7 mb-1">إلى تاريخ:</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control font-medium shadow-xs" onchange="this.form.submit()">
                    </div>

                    <!-- Submit & Reset buttons -->
                    <div class="col-md-12 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary font-bold w-100 py-2 rounded-3 shadow-2xs">
                            <i class="bi bi-search me-1"></i>عرض
                        </button>
                        @if(request()->anyFilled(['from_date', 'to_date', 'warehouse_id', 'movement_type', 'item_id']))
                            <a href="{{ route('inventory.item-card') }}" class="btn btn-light border font-bold py-2 rounded-3 text-muted" title="إعادة تعيين">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!$selectedItem)
        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
            <div class="card-body py-5">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center p-4 mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-search fs-1"></i>
                </div>
                <h5 class="font-bold text-slate-800 mb-2">يرجى تحديد صنف لمشاهدة بيانات الجرد والتفاصيل</h5>
                <p class="text-muted fs-7 mb-0">اختر أحد الأصناف من القائمة المنسدلة أعلاه ثم اضغط على زر <strong>(عرض)</strong> لإظهار كمياته في المخازن وسجل حركاته بالكامل.</p>
            </div>
        </div>
    @else

        <!-- Executive KPI Cards Grid (Matching Dashboard Unified Style) -->
        <div class="row g-3.5 mb-4">
            <!-- Card 1: Total Stock -->
            <div class="col-12 col-sm-6 col-xl-3">
                <x-kpi-card 
                    title="إجمالي رصيد الصنف المتوفر"
                    :value="number_format($totalBaseStock, 2)"
                    :currency="$selectedItem->baseUnit?->name ?? $selectedItem->unit ?? 'قطعة'"
                    :subtitle="$selectedItem->formatted_stock"
                    icon="bi-boxes"
                    color="primary"
                    :footerText="app()->getLocale() == 'ar' ? 'متوفر بكافة المخازن' : 'Total Stock'"
                    infoTooltip="إجمالي رصيد الصنف المتوفر بكافة المخازن" />
            </div>

            <!-- Card 2: Total Valuation -->
            <div class="col-12 col-sm-6 col-xl-3">
                <x-kpi-card 
                    title="إجمالي التقييم بسعر التكلفة"
                    :value="number_format($totalValuation, 2)"
                    :currency="setting('currency', 'SDG')"
                    :subtitle="'سعر التكلفة: ' . number_format((float)$selectedItem->cost_price, 2) . ' / وحدة'"
                    icon="bi-cash-stack"
                    color="emerald"
                    :footerText="app()->getLocale() == 'ar' ? 'بناءً على سعر التكلفة' : 'At Cost Price'"
                    infoTooltip="إجمالي تقييم رصيد الصنف بناءً على سعر التكلفة الفردي" />
            </div>

            <!-- Card 3: Active Warehouses -->
            <div class="col-12 col-sm-6 col-xl-3">
                <x-kpi-card 
                    title="المخازن المتوفر بها كميات"
                    :value="number_format($warehousesWithStockCount)"
                    :subtitle="'من إجمالي ' . $warehouses->count() . ' مخزن نشط'"
                    icon="bi-buildings"
                    color="info"
                    :footerText="app()->getLocale() == 'ar' ? 'موزعة حسب المخازن' : 'Warehouse Count'"
                    infoTooltip="عدد المخازن التي يتوفر بها رصيد موجب للصنف" />
            </div>

            <!-- Card 4: Stock Status -->
            <div class="col-12 col-sm-6 col-xl-3">
                <x-kpi-card 
                    title="حالة المخزون والتنبيه"
                    :value="$stockStatus === 'safe' ? 'رصيد آمن' : ($stockStatus === 'low' ? 'منخفض' : 'نفذ الرصيد')"
                    :subtitle="'حد التنبيه: ' . number_format((float)$selectedItem->min_stock_alert, 2) . ' ' . ($selectedItem->baseUnit?->name ?? 'وحدة')"
                    :icon="$stockStatus === 'safe' ? 'bi-shield-check' : ($stockStatus === 'low' ? 'bi-exclamation-triangle' : 'bi-x-circle')"
                    :color="$stockStatus === 'safe' ? 'emerald' : ($stockStatus === 'low' ? 'warning' : 'danger')"
                    :badgeText="$stockStatus === 'safe' ? 'آمن' : ($stockStatus === 'low' ? 'تنبيه' : 'خالي')"
                    :footerText="app()->getLocale() == 'ar' ? 'مقارنة بحد الطلب الأدنى' : 'Alert Level'"
                    infoTooltip="حالة الرصيد مقارنة بحد الطلب الأدنى" />
            </div>
        </div>

        <!-- Row 1: Item Specs Card ("بيانات ومواصفات الصنف") - Full Width -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom border-slate-100 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2.5 d-inline-flex">
                        <i class="bi bi-info-circle fs-5"></i>
                    </div>
                    <div>
                        <h5 class="font-black text-slate-900 mb-0.5">{{ $selectedItem->name }}</h5>
                        <small class="text-muted fs-7">
                            كود الصنف: <strong class="text-slate-800 font-mono">{{ $selectedItem->code ?? $selectedItem->item_code ?? '-' }}</strong>
                            @if($selectedItem->barcode)
                                | الباركود: <strong class="text-slate-800 font-mono">{{ $selectedItem->barcode }}</strong>
                            @endif
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-slate-100 text-slate-700 px-3 py-2 rounded-pill font-bold fs-7">
                        <i class="bi bi-tag me-1"></i>{{ $selectedItem->category?->name ?? 'بدون تصنيف' }}
                    </span>
                    <a href="{{ route('inventory.edit', $selectedItem->id) }}" class="btn btn-sm btn-outline-secondary font-bold rounded-3 px-3 py-1.5 fs-7">
                        <i class="bi bi-pencil me-1"></i>تعديل البيانات
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 text-slate-700 fs-7">
                    <!-- Unit & Conversion -->
                    <div class="col">
                        <div class="p-3 bg-slate-50 rounded-3 h-100 border border-slate-100">
                            <span class="text-muted fs-8 font-bold d-block mb-1"><i class="bi bi-ruler me-1"></i>وحدات القياس والتحويل:</span>
                            <div class="font-bold text-slate-800">
                                أساسية: {{ $selectedItem->baseUnit?->name ?? $selectedItem->unit ?? 'قطعة' }}
                            </div>
                            @if($selectedItem->wholesaleUnit)
                                <div class="font-bold text-slate-800 mt-1">
                                    كبرى: {{ $selectedItem->wholesaleUnit->name }}
                                </div>
                            @endif
                            @if($selectedItem->conversion_factor > 1)
                                <div class="text-primary font-bold fs-8 mt-1">
                                    1 {{ $selectedItem->wholesaleUnit?->name }} = {{ (float)$selectedItem->conversion_factor }} {{ $selectedItem->baseUnit?->name }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Pricing Breakdown -->
                    <div class="col">
                        <div class="p-3 bg-slate-50 rounded-3 h-100 border border-slate-100">
                            <span class="text-muted fs-8 font-bold d-block mb-1"><i class="bi bi-currency-dollar me-1"></i>أسعار التكلفة والبيع:</span>
                            <div class="font-bold text-slate-900">
                                التكلفة: {{ number_format((float)$selectedItem->cost_price, 2) }} {{ setting('currency', 'SDG') }}
                            </div>
                            <div class="font-bold text-emerald-600 mt-1">
                                سعر القطاعي: {{ number_format((float)$selectedItem->retail_price, 2) }} {{ setting('currency', 'SDG') }}
                            </div>
                            @if((float)$selectedItem->wholesale_price > 0)
                                <div class="text-slate-600 font-medium fs-8 mt-1">
                                    سعر الجملة: {{ number_format((float)$selectedItem->wholesale_price, 2) }} {{ setting('currency', 'SDG') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Stock Status & Alert Level -->
                    <div class="col">
                        <div class="p-3 bg-slate-50 rounded-3 h-100 border border-slate-100">
                            <span class="text-muted fs-8 font-bold d-block mb-1"><i class="bi bi-bell me-1"></i>حد التنبيه والتفعيل:</span>
                            <div class="font-bold text-slate-800">
                                حد الطلب الأدنى: {{ number_format((float)$selectedItem->min_stock_alert, 2) }} {{ $selectedItem->baseUnit?->name }}
                            </div>
                            <div class="mt-2">
                                <span class="badge {{ $selectedItem->is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} font-bold px-2.5 py-1 rounded-3">
                                    {{ $selectedItem->is_active ? 'الصنف مفعل بالمخزون' : 'الصنف غير مفعل' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details / Description -->
                    <div class="col">
                        <div class="p-3 bg-slate-50 rounded-3 h-100 border border-slate-100">
                            <span class="text-muted fs-8 font-bold d-block mb-1"><i class="bi bi-card-text me-1"></i>الوصف والملاحظات:</span>
                            <p class="text-slate-600 fs-7 mb-0 text-wrap">
                                {{ $selectedItem->description ?: 'لا توجد ملاحظات إضافية مسجلة على الصنف.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Warehouses Stock Table ("كميات الصنف المتوفرة في المخازن التفصيلية") - Full Width -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom border-slate-100 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="font-bold text-slate-900 mb-0 fs-6">
                    <i class="bi bi-building text-primary me-2"></i>كميات الصنف المتوفرة في المخازن التفصيلية
                </h6>
                <span class="badge bg-primary-subtle text-primary font-bold px-3 py-1.5 rounded-pill fs-7">
                    إجمالي الرصيد: {{ number_format($totalBaseStock, 2) }} {{ $selectedItem->baseUnit?->name ?? 'وحدة' }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-slate-50 text-slate-700 fs-7 font-bold border-bottom">
                            <tr>
                                <th class="py-3 ps-4">#</th>
                                <th class="py-3">اسم المخزن</th>
                                <th class="py-3">الفرع</th>
                                <th class="py-3 text-center">الكمية بالوحدة الأساسية</th>
                                <th class="py-3">تفاصيل الكمية (جملة + تجزئة)</th>
                                <th class="py-3 text-end pe-4">التقييم بسعر التكلفة</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($warehouseStock as $index => $row)
                                <tr class="bg-white">
                                    <td class="ps-4 font-medium text-slate-400">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary-subtle text-primary p-1.5 fs-7 d-inline-flex">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-800 d-block">{{ $row['warehouse']->name }}</span>
                                                <small class="text-muted font-mono fs-8">كود: {{ $row['warehouse']->code }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-slate-100 text-slate-700 font-medium">
                                            {{ $row['warehouse']->branch?->name ?? 'المركز الرئيسي' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-black fs-6 text-slate-900">
                                            {{ number_format($row['qty_in_base'], 2) }}
                                        </span>
                                        <small class="text-muted d-block fs-8">{{ $selectedItem->baseUnit?->name ?? 'قطعة' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-3 font-bold fs-7">
                                            <i class="bi bi-box-seam me-1"></i>{{ $row['formatted_stock'] }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="font-bold text-emerald-600">
                                            {{ number_format($row['valuation'], 2) }} {{ setting('currency', 'SDG') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-info-circle me-1"></i>لا تتوفر أي كميات لهذا الصنف في أي مخزن حالياً (الرصيد المتوفر 0).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-100 border-top font-bold text-slate-900 fs-7">
                            <tr>
                                <td colspan="3" class="ps-4 py-3">الإجمالي العام في جميع المخازن:</td>
                                <td class="text-center text-primary fs-6">{{ number_format($totalBaseStock, 2) }} {{ $selectedItem->baseUnit?->name }}</td>
                                <td>{{ $selectedItem->formatted_stock }}</td>
                                <td class="text-end pe-4 text-emerald-600 fs-6">{{ number_format($totalValuation, 2) }} {{ setting('currency', 'SDG') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Movements Log Card ("سجل حركة كارت الصنف") -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom border-slate-100 py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="font-bold text-slate-900 mb-0 fs-6">
                        <i class="bi bi-clock-history text-primary me-2"></i>سجل حركات كارت الصنف التفصيلي
                    </h6>
                    <small class="text-muted fs-8">تتبع جميع عمليات التوريد والصرف والتحويلات والتسويات التي تمت على الصنف</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-slate-100 text-slate-700 font-bold fs-7">
                        إجمالي الحركات المسجلة: {{ $movements->count() }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-slate-50 text-slate-700 fs-7 font-bold border-bottom">
                            <tr>
                                <th class="py-3 ps-4">#</th>
                                <th class="py-3">التاريخ والوقت</th>
                                <th class="py-3">المخزن</th>
                                <th class="py-3 text-center">نوع الحركة</th>
                                <th class="py-3 text-center">الكمية بالحركة</th>
                                <th class="py-3">نوع المستند والرمز</th>
                                <th class="py-3">البيان / الملاحظات</th>
                                <th class="py-3 text-end pe-4">المستخدم المسؤول</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($movements as $index => $m)
                                <tr>
                                    <td class="ps-4 font-medium text-slate-400">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="font-bold text-slate-800 d-block">{{ $m->created_at?->format('Y-m-d') }}</span>
                                        <small class="text-muted font-mono fs-8">{{ $m->created_at?->format('H:i:s A') }}</small>
                                    </td>
                                    <td>
                                        <span class="font-bold text-slate-800">🏢 {{ $m->warehouse?->name ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($m->movement_type === 'in')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-3 font-bold fs-8">
                                                <i class="bi bi-arrow-down-left me-1"></i>{{ __('inventory.movement_types.in') }}
                                            </span>
                                        @elseif($m->movement_type === 'out')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-3 font-bold fs-8">
                                                <i class="bi bi-arrow-up-right me-1"></i>{{ __('inventory.movement_types.out') }}
                                            </span>
                                        @elseif($m->movement_type === 'transfer')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-3 font-bold fs-8">
                                                <i class="bi bi-arrow-left-right me-1"></i>{{ __('inventory.movement_types.transfer') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-3 font-bold fs-8">
                                                {{ $m->movement_type }}
                                            </span>
                                        @endif
                                    </td>
                                    @php
                                        $factor = (float)($selectedItem->conversion_factor ?? 1);
                                        $wholesaleQty = ($factor > 1) ? floor($m->quantity / $factor) : 0;
                                        $remBase = ($factor > 1) ? fmod($m->quantity, $factor) : 0;
                                    @endphp
                                    <td class="text-center">
                                        <span class="font-black fs-6 {{ $m->movement_type === 'in' ? 'text-success' : ($m->movement_type === 'out' ? 'text-danger' : 'text-info') }}">
                                            {{ $m->movement_type === 'in' ? '+' : ($m->movement_type === 'out' ? '-' : '') }}{{ number_format($m->quantity, 2) }}
                                        </span>
                                        <small class="text-muted d-block fs-8">{{ $selectedItem->baseUnit?->name ?? __('inventory.units') }}</small>
                                        @if($factor > 1 && $wholesaleQty > 0 && $selectedItem->wholesaleUnit)
                                            <small class="text-primary d-block font-mono fs-8 fw-bold mt-1">
                                                ({{ __('inventory.reference_types.ie') }} {{ $wholesaleQty }} {{ $selectedItem->wholesaleUnit->name }}{{ $remBase > 0 ? ' ' . __('inventory.reference_types.and') . ' ' . $remBase . ' ' . $selectedItem->baseUnit?->name : '' }})
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($m->reference_url)
                                            <a href="{{ $m->reference_url }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-2.5 py-1.5 rounded-3 font-bold fs-8" target="_blank">
                                                <i class="bi bi-link-45deg me-1"></i>{{ $m->reference_type_name }} {{ $m->reference_id ? '#' . $m->reference_id : '' }}
                                            </a>
                                        @else
                                            <span class="badge bg-slate-100 text-slate-700 border px-2.5 py-1.5 rounded-3 font-bold fs-8">
                                                <i class="bi bi-file-earmark me-1"></i>{{ $m->reference_type_name }} {{ $m->reference_id ? '#' . $m->reference_id : '' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-slate-700 font-medium fs-7 text-wrap" style="max-width: 250px; display: inline-block;">
                                            {{ $m->notes ?: '-' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="font-bold text-slate-800 fs-7">
                                            <i class="bi bi-person me-1 text-slate-400"></i>{{ $m->creator?->name ?? __('inventory.reference_types.system') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 text-slate-300 d-block mb-2"></i>
                                        لا توجد حركات مخزنية مسجلة على هذا الصنف بالفترة الزمنية أو المخزن المحدد.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endif
</x-app-layout>
