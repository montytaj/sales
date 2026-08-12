<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-cart-plus text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'تسجيل فاتورة شراء جديدة' : 'Record New Purchase Invoice' }}
            </h2>
            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>الرجوع للمشتريات
            </a>
        </div>
    </x-slot>

    <form action="{{ route('purchases.store_invoice') }}" method="POST" id="purchaseInvoiceForm">
        @csrf
        <div class="row g-4 mb-4">
            <!-- Header Information -->
            <div class="col-md-4">
                <label class="form-label font-bold">المورد <span class="text-danger">*</span></label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">-- اختر المورد --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label font-bold">المخزن المستلم للبضاعة <span class="text-danger">*</span></label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">-- اختر المخزن المستلم --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label font-bold">تاريخ الشراء <span class="text-danger">*</span></label>
                <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label font-bold">طريقة الدفع <span class="text-danger">*</span></label>
                <select name="payment_type" id="paymentTypeSelect" class="form-select" onchange="togglePaymentBreakdown()" required>
                    <option value="" selected disabled>-- {{ app()->getLocale() == 'ar' ? 'اختر طريقة السداد' : 'Select Payment Method' }} --</option>
                    <option value="cash">{{ app()->getLocale() == 'ar' ? 'نقدي بالكامل' : 'Cash' }}</option>
                    <option value="bank">{{ app()->getLocale() == 'ar' ? 'تحويل بنكي / شبكة بالكامل' : 'Bank' }}</option>
                    <option value="credit">{{ app()->getLocale() == 'ar' ? 'آجل بالكامل للمورد' : 'Credit' }}</option>
                    <option value="split">{{ app()->getLocale() == 'ar' ? 'دفع متعدد / مختلط (كاش + بنك + أجل)' : 'Split Payment' }}</option>
                </select>
            </div>
        </div>

        <!-- Payment Breakdown & Accounts Section -->
        <div class="card shadow-sm border-0 rounded-3 mb-4 bg-light-subtle" id="paymentBreakdownCard" style="display: none;">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i>{{ app()->getLocale() == 'ar' ? 'تفاصيل السداد وحسابات الخزينة والبنك (من شجرة الحسابات)' : 'Payment & Account Details' }}</h5>
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
                                <label class="form-label font-bold text-success mb-1">{{ app()->getLocale() == 'ar' ? 'المبلغ المدفوع نقداً (' . currency() . '):' : 'Cash Paid Amount (' . currency() . '):' }}</label>
                                <input type="number" step="0.01" min="0" name="cash_amount" id="cashAmountInput" class="form-control font-mono" value="0.00" oninput="calculatePaymentBreakdown()">
                            </div>
                            <div class="mb-2">
                                <label class="form-label font-bold text-primary mb-1">{{ app()->getLocale() == 'ar' ? 'المبلغ المدفوع بنك/شبكة (' . currency() . '):' : 'Bank Paid Amount (' . currency() . '):' }}</label>
                                <input type="number" step="0.01" min="0" name="bank_amount" id="bankAmountInput" class="form-control font-mono" value="0.00" oninput="calculatePaymentBreakdown()">
                            </div>
                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                <span class="font-bold text-muted">{{ app()->getLocale() == 'ar' ? 'المبلغ الآجل للمورد (المتبقي):' : 'Remaining Supplier Credit:' }}</span>
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
                <h5 class="mb-0 font-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i>أصناف الشراء والكميات (اختيار الوحدة جملة أو تجزئة)</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addPurchaseRow()">
                    <i class="bi bi-plus-circle me-1"></i>إضافة بند شراء آخر
                </button>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="purchaseItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">الصنف <span class="text-danger">*</span></th>
                                <th style="width: 20%">وحدة الشراء (جملة/قطاعي) <span class="text-danger">*</span></th>
                                <th style="width: 15%">الكمية المشتراة <span class="text-danger">*</span></th>
                                <th style="width: 15%">سعر الشراء للوحدة <span class="text-danger">*</span></th>
                                <th style="width: 15%">الإجمالي الفرعي</th>
                                <th style="width: 5%">إزالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows inserted dynamically -->
                        </tbody>
                        <tfoot class="table-light font-bold">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">المجموع قبل الضريبة:</td>
                                <td colspan="2"><span id="subtotalDisplay" class="fs-6 font-mono text-dark">0.00</span> ر.س</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">ضريبة القيمة المضافة ({{ setting('tax_percentage', 15.00) }}%):</td>
                                <td colspan="2"><span id="taxDisplay" class="fs-6 font-mono text-danger">0.00</span> ر.س</td>
                            </tr>
                            <tr class="table-primary fs-5">
                                <td colspan="4" class="text-end fw-bold">إجمالي الفاتورة النهائي:</td>
                                <td colspan="2"><span id="totalDisplay" class="fw-bold font-mono text-primary">0.00</span> ر.س</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <label class="form-label">ملاحظات الفاتورة والشحن</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="ملاحظات الاستلام من المورد أو الفاتورة الورقية"></textarea>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success btn-lg w-100 py-3 font-bold">
                    <i class="bi bi-check-circle-fill me-2"></i>حفظ الفاتورة وتزويد المخزن
                </button>
            </div>
        </div>
    </form>

    <script>
        const inventoryItems = @json($items);
        const units = @json($units);
        let rowIndex = 0;

        function addPurchaseRow() {
            const tableBody = document.querySelector('#purchaseItemsTable tbody');
            const tr = document.createElement('tr');
            const rIdx = rowIndex;
            tr.id = `prow_${rIdx}`;

            let itemOptions = `<option value="">-- اختر الصنف --</option>`;
            inventoryItems.forEach(item => {
                itemOptions += `<option value="${item.id}" data-cost-price="${item.cost_price}" data-wholesale-unit-id="${item.wholesale_unit_id}" data-base-unit-id="${item.base_unit_id}">${item.name} (${item.code || ''})</option>`;
            });

            tr.innerHTML = `
                <td>
                    <select name="items[${rIdx}][inventory_item_id]" class="form-select item-select" onchange="onItemChange(${rIdx})" required>
                        ${itemOptions}
                    </select>
                </td>
                <td>
                    <select name="items[${rIdx}][unit_id]" class="form-select unit-select" onchange="onUnitChange(${rIdx})" required>
                        <option value="">-- حدد الوحدة --</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${rIdx}][quantity]" class="form-control qty-input" value="1" oninput="calculateRowTotal(${rIdx})" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${rIdx}][unit_price]" class="form-control price-input" value="0.00" oninput="calculateRowTotal(${rIdx})" required>
                </td>
                <td>
                    <span class="row-subtotal font-mono fw-bold text-dark">0.00</span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${rIdx})"><i class="bi bi-trash"></i></button>
                </td>
            `;

            tableBody.appendChild(tr);

            if (window.initSelect2) {
                window.initSelect2(tr);
            }

            if (window.jQuery) {
                const $tr = $(tr);
                $tr.find('.item-select').on('change', function() {
                    onItemChange(rIdx);
                });
                $tr.find('.unit-select').on('change', function() {
                    onUnitChange(rIdx);
                });
            }

            rowIndex++;
        }

        function onItemChange(idx) {
            const row = document.getElementById(`prow_${idx}`);
            if (!row) return;
            const itemSelect = row.querySelector('.item-select');
            const unitSelect = row.querySelector('.unit-select');

            const itemId = itemSelect.value;
            const item = inventoryItems.find(i => i.id == itemId);

            unitSelect.innerHTML = '';
            if (item) {
                const cost = parseFloat(item.cost_price) || parseFloat(item.default_purchase_price) || 0;
                const factor = parseFloat(item.conversion_factor) || 1;
                const wholesaleCost = cost * factor;

                if (item.wholesale_unit) {
                    unitSelect.innerHTML += `<option value="${item.wholesale_unit_id}" data-price="${wholesaleCost}">كرتونة / بالجملة (${item.wholesale_unit.name})</option>`;
                }
                if (item.base_unit) {
                    unitSelect.innerHTML += `<option value="${item.base_unit_id}" data-price="${cost}">قطعة / بالفرادي (${item.base_unit.name})</option>`;
                }
                if (!item.wholesale_unit && !item.base_unit) {
                    const unitName = item.unit || 'وحدة';
                    const unitId = item.base_unit_id || item.wholesale_unit_id || 1;
                    unitSelect.innerHTML += `<option value="${unitId}" data-price="${cost}">${unitName}</option>`;
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
                calculateRowTotal(idx);
            }
        }

        function onUnitChange(idx) {
            const row = document.getElementById(`prow_${idx}`);
            const unitSelect = row.querySelector('.unit-select');
            const priceInput = row.querySelector('.price-input');
            if (!unitSelect || !priceInput) return;

            const selectedOpt = unitSelect.options[unitSelect.selectedIndex];

            if (selectedOpt && selectedOpt.getAttribute('data-price') !== null) {
                priceInput.value = parseFloat(selectedOpt.getAttribute('data-price')).toFixed(2);
            }
            calculateRowTotal(idx);
        }

        function calculateRowTotal(idx) {
            const row = document.getElementById(`prow_${idx}`);
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = qty * price;

            row.querySelector('.row-subtotal').innerText = subtotal.toFixed(2);
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grandSubtotal = 0;
            document.querySelectorAll('#purchaseItemsTable tbody tr').forEach(tr => {
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
            const row = document.getElementById(`prow_${idx}`);
            if (row) {
                row.remove();
                calculateGrandTotal();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            addPurchaseRow();
            togglePaymentBreakdown();
        });
    </script>
</x-app-layout>
