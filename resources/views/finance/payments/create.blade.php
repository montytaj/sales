<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('payments.index'), 'label' => __('payments.vouchers_list')],
                ['label' => __('payments.create_voucher')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>{{ __('payments.create_voucher') }}
        </h2>
    </x-slot>

    <form method="POST" action="{{ route('payments.store') }}" id="voucherForm">
        @csrf

        <div class="row g-4">
            <!-- Main Voucher Info Card -->
            <div class="col-12 col-lg-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-info-circle me-2"></i>بيانات السند الرئيسي</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="type" class="form-label font-semibold">{{ __('payments.voucher_type') }} <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="receipt" {{ old('type', request('type')) === 'receipt' ? 'selected' : '' }}>سند قبض (تحصيل من عميل)</option>
                                <option value="payment" {{ old('type', request('type', $selectedPurchaseInvoice ? 'payment' : 'receipt')) === 'payment' ? 'selected' : '' }}>سند صرف (دفع لمورد / مصروفات)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="cashbox_id" class="form-label font-semibold">الخزنة / الصندوق المستهدف <span class="text-danger">*</span></label>
                            <select name="cashbox_id" id="cashbox_id" class="form-select @error('cashbox_id') is-invalid @enderror" required>
                                <option value="">-- اختر الخزنة --</option>
                                @foreach ($cashboxes as $cb)
                                    <option value="{{ $cb->id }}" {{ old('cashbox_id') == $cb->id ? 'selected' : '' }}>
                                        {{ $cb->name_ar }} (الرصيد الحالي: {{ number_format($cb->current_balance, 2) }} {{ setting('currency', 'SDG') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('cashbox_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Customer Dropdown (Receipts) -->
                        <div class="mb-3" id="customerSection">
                            <label for="customer_id" class="form-label font-semibold">{{ __('customers.name') }}</label>
                            <select name="customer_id" id="customer_id" class="form-select">
                                <option value="">-- اختر العميل --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $selectedInvoice?->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Supplier Dropdown (Payments) -->
                        <div class="mb-3 d-none" id="supplierSection">
                            <label for="supplier_id" class="form-label font-semibold">اسم المورد</label>
                            <select name="supplier_id" id="supplier_id" class="form-select">
                                <option value="">-- اختر المورد --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $selectedPurchaseInvoice?->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} ({{ $supplier->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Purchase Invoice Selection (Payments) -->
                        <div class="mb-3 d-none" id="purchaseInvoiceSection">
                            <label for="purchase_invoice_id" class="form-label font-semibold">ربط بفاتورة مشتريات (سداد آجل / جزئي)</label>
                            <select name="purchase_invoice_id" id="purchase_invoice_id" class="form-select">
                                <option value="">-- بدون ربط بفاتورة (صرف عام للمورد) --</option>
                                @foreach ($purchaseInvoices as $pinv)
                                    <option value="{{ $pinv->id }}" 
                                        data-supplier="{{ $pinv->supplier_id }}"
                                        data-due="{{ $pinv->due_amount }}"
                                        data-net="{{ $pinv->net_amount }}"
                                        data-paid="{{ $pinv->total_paid }}"
                                        {{ old('purchase_invoice_id', $selectedPurchaseInvoice?->id) == $pinv->id ? 'selected' : '' }}>
                                        {{ $pinv->invoice_number }} - {{ $pinv->supplier?->name }} (المستحق: {{ number_format($pinv->due_amount, 2) }} {{ setting('currency', 'ر.س') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Live Calculation Card for Purchase Invoice -->
                        <div class="p-3 bg-light border border-info rounded mb-3 d-none" id="purchaseCalcBox">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small"><i class="bi bi-receipt me-1"></i>صافي الفاتورة الإجمالي:</span>
                                <strong class="text-dark font-mono" id="calcNet">0.00 {{ setting('currency', 'ر.س') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>المدفوع سابقاً:</span>
                                <strong class="text-success font-mono" id="calcPaid">0.00 {{ setting('currency', 'ر.س') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-danger small font-semibold"><i class="bi bi-hourglass-split me-1"></i>المبلغ المستحق حالياً (قبل السداد):</span>
                                <strong class="text-danger font-mono fs-6" id="calcDue">0.00 {{ setting('currency', 'ر.س') }}</strong>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center pt-1">
                                <span class="text-primary font-semibold small"><i class="bi bi-calculator me-1"></i>المتبقي كدين بعد السداد الحالي:</span>
                                <strong class="text-primary font-mono fs-6" id="calcRemainingAfter">0.00 {{ setting('currency', 'ر.س') }}</strong>
                            </div>
                        </div>

                        <div class="mb-3" id="invoiceSection">
                            <label for="invoice_id" class="form-label font-semibold">ربط بفاتورة مبيعات</label>
                            <select name="invoice_id" id="invoice_id" class="form-select">
                                <option value="">-- بدون ربط بفاتورة (قبض عام) --</option>
                                @foreach ($invoices as $inv)
                                    <option value="{{ $inv->id }}" {{ old('invoice_id', $selectedInvoice?->id) == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->invoice_number }} - {{ $inv->customer->name }} (المبلغ: {{ number_format($inv->total_amount, 2) }} {{ setting('currency', 'SDG') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label font-semibold">{{ __('payments.amount') }} ({{ setting('currency', 'SDG') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control form-control-lg fw-bold text-success" value="{{ old('amount', $selectedPurchaseInvoice?->due_amount ?? ($selectedInvoice?->total_amount ?? '0.00')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="payment_date" class="form-label font-semibold">{{ __('payments.payment_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label font-semibold">ملاحظات وقيد السند</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Split Payments Card -->
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-wallet2 me-2"></i>وسائل الدفع والتوزيع (المدفوعات المختلطة)</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addMethodBtn">
                            <i class="bi bi-plus-lg me-1"></i>إضافة طريقة دفع
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;">طريقة الدفع</th>
                                        <th style="width: 30%;">الحساب الفرعي (شجرة الحسابات)</th>
                                        <th style="width: 20%;">المبلغ (ر.س)</th>
                                        <th style="width: 20%;">رقم المرجع / العملية</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="linesContainer">
                                    <!-- Dynamic rows -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Cheque Specific Form Fields (Shown if cheque is chosen) -->
                        <div id="chequeFields" class="p-3 bg-light border rounded mt-3 d-none">
                            <h6 class="font-bold text-dark mb-3"><i class="bi bi-bank me-2"></i>تفاصيل الشيك المدخل:</h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="cheque_number" class="form-label font-semibold">رقم الشيك <span class="text-danger">*</span></label>
                                    <input type="text" name="cheque_number" id="cheque_number" class="form-control" value="{{ old('cheque_number') }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="bank_name" class="form-label font-semibold">اسم البنك المصدر <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ old('bank_name') }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="drawer_name" class="form-label font-semibold">اسم الساحب / صاحب الشيك <span class="text-danger">*</span></label>
                                    <input type="text" name="drawer_name" id="drawer_name" class="form-control" value="{{ old('drawer_name') }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label for="issue_date" class="form-label font-semibold">تاريخ الإصدار</label>
                                    <input type="date" name="issue_date" id="issue_date" class="form-control" value="{{ old('issue_date', date('Y-m-d')) }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label for="due_date" class="form-label font-semibold">تاريخ الاستحقاق</label>
                                    <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('payments.index') }}" class="btn btn-light border">إلغاء</a>
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-check-lg me-2"></i>حفظ وإصدار السند
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const linesContainer = document.getElementById('linesContainer');
            const addMethodBtn = document.getElementById('addMethodBtn');
            const chequeFields = document.getElementById('chequeFields');
            const amountInput = document.getElementById('amount');
            let lineIndex = 0;

            const paymentMethods = @json(__('payments.methods'));
            const accounts = @json($accounts ?? []);

            function toggleChequeSection() {
                const selects = document.querySelectorAll('.payment-method-select');
                let hasCheque = false;
                selects.forEach(s => {
                    if (s.value === 'cheque') hasCheque = true;
                });

                if (hasCheque) {
                    chequeFields.classList.remove('d-none');
                } else {
                    chequeFields.classList.add('d-none');
                }
            }

            function createLineRow(data = {}) {
                let accountOptions = '<option value="">-- اختر الحساب (اختياري) --</option>';
                accounts.forEach(acc => {
                    accountOptions += `<option value="${acc.id}">${acc.name}</option>`;
                });

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <select name="lines[${lineIndex}][payment_method]" class="form-select payment-method-select" required>
                            ${Object.entries(paymentMethods).map(([k, v]) => `<option value="${k}">${v}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <select name="lines[${lineIndex}][account_id]" class="form-select">
                            ${accountOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="lines[${lineIndex}][amount]" class="form-control text-center line-amount-input" value="${data.amount || amountInput.value || '0.00'}" required>
                    </td>
                    <td>
                        <input type="text" name="lines[${lineIndex}][reference_number]" class="form-control" placeholder="رقم العملية / الحوالة" value="${data.reference_number || ''}">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="bi bi-trash"></i></button>
                    </td>
                `;

                const select = tr.querySelector('.payment-method-select');
                select.addEventListener('change', toggleChequeSection);

                tr.querySelector('.remove-row-btn').addEventListener('click', function() {
                    if (linesContainer.children.length > 1) {
                        tr.remove();
                        toggleChequeSection();
                    }
                });

                linesContainer.appendChild(tr);
                lineIndex++;
                toggleChequeSection();
            }

            addMethodBtn.addEventListener('click', function() {
                createLineRow();
            });

            amountInput.addEventListener('input', function() {
                const firstLineInput = linesContainer.querySelector('.line-amount-input');
                if (firstLineInput && linesContainer.children.length === 1) {
                    firstLineInput.value = this.value;
                }
            });

            // Initial row
            createLineRow();

            // Toggle Customer vs Supplier based on Voucher Type (Receipt vs Payment)
            const typeSelect = document.getElementById('type');
            const customerSection = document.getElementById('customerSection');
            const supplierSection = document.getElementById('supplierSection');
            const invoiceSection = document.getElementById('invoiceSection');
            const purchaseInvoiceSection = document.getElementById('purchaseInvoiceSection');
            const purchaseCalcBox = document.getElementById('purchaseCalcBox');
            const customerSelect = document.getElementById('customer_id');
            const supplierSelect = document.getElementById('supplier_id');
            const purchaseInvoiceSelect = document.getElementById('purchase_invoice_id');

            const calcNet = document.getElementById('calcNet');
            const calcPaid = document.getElementById('calcPaid');
            const calcDue = document.getElementById('calcDue');
            const calcRemainingAfter = document.getElementById('calcRemainingAfter');

            function updatePurchaseInvoiceCalc() {
                if (!purchaseInvoiceSelect || !purchaseInvoiceSelect.value) {
                    if (purchaseCalcBox) purchaseCalcBox.classList.add('d-none');
                    return;
                }

                const opt = purchaseInvoiceSelect.options[purchaseInvoiceSelect.selectedIndex];
                if (!opt || !opt.dataset.due) {
                    if (purchaseCalcBox) purchaseCalcBox.classList.add('d-none');
                    return;
                }

                const net = parseFloat(opt.dataset.net || 0);
                const paid = parseFloat(opt.dataset.paid || 0);
                const due = parseFloat(opt.dataset.due || 0);
                const currentPay = parseFloat(amountInput.value || 0);

                if (purchaseCalcBox) purchaseCalcBox.classList.remove('d-none');
                if (calcNet) calcNet.textContent = net.toFixed(2) + ' {{ setting('currency', 'ر.س') }}';
                if (calcPaid) calcPaid.textContent = paid.toFixed(2) + ' {{ setting('currency', 'ر.س') }}';
                if (calcDue) calcDue.textContent = due.toFixed(2) + ' {{ setting('currency', 'ر.س') }}';

                const remainingAfter = Math.max(0, due - currentPay);
                if (calcRemainingAfter) calcRemainingAfter.textContent = remainingAfter.toFixed(2) + ' {{ setting('currency', 'ر.س') }}';
            }

            function filterPurchaseInvoicesBySupplier() {
                if (!supplierSelect || !purchaseInvoiceSelect) return;
                const supId = supplierSelect.value;
                let firstMatchingDue = null;

                Array.from(purchaseInvoiceSelect.options).forEach(opt => {
                    if (!opt.value) return; // Keep placeholder
                    if (!supId || opt.dataset.supplier == supId) {
                        opt.style.display = '';
                        if (!firstMatchingDue && opt.dataset.due) {
                            firstMatchingDue = opt.dataset.due;
                        }
                    } else {
                        opt.style.display = 'none';
                        if (opt.selected) purchaseInvoiceSelect.value = '';
                    }
                });
            }

            function toggleVoucherType() {
                const type = typeSelect ? typeSelect.value : 'receipt';
                if (type === 'payment') {
                    if (supplierSection) supplierSection.classList.remove('d-none');
                    if (purchaseInvoiceSection) purchaseInvoiceSection.classList.remove('d-none');
                    if (customerSection) customerSection.classList.add('d-none');
                    if (invoiceSection) invoiceSection.classList.add('d-none');
                    if (customerSelect) customerSelect.value = '';
                } else if (type === 'receipt') {
                    if (customerSection) customerSection.classList.remove('d-none');
                    if (invoiceSection) invoiceSection.classList.remove('d-none');
                    if (supplierSection) supplierSection.classList.add('d-none');
                    if (purchaseInvoiceSection) purchaseInvoiceSection.classList.add('d-none');
                    if (purchaseCalcBox) purchaseCalcBox.classList.add('d-none');
                    if (supplierSelect) supplierSelect.value = '';
                    if (purchaseInvoiceSelect) purchaseInvoiceSelect.value = '';
                } else {
                    if (customerSection) customerSection.classList.add('d-none');
                    if (supplierSection) supplierSection.classList.add('d-none');
                    if (invoiceSection) invoiceSection.classList.add('d-none');
                    if (purchaseInvoiceSection) purchaseInvoiceSection.classList.add('d-none');
                    if (purchaseCalcBox) purchaseCalcBox.classList.add('d-none');
                    if (customerSelect) customerSelect.value = '';
                    if (supplierSelect) supplierSelect.value = '';
                    if (purchaseInvoiceSelect) purchaseInvoiceSelect.value = '';
                }
            }

            if (typeSelect) {
                typeSelect.addEventListener('change', toggleVoucherType);
                toggleVoucherType();
            }

            if (supplierSelect) {
                supplierSelect.addEventListener('change', function() {
                    filterPurchaseInvoicesBySupplier();
                    updatePurchaseInvoiceCalc();
                });
            }

            if (purchaseInvoiceSelect) {
                purchaseInvoiceSelect.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    if (opt && opt.dataset.due) {
                        amountInput.value = parseFloat(opt.dataset.due).toFixed(2);
                        const firstLineInput = linesContainer.querySelector('.line-amount-input');
                        if (firstLineInput) firstLineInput.value = amountInput.value;
                    }
                    updatePurchaseInvoiceCalc();
                });
            }

            amountInput.addEventListener('input', function() {
                updatePurchaseInvoiceCalc();
            });

            // Run initial calculations if pre-selected
            filterPurchaseInvoicesBySupplier();
            updatePurchaseInvoiceCalc();
        });
    </script>
</x-app-layout>
