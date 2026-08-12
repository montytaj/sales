<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-receipt text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'إنشاء فاتورة مبيعات جديدة' : 'Create New Sales Invoice' }}
            </h2>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>{{ app()->getLocale() == 'ar' ? 'الرجوع للفواتير' : 'Back to Invoices' }}
            </a>
        </div>
    </x-slot>

    <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
        @csrf
        <div class="row g-4 mb-4">
            <!-- Header Information -->
            <div class="col-md-4">
                <label class="form-label font-bold">العميل <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select" required>
                    <option value="">-- اختر العميل --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label font-bold">مخزن السحب / الصرف <span class="text-danger">*</span></label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">-- اختر المخزن --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label font-bold">تاريخ الفاتورة <span class="text-danger">*</span></label>
                <input type="date" name="issue_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label font-bold">طريقة الدفع <span class="text-danger">*</span></label>
                <select name="payment_type" id="paymentTypeSelect" class="form-select" onchange="togglePaymentBreakdown()" required>
                    <option value="" selected disabled>-- {{ app()->getLocale() == 'ar' ? 'اختر طريقة السداد' : 'Select Payment Method' }} --</option>
                    <option value="cash">{{ app()->getLocale() == 'ar' ? 'نقدي بالكامل' : 'Cash' }}</option>
                    <option value="bank">{{ app()->getLocale() == 'ar' ? 'بنكي / شبكة بالكامل' : 'Bank' }}</option>
                    <option value="credit">{{ app()->getLocale() == 'ar' ? 'آجل بالكامل' : 'Credit' }}</option>
                    <option value="split">{{ app()->getLocale() == 'ar' ? 'دفع متعدد / مختلط (كاش + بنك + أجل)' : 'Split Payment' }}</option>
                </select>
            </div>
        </div>

        <!-- Payment Breakdown & Accounts Section -->
        <div class="card shadow-sm border-0 rounded-3 mb-4 bg-light-subtle" id="paymentBreakdownCard" style="display: none;">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i>{{ app()->getLocale() == 'ar' ? 'تفاصيل السداد وحسابات الدفع (من شجرة الحسابات)' : 'Payment & Account Details' }}</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4" id="cashAccountCol" style="display: none;">
                        <label class="form-label font-bold"><i class="bi bi-cash-stack text-success me-1"></i>{{ app()->getLocale() == 'ar' ? 'حساب الكاش / الخزينة (شجرة الحسابات)' : 'Cashbox Account' }}</label>
                        <select name="cash_account_id" class="form-select">
                            <option value="">-- {{ app()->getLocale() == 'ar' ? 'اختر حساب الخزينة' : 'Select Cashbox Account' }} --</option>
                            @foreach($cashAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4" id="bankAccountCol" style="display: none;">
                        <label class="form-label font-bold"><i class="bi bi-bank text-primary me-1"></i>{{ app()->getLocale() == 'ar' ? 'حساب البنك / الشبكة (شجرة الحسابات)' : 'Bank Account' }}</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">-- {{ app()->getLocale() == 'ar' ? 'اختر حساب البنك' : 'Select Bank Account' }} --</option>
                            @foreach($bankAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4" id="splitAmountsCol" style="display: none;">
                        <div class="p-3 bg-white border rounded-3 shadow-xs">
                            <div class="mb-2">
                                <label class="form-label font-bold text-success mb-1">{{ app()->getLocale() == 'ar' ? 'المبلغ المدفوع كاش (' . currency() . '):' : 'Cash Paid Amount (' . currency() . '):' }}</label>
                                <input type="number" step="0.01" min="0" name="cash_amount" id="cashAmountInput" class="form-control font-mono" value="0.00" oninput="calculatePaymentBreakdown()">
                            </div>
                            <div class="mb-2">
                                <label class="form-label font-bold text-primary mb-1">{{ app()->getLocale() == 'ar' ? 'المبلغ المدفوع بنك/شبكة (' . currency() . '):' : 'Bank Paid Amount (' . currency() . '):' }}</label>
                                <input type="number" step="0.01" min="0" name="bank_amount" id="bankAmountInput" class="form-control font-mono" value="0.00" oninput="calculatePaymentBreakdown()">
                            </div>
                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                <span class="font-bold text-muted">{{ app()->getLocale() == 'ar' ? 'المبلغ المتبقي (آجل):' : 'Remaining Due:' }}</span>
                                <span id="dueAmountDisplay" class="fw-bold font-mono text-danger fs-5">0.00 {{ currency() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 font-bold text-dark"><i class="bi bi-cart-check text-primary me-2"></i>بنود الفاتورة والأصناف (تصفية حسب التصنيف والوحدة)</h5>
                <button type="button" class="btn btn-sm btn-primary" id="addRowBtn" onclick="addInvoiceRow()">
                    <i class="bi bi-plus-circle me-1"></i>إضافة بند آخر
                </button>
            </div>
            <div class="card-body p-4">
                <div id="warehouseNotice" class="alert alert-warning border-0 rounded-3 mb-3 d-flex align-items-center shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-warning"></i>
                    <span><strong>تنبيه هام:</strong> يجب اختيار <strong>مخزن السحب / الصرف</strong> أولاً لتحديد وإضافة الأصناف وعرض الرصيد المتاح.</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="invoiceItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%">التصنيف</th>
                                <th style="width: 25%">الصنف <span class="text-danger">*</span></th>
                                <th style="width: 20%">الوحدة المحددة <span class="text-danger">*</span></th>
                                <th style="width: 10%">الكمية <span class="text-danger">*</span></th>
                                <th style="width: 12%">سعر الوحدة <span class="text-danger">*</span></th>
                                <th style="width: 8%">الإجمالي الفرعي</th>
                                <th style="width: 5%">إزالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows inserted dynamically -->
                        </tbody>
                        <tfoot class="table-light font-bold">
                            <tr>
                                <td colspan="5" class="text-end fw-bold">المجموع قبل الضريبة:</td>
                                <td colspan="2"><span id="subtotalDisplay" class="fs-6 font-mono text-dark">0.00</span> ر.س</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">ضريبة القيمة المضافة ({{ setting('tax_percentage', 15.00) }}%):</td>
                                <td colspan="2"><span id="taxDisplay" class="fs-6 font-mono text-danger">0.00</span> ر.س</td>
                            </tr>
                            <tr class="table-primary fs-5">
                                <td colspan="5" class="text-end fw-bold">إجمالي الفاتورة النهائي:</td>
                                <td colspan="2"><span id="totalDisplay" class="fw-bold font-mono text-primary">0.00</span> ر.س</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <label class="form-label font-bold">ملاحظات وشروط الفاتورة</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="ملاحظات العميل أو طريقة التسليم"></textarea>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success btn-lg w-100 py-3 font-bold shadow-sm">
                    <i class="bi bi-check-circle-fill me-2"></i>حفظ وإصدار الفاتورة
                </button>
            </div>
        </div>
    </form>

    <script>
        const categories = @json($categories);
        const inventoryItems = @json($items);
        const units = @json($units);
        let rowIndex = 0;

        function getWarehouseStockForItem(itemId, warehouseId) {
            const item = inventoryItems.find(i => i.id == itemId);
            if (!item || !item.warehouse_items || !warehouseId) return 0;
            const whItem = item.warehouse_items.find(w => w.warehouse_id == warehouseId);
            return whItem ? Math.max(0, parseFloat(whItem.qty_in_base_units) || 0) : 0;
        }

        function updateWarehouseSelectionState() {
            const warehouseSelect = document.querySelector('select[name="warehouse_id"]');
            const selectedWh = warehouseSelect ? warehouseSelect.value : null;
            const addRowBtn = document.getElementById('addRowBtn');
            const notice = document.getElementById('warehouseNotice');

            const isWarehouseSelected = !!selectedWh;

            if (notice) {
                if (isWarehouseSelected) {
                    notice.classList.add('d-none');
                } else {
                    notice.classList.remove('d-none');
                }
            }

            if (addRowBtn) {
                addRowBtn.disabled = !isWarehouseSelected;
            }

            document.querySelectorAll('#invoiceItemsTable tbody tr').forEach(tr => {
                const catSelect = tr.querySelector('.category-select');
                const itemSelect = tr.querySelector('.item-select');
                const unitSelect = tr.querySelector('.unit-select');
                const qtyInput = tr.querySelector('.qty-input');
                const priceInput = tr.querySelector('.price-input');
                const removeBtn = tr.querySelector('button');

                if (catSelect) catSelect.disabled = false;
                if (itemSelect) itemSelect.disabled = !isWarehouseSelected;
                if (unitSelect) unitSelect.disabled = !isWarehouseSelected;
                if (qtyInput) qtyInput.disabled = !isWarehouseSelected;
                if (priceInput) priceInput.disabled = !isWarehouseSelected;
                if (removeBtn) removeBtn.disabled = !isWarehouseSelected;

                if (window.jQuery && $.fn.select2) {
                    $(tr).find('select').each(function() {
                        if ($(this).data('select2')) {
                            $(this).prop('disabled', this.disabled).trigger('change.select2');
                        }
                    });
                }
            });

            if (isWarehouseSelected) {
                updateAllRowsStockHints();
            }
        }

        function addInvoiceRow() {
            const tableBody = document.querySelector('#invoiceItemsTable tbody');
            const tr = document.createElement('tr');
            const rIdx = rowIndex;
            tr.id = `row_${rIdx}`;

            let catOptions = `<option value="">-- كل التصنيفات --</option>`;
            categories.forEach(cat => {
                catOptions += `<option value="${cat.id}">${cat.name}</option>`;
            });

            tr.innerHTML = `
                <td>
                    <select class="form-select category-select" onchange="onCategoryChange(${rIdx})">
                        ${catOptions}
                    </select>
                </td>
                <td>
                    <select name="items[${rIdx}][inventory_item_id]" class="form-select item-select" onchange="onItemChange(${rIdx})" required>
                        <option value="">-- اختر الصنف --</option>
                    </select>
                    <small class="stock-hint text-muted d-block mt-1 font-mono fs-8"></small>
                </td>
                <td>
                    <select name="items[${rIdx}][unit_id]" class="form-select unit-select" onchange="onUnitChange(${rIdx})" required>
                        <option value="">-- حدد الوحدة --</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" name="items[${rIdx}][quantity]" class="form-control qty-input" value="1" oninput="calculateRowTotal(${rIdx})" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${rIdx}][unit_price]" class="form-control price-input" value="0.00" oninput="calculateRowTotal(${rIdx})" required>
                </td>
                <td>
                    <span class="row-subtotal font-mono fw-bold text-dark">0.00</span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${rIdx})"><i class="bi bi-trash"></i></button>
                </td>
            `;

            tableBody.appendChild(tr);

            const warehouseSelect = document.querySelector('select[name="warehouse_id"]');
            const isWarehouseSelected = warehouseSelect && warehouseSelect.value;
            
            const catSelect = tr.querySelector('.category-select');
            if (catSelect) catSelect.disabled = false;

            tr.querySelectorAll('.item-select, .unit-select, .qty-input, .price-input, button').forEach(el => {
                el.disabled = !isWarehouseSelected;
            });

            populateItemsForCategory(rIdx, '');

            if (window.initSelect2) {
                window.initSelect2(tr);
            }

            if (window.jQuery) {
                const $tr = $(tr);
                $tr.find('.category-select').on('change', function() {
                    onCategoryChange(rIdx);
                });
                $tr.find('.item-select').on('change', function() {
                    onItemChange(rIdx);
                });
                $tr.find('.unit-select').on('change', function() {
                    onUnitChange(rIdx);
                });
            }

            rowIndex++;
        }

        function onCategoryChange(idx) {
            const row = document.getElementById(`row_${idx}`);
            if (!row) return;
            const catId = row.querySelector('.category-select').value;
            populateItemsForCategory(idx, catId);
        }

        function populateItemsForCategory(idx, catId) {
            const row = document.getElementById(`row_${idx}`);
            if (!row) return;
            const itemSelect = row.querySelector('.item-select');
            
            let filteredItems = inventoryItems;
            if (catId) {
                filteredItems = inventoryItems.filter(i => i.category_id == catId);
            }

            let itemOptions = `<option value="">-- اختر الصنف (${filteredItems.length}) --</option>`;
            filteredItems.forEach(item => {
                itemOptions += `<option value="${item.id}" data-wholesale-price="${item.wholesale_price}" data-retail-price="${item.retail_price}" data-wholesale-unit-id="${item.wholesale_unit_id}" data-base-unit-id="${item.base_unit_id}">${item.name} (${item.code || ''})</option>`;
            });

            itemSelect.innerHTML = itemOptions;

            if (window.jQuery && $.fn.select2) {
                const $itemSelect = $(itemSelect);
                if ($itemSelect.data('select2')) {
                    $itemSelect.select2('destroy');
                }
                if (window.initSelect2) {
                    window.initSelect2($itemSelect);
                }
            }

            onItemChange(idx);
        }

        function onItemChange(idx) {
            const row = document.getElementById(`row_${idx}`);
            if (!row) return;
            const itemSelect = row.querySelector('.item-select');
            const unitSelect = row.querySelector('.unit-select');

            const itemId = itemSelect.value;
            const item = inventoryItems.find(i => i.id == itemId);

            unitSelect.innerHTML = '';
            if (item) {
                const defaultSale = parseFloat(item.default_sale_price) || 0;
                const retail = parseFloat(item.retail_price) || 0;
                const wholesale = parseFloat(item.wholesale_price) || 0;
                const cost = parseFloat(item.cost_price) || 0;

                const basePrice = retail > 0 ? retail : (defaultSale > 0 ? defaultSale : cost);
                const wholesalePrice = wholesale > 0 ? wholesale : (basePrice * (parseFloat(item.conversion_factor) || 1));

                if (item.wholesale_unit) {
                    unitSelect.innerHTML += `<option value="${item.wholesale_unit_id}" data-price="${wholesalePrice}" data-is-wholesale="1">كرتونة / بالجملة (${item.wholesale_unit.name})</option>`;
                }
                if (item.base_unit) {
                    unitSelect.innerHTML += `<option value="${item.base_unit_id}" data-price="${basePrice}" data-is-wholesale="0">قطعة / بالفرادي (${item.base_unit.name})</option>`;
                }

                if (!item.wholesale_unit && !item.base_unit) {
                    const unitName = item.unit || 'وحدة';
                    const unitId = item.base_unit_id || item.wholesale_unit_id || 1;
                    unitSelect.innerHTML += `<option value="${unitId}" data-price="${basePrice}" data-is-wholesale="0">${unitName}</option>`;
                }
            } else {
                unitSelect.innerHTML = '<option value="">-- حدد الوحدة --</option>';
            }

            if (window.jQuery && $.fn.select2) {
                const $unitSelect = $(unitSelect);
                if ($unitSelect.data('select2')) {
                    $unitSelect.select2('destroy');
                }
                if (window.initSelect2) {
                    window.initSelect2($unitSelect);
                }
            }

            if (item) {
                onUnitChange(idx);
            } else {
                const priceInput = row.querySelector('.price-input');
                if (priceInput) priceInput.value = '0.00';
                const stockHint = row.querySelector('.stock-hint');
                if (stockHint) stockHint.innerHTML = '';
                calculateRowTotal(idx);
            }
        }

        function onUnitChange(idx) {
            const row = document.getElementById(`row_${idx}`);
            if (!row) return;
            const unitSelect = row.querySelector('.unit-select');
            const priceInput = row.querySelector('.price-input');
            if (!unitSelect || !priceInput) return;

            const selectedOpt = unitSelect.options[unitSelect.selectedIndex];

            if (selectedOpt && selectedOpt.getAttribute('data-price') !== null) {
                const priceVal = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
                priceInput.value = priceVal.toFixed(2);
            }
            calculateRowTotal(idx);
        }

        function updateAllRowsStockHints() {
            const warehouseSelect = document.querySelector('select[name="warehouse_id"]');
            const selectedWh = warehouseSelect ? warehouseSelect.value : null;

            document.querySelectorAll('#invoiceItemsTable tbody tr').forEach(tr => {
                const itemId = tr.querySelector('.item-select').value;
                const stockHint = tr.querySelector('.stock-hint');
                if (itemId && selectedWh && stockHint) {
                    const item = inventoryItems.find(i => i.id == itemId);
                    const availStock = getWarehouseStockForItem(itemId, selectedWh);
                    const baseUnitName = item?.base_unit?.name || 'قطعة';
                    if (availStock > 0) {
                        stockHint.className = 'stock-hint text-success font-mono fs-8 d-block mt-1';
                        stockHint.innerHTML = `<i class="bi bi-check-circle me-1"></i>المتاح بالمخزن المختار: ${availStock} ${baseUnitName}`;
                    } else {
                        stockHint.className = 'stock-hint text-danger font-mono fs-8 d-block mt-1';
                        stockHint.innerHTML = `<i class="bi bi-x-circle me-1"></i>غير متوفر بالمخزن المختار (0)`;
                    }
                }
            });
        }

        function calculateRowTotal(idx) {
            const row = document.getElementById(`row_${idx}`);
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = qty * price;

            row.querySelector('.row-subtotal').innerText = subtotal.toFixed(2);
            updateAllRowsStockHints();
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grandSubtotal = 0;
            document.querySelectorAll('#invoiceItemsTable tbody tr').forEach(tr => {
                const sub = parseFloat(tr.querySelector('.row-subtotal').innerText) || 0;
                grandSubtotal += sub;
            });

            const taxRate = {{ (float) setting('tax_percentage', 15.00) }} / 100;
            const tax = grandSubtotal * taxRate;
            const total = grandSubtotal + tax;

            document.getElementById('subtotalDisplay').innerText = grandSubtotal.toFixed(2);
            document.getElementById('taxDisplay').innerText = tax.toFixed(2);
            document.getElementById('totalDisplay').innerText = total.toFixed(2);

            calculatePaymentBreakdown();
        }

        function togglePaymentBreakdown() {
            const payType = document.getElementById('paymentTypeSelect').value;
            const card = document.getElementById('paymentBreakdownCard');
            const cashCol = document.getElementById('cashAccountCol');
            const bankCol = document.getElementById('bankAccountCol');
            const splitCol = document.getElementById('splitAmountsCol');
            const cashInput = document.getElementById('cashAmountInput');
            const bankInput = document.getElementById('bankAmountInput');

            const totalText = document.getElementById('totalDisplay').innerText;
            const total = parseFloat(totalText) || 0;

            if (!payType || payType === '') {
                if (card) card.style.display = 'none';
                if (cashCol) cashCol.style.display = 'none';
                if (bankCol) bankCol.style.display = 'none';
                if (splitCol) splitCol.style.display = 'none';
                if (cashInput) cashInput.value = '0.00';
                if (bankInput) bankInput.value = '0.00';
                return;
            }

            if (payType === 'cash') {
                if (card) card.style.display = 'block';
                cashCol.style.display = 'block';
                bankCol.style.display = 'none';
                splitCol.style.display = 'none';
                cashInput.value = total.toFixed(2);
                bankInput.value = '0.00';
            } else if (payType === 'bank') {
                if (card) card.style.display = 'block';
                cashCol.style.display = 'none';
                bankCol.style.display = 'block';
                splitCol.style.display = 'none';
                cashInput.value = '0.00';
                bankInput.value = total.toFixed(2);
            } else if (payType === 'credit') {
                if (card) card.style.display = 'none';
                cashCol.style.display = 'none';
                bankCol.style.display = 'none';
                splitCol.style.display = 'none';
                cashInput.value = '0.00';
                bankInput.value = '0.00';
            } else if (payType === 'split') {
                if (card) card.style.display = 'block';
                cashCol.style.display = 'block';
                bankCol.style.display = 'block';
                splitCol.style.display = 'block';
            }
            calculatePaymentBreakdown();
        }

        function calculatePaymentBreakdown() {
            const payType = document.getElementById('paymentTypeSelect').value;
            const total = parseFloat(document.getElementById('totalDisplay').innerText) || 0;
            const cashInput = document.getElementById('cashAmountInput');
            const bankInput = document.getElementById('bankAmountInput');
            const dueDisplay = document.getElementById('dueAmountDisplay');

            let cash = parseFloat(cashInput.value) || 0;
            let bank = parseFloat(bankInput.value) || 0;

            if (payType === 'cash') {
                cash = total;
                bank = 0;
            } else if (payType === 'bank') {
                cash = 0;
                bank = total;
            } else if (payType === 'credit') {
                cash = 0;
                bank = 0;
            }

            const paidTotal = cash + bank;
            const due = Math.max(0, total - paidTotal);

            if (dueDisplay) {
                dueDisplay.innerText = due.toFixed(2) + ' ' + '{{ currency() }}';
            }
        }

        function removeRow(idx) {
            const row = document.getElementById(`row_${idx}`);
            if (row) {
                row.remove();
                calculateGrandTotal();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            addInvoiceRow();

            const warehouseSelect = document.querySelector('select[name="warehouse_id"]');
            if (warehouseSelect) {
                warehouseSelect.addEventListener('change', () => {
                    updateWarehouseSelectionState();
                    updateAllRowsStockHints();
                });
                if (window.jQuery) {
                    $(warehouseSelect).on('change', () => {
                        updateWarehouseSelectionState();
                        updateAllRowsStockHints();
                    });
                }
            }

            const paymentTypeSelect = document.getElementById('paymentTypeSelect');
            if (paymentTypeSelect) {
                paymentTypeSelect.addEventListener('change', togglePaymentBreakdown);
                if (window.jQuery) {
                    $(paymentTypeSelect).on('change', togglePaymentBreakdown);
                }
            }

            updateWarehouseSelectionState();
            togglePaymentBreakdown();

            const invoiceForm = document.getElementById('invoiceForm');
            if (invoiceForm) {
                invoiceForm.addEventListener('submit', function(e) {
                    const selectedWh = warehouseSelect ? warehouseSelect.value : null;
                    if (!selectedWh) {
                        e.preventDefault();
                        alert('يرجى اختيار المخزن أولاً قبل حفظ الفاتورة.');
                        return;
                    }

                    let itemTotalsBase = {};
                    let hasError = false;
                    let errorMessage = '';

                    const rows = document.querySelectorAll('#invoiceItemsTable tbody tr');
                    rows.forEach(tr => {
                        const itemId = tr.querySelector('.item-select').value;
                        const unitId = tr.querySelector('.unit-select').value;
                        const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
                        const item = inventoryItems.find(i => i.id == itemId);

                        if (itemId && unitId && qty > 0 && item) {
                            const isWholesale = (unitId == item.wholesale_unit_id);
                            const factor = isWholesale ? (parseFloat(item.conversion_factor) || 1) : 1;
                            const qtyInBase = qty * factor;
                            itemTotalsBase[itemId] = (itemTotalsBase[itemId] || 0) + qtyInBase;
                        }
                    });

                    for (const itemId in itemTotalsBase) {
                        const requiredBase = itemTotalsBase[itemId];
                        const availStock = getWarehouseStockForItem(itemId, selectedWh);
                        const item = inventoryItems.find(i => i.id == itemId);

                        if (requiredBase > availStock) {
                            hasError = true;
                            const unitName = item?.base_unit?.name || 'قطعة';
                            errorMessage = `عفواً، الكمية المطلوبة من الصنف (${item?.name || ''}) وهي ${requiredBase} ${unitName} تتجاوز الرصيد المتوفر بالمخزن المختار (${availStock} ${unitName}).`;
                            break;
                        }
                    }

                    if (hasError) {
                        e.preventDefault();
                        alert(errorMessage);
                    }
                });
            }
        });
    </script>
</x-app-layout>

