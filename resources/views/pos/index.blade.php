<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-0 font-bold text-slate-900">
                    <i class="bi bi-display text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'شاشة الكاشير والمبيعات السريعة (POS)' : 'Point of Sale (POS)' }}
                </h2>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-file-earmark-plus me-1"></i>{{ app()->getLocale() == 'ar' ? 'الفواتير العادية' : 'Standard Invoice' }}
                </a>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
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
                <select id="posWarehouseSelect" class="form-select form-select-sm rounded-3 font-bold bg-light">
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
                <select id="posCustomerSelect" class="form-select form-select-sm rounded-3 font-bold bg-light">
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
                        <i class="bi bi-cash me-1"></i>{{ app()->getLocale() == 'ar' ? 'نقدي (Cash)' : 'Cash' }}
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

    <!-- Main Expanded Cashier Grid & Cart Area -->
    <div class="row g-3">
        <!-- Expanded Products Grid Area (Col-8 / Col-9 in Widescreen) -->
        <div class="col-12 col-lg-8 col-xl-8">
            <!-- Barcode Scan & Search Bar -->
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 bg-white">
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-12 col-md-8">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0 text-primary"><i class="bi bi-upc-scan fs-4"></i></span>
                            <input type="text" id="posSearchInput" class="form-control bg-light border-start-0 fs-6 font-bold" placeholder="{{ app()->getLocale() == 'ar' ? 'امسح الباركود أو ابحث باسم الصنف أو الكود...' : 'Scan barcode or search by name / code...' }}" onkeyup="filterPosItems()" autofocus>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-end">
                        <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2.5 rounded-pill font-mono border border-primary-subtle">
                            <i class="bi bi-boxes me-1"></i>{{ count($items) }} {{ app()->getLocale() == 'ar' ? 'صنف جاهز للبيع' : 'Items Available' }}
                        </span>
                    </div>
                </div>

                <!-- Category Filter Pills -->
                <div class="d-flex gap-2 overflow-x-auto pb-1 custom-scrollbar">
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 cat-filter-btn active font-bold" onclick="filterCategory('all', this)">
                        <i class="bi bi-grid-fill me-1"></i>{{ app()->getLocale() == 'ar' ? 'جميع الأصناف' : 'All Categories' }}
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 cat-filter-btn font-medium" onclick="filterCategory({{ $cat->id }}, this)">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Items Cards Grid (4 Columns Grid for Maximum Cashier Efficiency) -->
            <div class="row g-3" id="posItemsGrid">
                @forelse($items as $item)
                    @php
                        $totalStock = $item->warehouseItems->sum('qty_in_base_units');
                        $baseUnitName = $item->baseUnit?->name ?? 'قطعة';
                        $wholesaleUnitName = $item->wholesaleUnit?->name ?? 'كرتونة';
                    @endphp
                    <div class="col-12 col-sm-6 col-md-4 col-xl-3 pos-item-card" 
                         data-category-id="{{ $item->category_id }}" 
                         data-name="{{ strtolower($item->name) }}" 
                         data-code="{{ strtolower($item->item_code) }}" 
                         data-barcode="{{ strtolower($item->barcode ?? '') }}">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white hover-lift transition p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-dark-subtle text-dark border font-mono fs-7 rounded-2 px-2 py-1">{{ $item->item_code }}</span>
                                <span class="badge {{ $totalStock > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} fs-7 rounded-pill px-2">
                                    <i class="bi bi-box-seam me-1"></i>{{ number_format($totalStock, 0) }} {{ $baseUnitName }}
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="bi bi-box2-fill fs-4"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="fw-bold text-slate-900 mb-0 text-truncate fs-6" title="{{ $item->name }}">{{ $item->name }}</h6>
                                    <small class="text-muted fs-7 text-truncate d-block">{{ $item->category?->name ?? 'تصنيف عام' }}</small>
                                </div>
                            </div>

                            <div class="bg-light p-2 rounded-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-7 text-muted">{{ app()->getLocale() == 'ar' ? 'الفرادي:' : 'Retail:' }}</span>
                                    <strong class="font-mono text-primary fs-6">{{ number_format($item->retail_price, 2) }} <small class="fs-8 text-muted">ر.س</small></strong>
                                </div>
                                @if($item->wholesale_price > 0 && $item->wholesale_unit_id)
                                    <div class="d-flex align-items-center justify-content-between mt-1 pt-1 border-top border-slate-200">
                                        <span class="fs-7 text-muted">{{ app()->getLocale() == 'ar' ? 'الجملة:' : 'Wholesale:' }}</span>
                                        <strong class="font-mono text-success fs-6">{{ number_format($item->wholesale_price, 2) }} <small class="fs-8 text-muted">ر.س</small></strong>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto d-grid gap-1.5">
                                <button type="button" class="btn btn-primary btn-sm rounded-3 fw-bold py-1.5" 
                                        onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->retail_price }}, 'base', '{{ $baseUnitName }}')">
                                    <i class="bi bi-cart-plus me-1"></i>{{ app()->getLocale() == 'ar' ? 'بيع بالفرادي (' . $baseUnitName . ')' : 'Add Retail' }}
                                </button>
                                @if($item->wholesale_price > 0 && $item->wholesale_unit_id)
                                    <button type="button" class="btn btn-outline-success btn-sm rounded-3 fw-bold py-1.5" 
                                            onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->wholesale_price }}, 'wholesale', '{{ $wholesaleUnitName }}')">
                                        <i class="bi bi-box-seam me-1"></i>{{ app()->getLocale() == 'ar' ? 'بيع بالجملة (' . $wholesaleUnitName . ')' : 'Add Wholesale' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-box-seam fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted">{{ app()->getLocale() == 'ar' ? 'لا توجد أصناف سلع متوفرة حالياً' : 'No items found.' }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- POS Cart Sidebar Area (Col-4) -->
        <div class="col-12 col-lg-4 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 sticky-top" style="top: 80px;">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="mb-0 font-bold text-slate-900">
                        <i class="bi bi-cart3 text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'سلة الفاتورة' : 'Current Cart' }}
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5" onclick="clearCart()">
                        <i class="bi bi-trash me-1"></i>{{ app()->getLocale() == 'ar' ? 'تفريغ' : 'Clear' }}
                    </button>
                </div>

                <!-- Cart Items List Container -->
                <div class="cart-items-container overflow-y-auto mb-3 custom-scrollbar" style="max-height: 320px; min-height: 180px;">
                    <div id="emptyCartNotice" class="text-center py-4 text-muted">
                        <i class="bi bi-bag-plus fs-1 d-block mb-2 text-slate-300"></i>
                        <span class="fs-7 text-muted">{{ app()->getLocale() == 'ar' ? 'السلة فارغة. انقر على أي صنف لإضافته للفاتورة' : 'Cart is empty. Click items to add.' }}</span>
                    </div>
                    <ul class="list-group list-group-flush border-0" id="cartItemsList"></ul>
                </div>

                <!-- Calculation Summary -->
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between mb-1 fs-7">
                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'المجموع الفرعي:' : 'Subtotal:' }}</span>
                        <span class="font-mono fw-bold text-slate-800" id="cartSubtotal">0.00 ر.س</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1 fs-7">
                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'مبلغ الخصم:' : 'Discount:' }}</span>
                        <input type="number" step="0.01" min="0" id="cartDiscount" class="form-control form-control-sm text-end font-mono" style="width: 100px;" value="0.00" onchange="renderCart()">
                    </div>
                    <div class="d-flex justify-content-between mb-2 fs-7">
                        <span class="text-muted">{{ app()->getLocale() == 'ar' ? 'الضريبة المضافة (' . setting('tax_percentage', 15.00) . '%):' : 'VAT (' . setting('tax_percentage', 15.00) . '%):' }}</span>
                        <span class="font-mono fw-bold text-danger" id="cartTax">0.00 ر.س</span>
                    </div>
                    
                    <div class="p-3 bg-primary-subtle rounded-3 mb-3 border border-primary-subtle d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary fs-6">{{ app()->getLocale() == 'ar' ? 'الإجمالي النهائي:' : 'Grand Total:' }}</span>
                        <strong class="fs-3 font-mono text-primary" id="cartGrandTotal">0.00 ر.س</strong>
                    </div>

                    <!-- Payment Button -->
                    <button type="button" class="btn btn-success btn-lg w-100 font-bold shadow-sm rounded-3 py-3 fs-5" onclick="checkoutPos()">
                        <i class="bi bi-printer-fill me-2"></i>{{ app()->getLocale() == 'ar' ? 'إتمام الفاتورة والطباعة' : 'Checkout & Print' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        function addToCart(id, name, price, unitType, unitName) {
            const key = `${id}_${unitType}`;
            const existing = cart.find(i => i.key === key);

            if (existing) {
                existing.qty += 1;
            } else {
                cart.push({
                    key: key,
                    id: id,
                    name: name,
                    price: price,
                    unitType: unitType,
                    unitName: unitName,
                    qty: 1
                });
            }
            renderCart();
            if (typeof window.playAudioAlert === 'function') {
                window.playAudioAlert('notification');
            }
        }

        function updateCartQty(key, change) {
            const item = cart.find(i => i.key === key);
            if (item) {
                item.qty += change;
                if (item.qty <= 0) {
                    cart = cart.filter(i => i.key !== key);
                }
            }
            renderCart();
        }

        function removeFromCart(key) {
            cart = cart.filter(i => i.key !== key);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function renderCart() {
            const list = document.getElementById('cartItemsList');
            const notice = document.getElementById('emptyCartNotice');
            list.innerHTML = '';

            if (cart.length === 0) {
                notice.style.display = 'block';
            } else {
                notice.style.display = 'none';
            }

            let subtotal = 0;

            cart.forEach(item => {
                const itemSub = item.qty * item.price;
                subtotal += itemSub;

                const li = document.createElement('li');
                li.className = 'list-group-item border-0 p-2 mb-1 bg-light rounded-3 d-flex align-items-center justify-content-between gap-2';
                li.innerHTML = `
                    <div class="flex-grow-1 overflow-hidden">
                        <strong class="d-block text-slate-800 fs-7 text-truncate">${item.name}</strong>
                        <small class="text-muted font-mono fs-8">${item.price.toFixed(2)} / ${item.unitName}</small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-outline-secondary p-0 px-2 rounded-2" onclick="updateCartQty('${item.key}', -1)">-</button>
                        <span class="font-mono fw-bold px-1 fs-7">${item.qty}</span>
                        <button class="btn btn-sm btn-outline-secondary p-0 px-2 rounded-2" onclick="updateCartQty('${item.key}', 1)">+</button>
                    </div>
                    <div class="text-end" style="min-width: 65px;">
                        <strong class="font-mono fs-7 text-slate-900">${itemSub.toFixed(2)}</strong>
                        <button class="btn btn-sm text-danger p-0 ms-1" onclick="removeFromCart('${item.key}')"><i class="bi bi-x-circle"></i></button>
                    </div>
                `;
                list.appendChild(li);
            });

            const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
            const taxable = Math.max(0, subtotal - discount);
            const taxRate = {{ (float) setting('tax_percentage', 15.00) }} / 100;
            const tax = taxable * taxRate;
            const total = taxable + tax;

            document.getElementById('cartSubtotal').innerText = subtotal.toFixed(2) + ' ر.س';
            document.getElementById('cartTax').innerText = tax.toFixed(2) + ' ر.س';
            document.getElementById('cartGrandTotal').innerText = total.toFixed(2) + ' ر.س';
        }

        function filterCategory(catId, btn) {
            document.querySelectorAll('.cat-filter-btn').forEach(b => b.classList.replace('btn-primary', 'btn-outline-secondary'));
            btn.classList.replace('btn-outline-secondary', 'btn-primary');

            const cards = document.querySelectorAll('.pos-item-card');
            cards.forEach(card => {
                if (catId === 'all' || card.getAttribute('data-category-id') == catId) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function filterPosItems() {
            const q = document.getElementById('posSearchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.pos-item-card');
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const code = card.getAttribute('data-code') || '';
                const barcode = card.getAttribute('data-barcode') || '';

                if (name.includes(q) || code.includes(q) || barcode.includes(q)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function checkoutPos() {
            if (cart.length === 0) {
                if (typeof window.showSystemToast === 'function') {
                    window.showSystemToast('تنبيه السلة', 'يرجى إضافة أصناف إلى السلة أولاً قبل إتمام الفاتورة', 'warning');
                } else {
                    alert('{{ app()->getLocale() == "ar" ? "يرجى إضافة أصناف إلى السلة أولاً" : "Please add items to cart" }}');
                }
                return;
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
