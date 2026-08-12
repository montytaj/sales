<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-3 bg-primary text-white p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;">
                    <i class="bi bi-display fs-4"></i>
                </div>
                <div>
                    <h2 class="h4 mb-0 font-bold text-slate-900">
                        {{ app()->getLocale() == 'ar' ? 'شاشة الكاشير والمبيعات السريعة (POS)' : 'Point of Sale (POS)' }}
                    </h2>
                    <small class="text-muted fs-7">
                        <i class="bi bi-clock-history me-1 text-primary"></i>{{ app()->getLocale() == 'ar' ? 'نظام المبيعات السريعة والتحصيل الفوري' : 'Fast Checkout & Sales System' }}
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm" onclick="toggleFullScreenPos()" title="{{ app()->getLocale() == 'ar' ? 'ملء الشاشة' : 'Fullscreen' }}">
                    <i class="bi bi-arrows-fullscreen me-1"></i><span class="d-none d-sm-inline">{{ app()->getLocale() == 'ar' ? 'ملء الشاشة' : 'Fullscreen' }}</span>
                </button>
                <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-plus me-1"></i>{{ app()->getLocale() == 'ar' ? 'الفواتير العادية' : 'Standard Invoice' }}
                </a>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                    <i class="bi bi-receipt me-1"></i>{{ app()->getLocale() == 'ar' ? 'سجل الفواتير' : 'Invoices Log' }}
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Top Sticky POS Configuration Bar (Full Width across POS Screen) -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white border-start border-4 border-primary">
        <div class="row g-3 align-items-center">
            <!-- Warehouse Selector -->
            <div class="col-12 col-md-3">
                <label class="form-label fs-7 font-bold text-slate-700 mb-1">
                    <i class="bi bi-building text-primary me-1"></i>{{ app()->getLocale() == 'ar' ? 'المخزن المصدر للصرف:' : 'Warehouse:' }}
                </label>
                <select id="posWarehouseSelect" class="form-select form-select-sm rounded-3 font-bold bg-light border-slate-300">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Customer Selector -->
            <div class="col-12 col-md-4">
                <label class="form-label fs-7 font-bold text-slate-700 mb-1">
                    <i class="bi bi-person-check text-primary me-1"></i>{{ app()->getLocale() == 'ar' ? 'العميل المستلم:' : 'Customer:' }}
                </label>
                <select id="posCustomerSelect" class="form-select form-select-sm rounded-3 font-bold bg-light border-slate-300">
                    <option value="">{{ app()->getLocale() == 'ar' ? '👤 -- عميل نقدي مباشر (Cash Customer) --' : '👤 -- Cash Customer --' }}</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}">{{ $cust->name }} ({{ $cust->phone }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Method Selector -->
            <div class="col-12 col-md-5">
                <label class="form-label fs-7 font-bold text-slate-700 mb-1">
                    <i class="bi bi-wallet2 text-primary me-1"></i>{{ app()->getLocale() == 'ar' ? 'طريقة الدفع للفاتورة:' : 'Payment Method:' }}
                </label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="posPaymentMethod" id="payCash" value="cash" checked>
                    <label class="btn btn-outline-primary btn-sm py-1.5 fw-bold" for="payCash">
                        <i class="bi bi-cash-stack me-1"></i>{{ app()->getLocale() == 'ar' ? 'نقدي (Cash)' : 'Cash' }}
                    </label>

                    <input type="radio" class="btn-check" name="posPaymentMethod" id="payCard" value="card">
                    <label class="btn btn-outline-primary btn-sm py-1.5 fw-bold" for="payCard">
                        <i class="bi bi-credit-card me-1"></i>{{ app()->getLocale() == 'ar' ? 'شبكة / بنك (Card)' : 'Card' }}
                    </label>

                    <input type="radio" class="btn-check" name="posPaymentMethod" id="payCredit" value="credit">
                    <label class="btn btn-outline-primary btn-sm py-1.5 fw-bold" for="payCredit">
                        <i class="bi bi-clock-history me-1"></i>{{ app()->getLocale() == 'ar' ? 'آجل (Credit)' : 'Credit' }}
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- SEPARATE FULL-WIDTH ROW: Categories Filter & Search Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-top border-4 border-primary">
                <!-- Barcode Scan & Search Input (Full Width) -->
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-12 col-md-9">
                        <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                            <span class="input-group-text bg-primary text-white border-0 px-3">
                                <i class="bi bi-upc-scan fs-4"></i>
                            </span>
                            <input type="text" id="posSearchInput" 
                                   class="form-control bg-light border-0 fs-6 font-bold px-3" 
                                   placeholder="{{ app()->getLocale() == 'ar' ? 'امسح الباركود بالماسح أو ابحث باسم الصنف أو الكود... (ضغط F1 أو / للتركيز)' : 'Scan barcode or search by name / code... (Press F1 or / to focus)' }}" 
                                   onkeyup="filterPosItems(event)" 
                                   onkeydown="handleSearchKeyDown(event)" 
                                   autofocus>
                            <button type="button" class="btn btn-light border-0 text-muted px-3" onclick="clearPosSearch()" title="{{ app()->getLocale() == 'ar' ? 'مسح البحث' : 'Clear search' }}">
                                <i class="bi bi-x-circle fs-5"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 flex-wrap mt-2 text-muted fs-8">
                            <span class="fw-bold me-1 text-dark"><i class="bi bi-keyboard text-primary me-1"></i>{{ app()->getLocale() == 'ar' ? 'اختصارات الكاشير:' : 'Shortcuts:' }}</span>
                            <span class="badge bg-dark text-white font-mono px-2 py-1"><kbd class="bg-secondary text-white border-0">F2</kbd> {{ app()->getLocale() == 'ar' ? 'البحث والباركود' : 'Search/Barcode' }}</span>
                            <span class="badge bg-dark text-white font-mono px-2 py-1"><kbd class="bg-secondary text-white border-0">F4</kbd> {{ app()->getLocale() == 'ar' ? 'طريقة الدفع' : 'Payment Method' }}</span>
                            <span class="badge bg-success text-white font-mono px-2 py-1"><kbd class="bg-white text-success border-0 fw-bold">F8</kbd> {{ app()->getLocale() == 'ar' ? 'إتمام الفاتورة والطباعة' : 'Checkout & Print' }}</span>
                            <span class="badge bg-secondary text-white font-mono px-2 py-1"><kbd class="bg-dark text-white border-0">F7</kbd> {{ app()->getLocale() == 'ar' ? 'تفريغ السلة' : 'Clear Cart' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 text-end">
                        <div class="d-flex align-items-center justify-content-md-end gap-2">
                            <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2.5 rounded-pill font-mono border border-primary-subtle shadow-sm w-100 text-center">
                                <i class="bi bi-boxes me-1"></i>{{ count($items) }} {{ app()->getLocale() == 'ar' ? 'صنف جاهز للبيع' : 'Items Available' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Category Filter Buttons Row with Horizontal Drag & Scroll Navigation -->
                <div class="position-relative d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle p-1 me-1 d-none d-md-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;" onclick="scrollCategories(-250)" title="إلى اليمين">
                        <i class="bi bi-chevron-right fs-6"></i>
                    </button>
                    <div id="categoriesFilterContainer" class="d-flex align-items-center gap-2 overflow-x-auto pb-1.5 custom-scrollbar flex-grow-1" style="scroll-behavior: smooth; cursor: grab; user-select: none;">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 py-2 cat-filter-btn active font-bold text-nowrap shadow-sm flex-shrink-0" onclick="filterCategory('all', this)">
                            <i class="bi bi-grid-fill me-1"></i>{{ app()->getLocale() == 'ar' ? 'جميع الأصناف' : 'All Categories' }}
                            <span class="badge bg-white text-primary ms-1.5 rounded-pill fs-8">{{ count($items) }}</span>
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-2 cat-filter-btn font-medium text-nowrap flex-shrink-0" onclick="filterCategory({{ $cat->id }}, this)">
                                <i class="bi bi-folder2 me-1"></i>{{ $cat->name }}
                                @if(isset($cat->items_count) && $cat->items_count > 0)
                                    <span class="badge bg-secondary text-white ms-1 rounded-pill fs-8">{{ $cat->items_count }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle p-1 ms-1 d-none d-md-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;" onclick="scrollCategories(250)" title="إلى اليسار">
                        <i class="bi bi-chevron-left fs-6"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Expanded Cashier Grid & Cart Area -->
    <div class="row g-3">
        <!-- Expanded Products Grid Area (Col-8 in Widescreen, 3 Cards Per Row) -->
        <div class="col-12 col-lg-8 col-xl-8">
            <!-- Uniform 3-Column Items Cards Grid -->
            <div class="row g-3" id="posItemsGrid">
                @forelse($items as $item)
                    @php
                        $totalStock = $item->warehouseItems->sum('qty_in_base_units');
                        $baseUnitName = $item->baseUnit?->name ?? $item->unit ?? 'قطعة';
                        $wholesaleUnitName = $item->wholesaleUnit?->name ?? 'كرتونة';

                        // Price fallbacks: ensure retail_price and wholesale_price are valid non-null floats
                        $retailPrice = (float)($item->retail_price > 0 ? $item->retail_price : ($item->default_sale_price > 0 ? $item->default_sale_price : ($item->cost_price > 0 ? $item->cost_price : 0)));
                        $wholesalePrice = (float)($item->wholesale_price > 0 ? $item->wholesale_price : 0);
                    @endphp
                    <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 pos-item-card" 
                         data-item-id="{{ $item->id }}"
                         data-base-unit="{{ $baseUnitName }}"
                         data-category-id="{{ $item->category_id }}" 
                         data-name="{{ strtolower($item->name) }}" 
                         data-code="{{ strtolower($item->item_code) }}" 
                         data-barcode="{{ strtolower($item->barcode ?? '') }}">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white hover-lift transition p-3.5 d-flex flex-column justify-content-between position-relative">
                            <!-- Card Header: Code & In-Stock Status -->
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2.5">
                                    <span class="badge bg-dark-subtle text-dark border font-mono fs-7 rounded-2 px-2.5 py-1" title="رمز الصنف">{{ $item->item_code }}</span>
                                    <span class="stock-badge-container badge {{ $totalStock > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} fs-7 rounded-pill px-2.5 py-1" title="الرصيد المتاح بالمخزن">
                                        <i class="bi bi-box-seam me-1"></i>{{ number_format($totalStock, 0) }} {{ $baseUnitName }}
                                    </span>
                                </div>

                                <!-- Item Icon & Name (Fixed Height for 100% Uniform Alignment) -->
                                <div class="d-flex align-items-start gap-2.5 mb-3" style="min-height: 48px;">
                                    <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 40px; height: 40px;">
                                        <i class="bi bi-box2-fill fs-4"></i>
                                    </div>
                                    <div class="overflow-hidden flex-grow-1">
                                        <h6 class="fw-bold text-slate-900 mb-0 fs-6 text-wrap" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35;" title="{{ $item->name }}">{{ $item->name }}</h6>
                                        <small class="text-muted fs-7 text-truncate d-block mt-0.5">{{ $item->category?->name ?? 'تصنيف عام' }}</small>
                                    </div>
                                </div>

                                <!-- Clean, Prominent Price Box (Clean Typography - No 'الفرادي' text, Fixed Height for All Cards) -->
                                <div class="bg-light p-2.5 rounded-3 mb-3 border border-slate-200 text-center d-flex flex-column justify-content-center" style="min-height: 70px;">
                                    <div class="d-flex align-items-baseline justify-content-center gap-1">
                                        <span class="fs-4 font-mono fw-extrabold text-primary mb-0">{{ number_format($retailPrice, 2) }}</span>
                                        <span class="fs-7 text-muted font-sans fw-bold">ر.س</span>
                                        <span class="fs-8 text-muted font-sans ms-1">/ {{ $baseUnitName }}</span>
                                    </div>
                                    @if($wholesalePrice > 0 && $item->wholesale_unit_id)
                                        <div class="fs-8 text-success font-mono fw-bold mt-1">
                                            <i class="bi bi-box-seam me-1"></i>الجملة: {{ number_format($wholesalePrice, 2) }} ر.س ({{ $wholesaleUnitName }})
                                        </div>
                                    @else
                                        <div class="fs-8 text-muted mt-1 opacity-0 pointer-events-none">&nbsp;</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Buttons Action Section (Uniform Height & Alignment across all Cards) -->
                            <div class="mt-auto d-flex flex-column gap-1.5">
                                <button type="button" class="btn btn-primary btn-sm rounded-3 fw-bold py-2 shadow-xs w-100 d-flex align-items-center justify-content-center gap-1 btn-add-base" 
                                        onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $retailPrice }}, 'base', '{{ addslashes($baseUnitName) }}')">
                                    <i class="bi bi-cart-plus fs-6"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'إضافة للفاتورة (' . $baseUnitName . ')' : 'Add (' . $baseUnitName . ')' }}</span>
                                </button>

                                @if($wholesalePrice > 0 && $item->wholesale_unit_id)
                                    <button type="button" class="btn btn-outline-success btn-sm rounded-3 fw-bold py-1.5 shadow-xs w-100 d-flex align-items-center justify-content-center gap-1 btn-add-wholesale" 
                                            onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $wholesalePrice }}, 'wholesale', '{{ addslashes($wholesaleUnitName) }}')">
                                        <i class="bi bi-box-seam fs-6"></i>
                                        <span>{{ app()->getLocale() == 'ar' ? 'بيع بالجملة (' . $wholesaleUnitName . ')' : 'Wholesale (' . $wholesaleUnitName . ')' }}</span>
                                    </button>
                                @else
                                    <!-- Equal-height spacer element for items without wholesale price -->
                                    <div class="py-1.5 my-0.5 opacity-0 pointer-events-none" aria-hidden="true">&nbsp;</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
                        <i class="bi bi-box-seam fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted fw-medium fs-6">{{ app()->getLocale() == 'ar' ? 'لا توجد أصناف سلع متوفرة حالياً' : 'No items found.' }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- POS Cart Sidebar Area (Col-4) -->
        <div class="col-12 col-lg-4 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 sticky-top border-top border-4 border-primary" style="top: 80px;">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 font-bold text-slate-900">
                            <i class="bi bi-cart3 text-primary me-1"></i>{{ app()->getLocale() == 'ar' ? 'سلة الفاتورة' : 'Current Cart' }}
                        </h5>
                        <span class="badge bg-primary text-white rounded-pill fs-7 font-mono" id="cartBadgeCount">0</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5" onclick="clearCart(true)">
                        <i class="bi bi-trash me-1"></i>{{ app()->getLocale() == 'ar' ? 'تفريغ' : 'Clear' }}
                    </button>
                </div>

                <!-- Cart Items List Container -->
                <div class="cart-items-container overflow-y-auto mb-3 custom-scrollbar" style="max-height: 380px; min-height: 200px;">
                    <div id="emptyCartNotice" class="text-center py-5 text-muted">
                        <i class="bi bi-bag-plus fs-1 d-block mb-2 text-slate-300"></i>
                        <span class="fs-7 text-muted d-block">{{ app()->getLocale() == 'ar' ? 'السلة فارغة حالياً' : 'Cart is currently empty' }}</span>
                        <small class="fs-8 text-slate-400 d-block mt-1">{{ app()->getLocale() == 'ar' ? 'انقر على أي صنف من القائمة لإضافته للفاتورة' : 'Click items to add to invoice' }}</small>
                    </div>
                    <ul class="list-group list-group-flush border-0 gap-2" id="cartItemsList"></ul>
                </div>

                <!-- Calculation Summary -->
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between mb-1 fs-7">
                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'المجموع الفرعي:' : 'Subtotal:' }}</span>
                        <span class="font-mono fw-bold text-slate-800" id="cartSubtotal">0.00 ر.س</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1 fs-7">
                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'مبلغ الخصم:' : 'Discount:' }}</span>
                        <input type="number" step="0.01" min="0" id="cartDiscount" class="form-control form-control-sm text-end font-mono" style="width: 110px;" value="0.00" oninput="updateCartTotals()" onfocus="this.select()">
                    </div>
                    <div class="d-flex justify-content-between mb-2 fs-7">
                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'الضريبة المضافة (' . setting('tax_percentage', 15.00) . '%):' : 'VAT (' . setting('tax_percentage', 15.00) . '%):' }}</span>
                        <span class="font-mono fw-bold text-danger" id="cartTax">0.00 ر.س</span>
                    </div>
                    
                    <div class="p-3 bg-primary-subtle rounded-3 mb-3 border border-primary-subtle d-flex justify-content-between align-items-center shadow-xs">
                        <span class="fw-bold text-primary fs-6">{{ app()->getLocale() == 'ar' ? 'الإجمالي النهائي:' : 'Grand Total:' }}</span>
                        <strong class="fs-3 font-mono text-primary" id="cartGrandTotal">0.00 ر.س</strong>
                    </div>

                    <!-- Payment Button -->
                    <button type="button" class="btn btn-success btn-lg w-100 font-bold shadow-sm rounded-3 py-3 fs-5 position-relative" onclick="checkoutPos()">
                        <i class="bi bi-printer-fill me-2"></i>{{ app()->getLocale() == 'ar' ? 'إتمام الفاتورة والطباعة' : 'Checkout & Print' }}
                        <span class="badge bg-white text-success position-absolute top-50 end-0 translate-middle-y me-3 fs-8 font-mono d-none d-sm-inline">[F9]</span>
                    </button>

                    <!-- Keyboard Shortcuts Quick Guide -->
                    <div class="d-flex justify-content-around text-center text-muted fs-8 mt-2 pt-2 border-top border-slate-100">
                        <span><kbd class="bg-light text-dark border">F1</kbd> بحث الأصناف</span>
                        <span><kbd class="bg-light text-dark border">F8</kbd> تفريغ السلة</span>
                        <span><kbd class="bg-light text-dark border">F9</kbd> إتمام الفاتورة</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        const posItemsStockMap = {
            @foreach($items as $item)
                "{{ $item->id }}": {
                    @foreach($item->warehouseItems as $whItem)
                        "{{ $whItem->warehouse_id }}": {{ max(0, (float)$whItem->qty_in_base_units) }},
                    @endforeach
                },
            @endforeach
        };

        const posConversionFactorsMap = {
            @foreach($items as $item)
                "{{ $item->id }}": {{ (float)($item->conversion_factor > 0 ? $item->conversion_factor : 1) }},
            @endforeach
        };

        function getItemStockInSelectedWarehouse(itemId) {
            const selectedWh = document.getElementById('posWarehouseSelect')?.value;
            if (!selectedWh || !posItemsStockMap[itemId]) return 0;
            return parseFloat(posItemsStockMap[itemId][selectedWh]) || 0;
        }

        function updateItemsStockDisplay() {
            const selectedWh = document.getElementById('posWarehouseSelect')?.value;
            document.querySelectorAll('.pos-item-card').forEach(card => {
                const itemId = card.getAttribute('data-item-id');
                const badgeEl = card.querySelector('.stock-badge-container');
                const availStock = getItemStockInSelectedWarehouse(itemId);
                const baseUnitName = card.getAttribute('data-base-unit') || 'قطعة';

                if (badgeEl) {
                    if (availStock > 0) {
                        badgeEl.className = 'stock-badge-container badge bg-success-subtle text-success border border-success-subtle fs-7 rounded-pill px-2.5 py-1';
                        badgeEl.innerHTML = `<i class="bi bi-box-seam me-1"></i>${availStock} ${baseUnitName}`;
                    } else {
                        badgeEl.className = 'stock-badge-container badge bg-danger-subtle text-danger border border-danger-subtle fs-7 rounded-pill px-2.5 py-1';
                        badgeEl.innerHTML = `<i class="bi bi-x-circle me-1"></i>غير متوفر بالمخزن (0)`;
                    }
                }
            });
        }

        function addToCart(id, name, price, unitType, unitName) {
            const availStock = getItemStockInSelectedWarehouse(id);
            if (availStock <= 0) {
                if (typeof window.showSystemToast === 'function') {
                    window.showSystemToast('تنبيه المخزون', `عفواً، الصنف (${name}) غير متوفر حالياً بالمخزن المختار.`, 'danger');
                } else {
                    alert(`عفواً، الصنف (${name}) غير متوفر حالياً بالمخزن المختار.`);
                }
                return;
            }

            const conversionFactor = posConversionFactorsMap[id] || 1;
            let addedBaseQty = (unitType === 'wholesale') ? conversionFactor : 1;

            let currentInCartBase = 0;
            cart.filter(i => i.id === id).forEach(i => {
                const factor = posConversionFactorsMap[i.id] || 1;
                currentInCartBase += (i.unitType === 'wholesale') ? (i.qty * factor) : i.qty;
            });

            if ((currentInCartBase + addedBaseQty) > availStock) {
                if (typeof window.showSystemToast === 'function') {
                    window.showSystemToast('تنبيه المخزون', `لا يمكن إضافة المزيد. الكمية المتاحة في المخزن فقط ${availStock} قطعة.`, 'warning');
                } else {
                    alert(`لا يمكن إضافة المزيد. الكمية المتاحة في المخزن فقط ${availStock} قطعة.`);
                }
                return;
            }

            const parsedPrice = parseFloat(price) || 0;
            const key = `${id}_${unitType}`;
            const existing = cart.find(i => i.key === key);

            if (existing) {
                existing.qty += 1;
            } else {
                cart.push({
                    key: key,
                    id: id,
                    name: name,
                    price: parsedPrice,
                    unitType: unitType,
                    unitName: unitName,
                    qty: 1
                });
            }
            renderCartList();
            if (typeof window.playAudioAlert === 'function') {
                window.playAudioAlert('notification');
            }
        }

        function updateCartQty(key, change) {
            const item = cart.find(i => i.key === key);
            if (!item) return;

            const targetQty = Math.max(1, (parseInt(item.qty, 10) || 1) + change);
            const factor = posConversionFactorsMap[item.id] || 1;
            const availStock = getItemStockInSelectedWarehouse(item.id);

            let otherBaseQty = 0;
            cart.filter(i => i.id === item.id && i.key !== key).forEach(i => {
                const f = posConversionFactorsMap[i.id] || 1;
                otherBaseQty += (i.unitType === 'wholesale') ? (i.qty * f) : i.qty;
            });

            const newTotalBase = otherBaseQty + ((item.unitType === 'wholesale') ? (targetQty * factor) : targetQty);

            if (change > 0 && newTotalBase > availStock) {
                if (typeof window.showSystemToast === 'function') {
                    window.showSystemToast('تنبيه المخزون', `عفواً، الكمية المتاحة بالرصيد لهذا الصنف هي ${availStock} فقط.`, 'warning');
                } else {
                    alert(`عفواً، الكمية المتاحة بالرصيد لهذا الصنف هي ${availStock} فقط.`);
                }
                return;
            }

            item.qty = targetQty;
            const inputEl = document.getElementById(`qty-input-${key}`);
            if (inputEl) inputEl.value = item.qty;
            updateLineTotal(item);
            updateCartTotals();
        }

        function handleQtyInput(key, el) {
            const item = cart.find(i => i.key === key);
            if (!item) return;

            let rawVal = el.value.replace(/[\.,].*/, '');
            el.value = rawVal;

            let parsed = parseInt(rawVal, 10);
            if (isNaN(parsed) || parsed < 1) parsed = 1;

            const factor = posConversionFactorsMap[item.id] || 1;
            const availStock = getItemStockInSelectedWarehouse(item.id);

            let otherBaseQty = 0;
            cart.filter(i => i.id === item.id && i.key !== key).forEach(i => {
                const f = posConversionFactorsMap[i.id] || 1;
                otherBaseQty += (i.unitType === 'wholesale') ? (i.qty * f) : i.qty;
            });

            const newTotalBase = otherBaseQty + ((item.unitType === 'wholesale') ? (parsed * factor) : parsed);

            if (newTotalBase > availStock) {
                const maxAllowedQty = Math.max(1, Math.floor((availStock - otherBaseQty) / ((item.unitType === 'wholesale') ? factor : 1)));
                parsed = maxAllowedQty;
                el.value = maxAllowedQty;
                if (typeof window.showSystemToast === 'function') {
                    window.showSystemToast('تنبيه المخزون', `تم تعديل الكمية لأقصى رصيد متاح بالمخزن (${maxAllowedQty}).`, 'warning');
                }
            }

            item.qty = parsed;
            updateLineTotal(item);
            updateCartTotals();
        }

        function handlePriceInput(key, el) {
            const item = cart.find(i => i.key === key);
            if (!item) return;

            let parsed = parseFloat(el.value);
            item.price = isNaN(parsed) || parsed < 0 ? 0 : parsed;

            updateLineTotal(item);
            updateCartTotals();
        }

        function updateLineTotal(item) {
            const lineSubtotalEl = document.getElementById(`line-subtotal-${item.key}`);
            if (lineSubtotalEl) {
                lineSubtotalEl.innerText = (item.qty * item.price).toFixed(2);
            }
        }

        function removeFromCart(key) {
            cart = cart.filter(i => i.key !== key);
            renderCartList();
        }

        function clearCart(confirmFirst = false) {
            if (cart.length === 0) return;
            if (confirmFirst) {
                if (!confirm('{{ app()->getLocale() == "ar" ? "هل أنت تأكد من تفريغ كافة عناصر السلة؟" : "Are you sure you want to clear the cart?" }}')) {
                    return;
                }
            }
            cart = [];
            renderCartList();
        }

        // Complete Cart List Builder (Called on Add/Remove Item)
        function renderCartList() {
            const list = document.getElementById('cartItemsList');
            const notice = document.getElementById('emptyCartNotice');
            list.innerHTML = '';

            if (cart.length === 0) {
                notice.style.display = 'block';
            } else {
                notice.style.display = 'none';
            }

            cart.forEach(item => {
                const itemSub = item.qty * item.price;
                const unitTag = item.unitType === 'wholesale' ? 'جملة' : 'فرادي';

                const li = document.createElement('li');
                li.className = 'list-group-item border-0 p-2.5 bg-light rounded-3 shadow-xs mb-1';
                li.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <div class="overflow-hidden me-2">
                            <strong class="d-block text-slate-900 fs-7 text-truncate" title="${item.name}">${item.name}</strong>
                            <small class="badge bg-secondary-subtle text-secondary fs-8">${unitTag} (${item.unitName})</small>
                        </div>
                        <button class="btn btn-xs btn-outline-danger p-0 px-1 rounded-circle" onclick="removeFromCart('${item.key}')" title="حذف الصنف">
                            <i class="bi bi-x-lg fs-8"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 pt-1 border-top border-slate-200">
                        <!-- Direct Editable Unit Price Input (Updates Totals Realtime on Input) -->
                        <div class="d-flex align-items-center gap-1">
                            <span class="fs-8 text-muted font-bold">السعر:</span>
                            <input type="number" step="0.01" min="0" 
                                   class="form-control form-control-sm text-center font-mono p-0 px-1 border-slate-300 fs-7" 
                                   style="width: 75px;" value="${item.price}" 
                                   oninput="handlePriceInput('${item.key}', this)" 
                                   onfocus="this.select()" title="تعديل سعر الوحدة الفوري">
                        </div>

                        <!-- Integer-Only Quantity Input with Plus & Minus Buttons (Updates Totals Realtime on Input) -->
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary p-0 px-2 rounded-2 fw-bold" onclick="updateCartQty('${item.key}', -1)">-</button>
                            <input type="number" id="qty-input-${item.key}" step="1" min="1" pattern="[0-9]*" 
                                   class="form-control form-control-sm text-center font-mono fw-bold p-0 px-1 border-slate-300 fs-7" 
                                   style="width: 60px;" value="${item.qty}" 
                                   oninput="handleQtyInput('${item.key}', this)" 
                                   onfocus="this.select()" title="كتابة الكمية (أعداد صحيحة فقط)">
                            <button type="button" class="btn btn-sm btn-outline-secondary p-0 px-2 rounded-2 fw-bold" onclick="updateCartQty('${item.key}', 1)">+</button>
                        </div>

                        <!-- Line Item Subtotal -->
                        <div class="text-end" style="min-width: 65px;">
                            <strong class="font-mono fs-7 text-primary" id="line-subtotal-${item.key}">${itemSub.toFixed(2)}</strong>
                            <small class="fs-8 text-muted d-block">ر.س</small>
                        </div>
                    </div>
                `;
                list.appendChild(li);
            });

            updateCartTotals();
        }

        // Global Cart Totals Recalculator (Called on Keypress & Input Events Instantly)
        function updateCartTotals() {
            const totalItemCount = cart.reduce((sum, i) => sum + (parseInt(i.qty, 10) || 0), 0);
            const cartBadge = document.getElementById('cartBadgeCount');
            if (cartBadge) {
                cartBadge.innerText = totalItemCount;
            }

            let subtotal = cart.reduce((sum, i) => sum + ((parseInt(i.qty, 10) || 0) * (parseFloat(i.price) || 0)), 0);
            const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
            const taxable = Math.max(0, subtotal - discount);
            const taxRate = {{ (float) setting('tax_percentage', 15.00) }} / 100;
            const tax = taxable * taxRate;
            const total = taxable + tax;

            document.getElementById('cartSubtotal').innerText = subtotal.toFixed(2) + ' ر.س';
            document.getElementById('cartTax').innerText = tax.toFixed(2) + ' ر.س';
            document.getElementById('cartGrandTotal').innerText = total.toFixed(2) + ' ر.س';
        }

        // Horizontal Categories Scroll and Mouse Drag Controls
        function scrollCategories(distance) {
            const container = document.getElementById('categoriesFilterContainer');
            if (container) {
                container.scrollBy({ left: distance, behavior: 'smooth' });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateItemsStockDisplay();
            const whSelect = document.getElementById('posWarehouseSelect');
            if (whSelect) {
                whSelect.addEventListener('change', function() {
                    updateItemsStockDisplay();
                });
            }

            const catContainer = document.getElementById('categoriesFilterContainer');
            if (catContainer) {
                let isDown = false;
                let startX, scrollLeft;

                catContainer.addEventListener('mousedown', (e) => {
                    isDown = true;
                    catContainer.style.cursor = 'grabbing';
                    startX = e.pageX - catContainer.offsetLeft;
                    scrollLeft = catContainer.scrollLeft;
                });

                catContainer.addEventListener('mouseleave', () => {
                    isDown = false;
                    catContainer.style.cursor = 'grab';
                });

                catContainer.addEventListener('mouseup', () => {
                    isDown = false;
                    catContainer.style.cursor = 'grab';
                });

                catContainer.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - catContainer.offsetLeft;
                    const walk = (x - startX) * 2;
                    catContainer.scrollLeft = scrollLeft - walk;
                });

                catContainer.addEventListener('wheel', (e) => {
                    if (e.deltaY !== 0) {
                        catContainer.scrollLeft += e.deltaY;
                        e.preventDefault();
                    }
                }, { passive: false });
            }
        });

        function filterCategory(catId, btn) {
            document.querySelectorAll('.cat-filter-btn').forEach(b => {
                b.classList.remove('btn-primary', 'active', 'shadow-sm');
                b.classList.add('btn-outline-secondary');
            });
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-primary', 'active', 'shadow-sm');

            const cards = document.querySelectorAll('.pos-item-card');
            cards.forEach(card => {
                if (catId === 'all' || card.getAttribute('data-category-id') == catId) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function filterPosItems(event) {
            const q = document.getElementById('posSearchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.pos-item-card');
            let visibleCards = [];
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const code = card.getAttribute('data-code') || '';
                const barcode = card.getAttribute('data-barcode') || '';

                if (name.includes(q) || code.includes(q) || barcode.includes(q)) {
                    card.style.display = 'block';
                    visibleCards.push(card);
                } else {
                    card.style.display = 'none';
                }
            });

            // Handle Barcode Scanner Enter Keypress (Automatic Add to Cart if exact barcode match or unique item)
            if (event && event.key === 'Enter' && q.length > 0) {
                event.preventDefault();
                let exactCard = Array.from(cards).find(c => (c.getAttribute('data-barcode') || '') === q || (c.getAttribute('data-code') || '') === q);
                if (!exactCard && visibleCards.length === 1) {
                    exactCard = visibleCards[0];
                }

                if (exactCard) {
                    const btn = exactCard.querySelector('button.btn-primary');
                    if (btn) {
                        btn.click();
                        document.getElementById('posSearchInput').value = '';
                        filterPosItems();
                    }
                }
            }
        }

        function handleSearchKeyDown(event) {
            if (event.key === 'Escape') {
                clearPosSearch();
            }
        }

        function clearPosSearch() {
            const input = document.getElementById('posSearchInput');
            if (input) {
                input.value = '';
                filterPosItems();
                input.focus();
            }
        }

        function toggleFullScreenPos() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => console.warn(err));
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        // Global Keyboard Shortcuts for Cashiers (F2 search, F4 payment method, F8 checkout, F7 clear)
        function cyclePosPaymentMethod() {
            const cash = document.getElementById('payCash');
            const card = document.getElementById('payCard');
            const credit = document.getElementById('payCredit');
            if (cash && cash.checked) {
                if (card) card.checked = true;
            } else if (card && card.checked) {
                if (credit) credit.checked = true;
            } else {
                if (cash) cash.checked = true;
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'F2' || e.key === 'F1' || (e.key === '/' && document.activeElement.tagName !== 'INPUT')) {
                e.preventDefault();
                const searchEl = document.getElementById('posSearchInput');
                if (searchEl) searchEl.focus();
            } else if (e.key === 'F4') {
                e.preventDefault();
                cyclePosPaymentMethod();
            } else if (e.key === 'F8' || e.key === 'F9' || (e.ctrlKey && e.key === 'Enter')) {
                e.preventDefault();
                checkoutPos();
            } else if (e.key === 'F7') {
                e.preventDefault();
                clearCart(true);
            }
        });

        function checkoutPos() {
            if (cart.length === 0) {
                if (typeof window.showSystemToast === 'function') {
                    window.showSystemToast('تنبيه السلة', 'يرجى إضافة أصناف إلى السلة أولاً قبل إتمام الفاتورة', 'warning');
                } else {
                    alert('{{ app()->getLocale() == "ar" ? "يرجى إضافة أصناف إلى السلة أولاً" : "Please add items to cart" }}');
                }
                return;
            }

            const selectedWh = document.getElementById('posWarehouseSelect').value;
            for (let i = 0; i < cart.length; i++) {
                const item = cart[i];
                const availStock = getItemStockInSelectedWarehouse(item.id);
                let totalItemBase = 0;
                cart.filter(c => c.id === item.id).forEach(c => {
                    const f = posConversionFactorsMap[c.id] || 1;
                    totalItemBase += (c.unitType === 'wholesale') ? (c.qty * f) : c.qty;
                });
                if (totalItemBase > availStock) {
                    if (typeof window.showSystemToast === 'function') {
                        window.showSystemToast('تنبيه المخزون', `الكمية المطلوبة للصنف (${item.name}) وهي ${totalItemBase} تتجاوز المتاح بالمخزن المختار (${availStock})`, 'danger');
                    } else {
                        alert(`الكمية المطلوبة للصنف (${item.name}) تتجاوز المتاح بالمخزن المختار (${availStock})`);
                    }
                    return;
                }
            }

            const paymentMethodEl = document.querySelector('input[name="posPaymentMethod"]:checked');
            const paymentType = paymentMethodEl ? paymentMethodEl.value : 'cash';

            const payload = {
                _token: '{{ csrf_token() }}',
                warehouse_id: document.getElementById('posWarehouseSelect').value,
                customer_id: document.getElementById('posCustomerSelect').value || null,
                payment_type: paymentType,
                discount_amount: parseFloat(document.getElementById('cartDiscount').value) || 0,
                items: cart.map(i => ({
                    id: i.id,
                    qty: i.qty,
                    unit_type: i.unitType,
                    price: i.price
                }))
            };

            fetch('{{ route("pos.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.playAudioAlert === 'function') {
                        window.playAudioAlert('success');
                    }
                    if (typeof window.showSystemToast === 'function') {
                        window.showSystemToast('نجاح العملية', data.message || 'تم إتمام عملية البيع وطباعة الفاتورة بنجاح', 'success');
                    }
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 800);
                } else {
                    if (typeof window.showSystemToast === 'function') {
                        window.showSystemToast('خطأ في العملية', data.message || 'حدث خطأ أثناء إتمام الفاتورة', 'danger');
                    } else {
                        alert(data.message || 'حدث خطأ أثناء إتمام الفاتورة');
                    }
                }
            })
            .catch(err => {
                if (typeof window.playAudioAlert === 'function') {
                    window.playAudioAlert('success');
                }
                setTimeout(() => {
                    window.location.href = '{{ route("invoices.index") }}';
                }, 800);
            });
        }
    </script>
</x-app-layout>
