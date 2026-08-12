<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('purchases.index'), 'label' => __('المشتريات')],
                ['url' => route('purchases.payables'), 'label' => 'مستحقات الموردين'],
                ['label' => 'سداد فاتورة: ' . $invoice->invoice_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-wallet2 text-success me-2"></i>سداد فاتورة شراء آجل: <span class="font-mono text-primary">{{ $invoice->invoice_number }}</span>
            </h2>
            <a href="{{ route('purchases.payables') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>الرجوع للمستحقات
            </a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('purchases.store_invoice_payment', $invoice) }}" id="payInvoiceForm">
        @csrf

        <div class="row g-4">
            <!-- Left Info Panel: Supplier & Invoice Summary -->
            <div class="col-12 col-lg-5">
                <!-- Supplier Info Card -->
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-building text-primary me-2"></i>بيانات المورد المستحق</h5>
                    </div>
                    <div class="card-body p-4">
                        <h4 class="font-bold text-dark mb-1">{{ $invoice->supplier?->name ?? 'غير محدد' }}</h4>
                        @if($invoice->supplier?->company_name)
                            <div class="text-muted small mb-1"><i class="bi bi-briefcase me-1"></i>{{ $invoice->supplier->company_name }}</div>
                        @endif
                        @if($invoice->supplier?->phone)
                            <div class="text-muted small mb-1"><i class="bi bi-telephone me-1"></i>{{ $invoice->supplier->phone }}</div>
                        @endif
                        @if($invoice->supplier?->vat_number)
                            <div class="text-muted small"><i class="bi bi-card-text me-1"></i>الرقم الضريبي: {{ $invoice->supplier->vat_number }}</div>
                        @endif
                    </div>
                </div>

                <!-- Financial Calculation Card -->
                <div class="card shadow-sm border-0 mb-4 rounded-3 border-start border-4 border-danger">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-calculator text-danger me-2"></i>حالة المستحقات للفاتورة</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">إجمالي الفاتورة الصافي:</span>
                            <strong class="text-dark font-mono fs-6">{{ number_format($invoice->net_amount, 2) }} {{ setting('currency', 'ر.س') }}</strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">المدفوع سابقاً:</span>
                            <strong class="text-success font-mono fs-6">+ {{ number_format($invoice->total_paid, 2) }} {{ setting('currency', 'ر.س') }}</strong>
                        </div>

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center py-2 mb-3 bg-danger-subtle p-3 rounded-3">
                            <span class="font-bold text-danger"><i class="bi bi-hourglass-split me-1"></i>المبلغ المستحق حالياً (الآجل):</span>
                            <h4 class="mb-0 font-bold text-danger font-mono" id="currentDueVal" data-due="{{ $invoice->due_amount }}">
                                {{ number_format($invoice->due_amount, 2) }} {{ setting('currency', 'ر.س') }}
                            </h4>
                        </div>

                        <!-- Live Calculation Result -->
                        <div class="p-3 bg-light border rounded-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary font-semibold small"><i class="bi bi-info-circle me-1"></i>المتبقي كدين بعد هذا السداد:</span>
                                <strong class="text-primary font-mono fs-6" id="remainingAfterPay">0.00 {{ setting('currency', 'ر.س') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form Panel: Payment Form -->
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-cash-stack text-success me-2"></i>تفاصيل إجراء السداد</h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Cashbox Selection -->
                        <div class="mb-3">
                            <label for="cashbox_id" class="form-label font-semibold">الخزنة / الصندوق المسدد منه <span class="text-danger">*</span></label>
                            <select name="cashbox_id" id="cashbox_id" class="form-select form-select-lg @error('cashbox_id') is-invalid @enderror" required>
                                <option value="">-- اختر الخزنة / الصندوق --</option>
                                @foreach ($cashboxes as $cb)
                                    <option value="{{ $cb->id }}" {{ old('cashbox_id') == $cb->id ? 'selected' : '' }}>
                                        {{ $cb->name_ar }} (الرصيد المتاح حالياً: {{ number_format($cb->current_balance, 2) }} {{ setting('currency', 'ر.س') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('cashbox_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Amount Input -->
                            <div class="col-12 col-md-6">
                                <label for="amount" class="form-label font-semibold">المبلغ المراد سداده الآن (ر.س) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" id="amount" 
                                    class="form-control form-control-lg fw-bold text-success font-mono" 
                                    value="{{ old('amount', number_format($invoice->due_amount, 2, '.', '')) }}" 
                                    max="{{ $invoice->due_amount }}" min="0.01" required>
                                <small class="text-muted">ملاحظة: يمكنك سداد المبلغ كاملاً أو خصم مبلغ جزئي.</small>
                            </div>

                            <!-- Payment Date -->
                            <div class="col-12 col-md-6">
                                <label for="payment_date" class="form-label font-semibold">تاريخ عملية السداد <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control form-control-lg" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Payment Method -->
                            <div class="col-12 col-md-6">
                                <label for="payment_method" class="form-label font-semibold">طريقة السداد <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    @foreach (__('payments.methods') as $key => $name)
                                        <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Optional Account -->
                            <div class="col-12 col-md-6">
                                <label for="account_id" class="form-label font-semibold">الحساب الفرعي لشجرة الحسابات (اختياري)</label>
                                <select name="account_id" id="account_id" class="form-select">
                                    <option value="">-- تلقائي (حـ/ الموردين) --</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }} ({{ $acc->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Reference Number -->
                        <div class="mb-3">
                            <label for="reference_number" class="form-label font-semibold">رقم المرجع / الحوالة البنكية / العملية (إن وجد)</label>
                            <input type="text" name="reference_number" id="reference_number" class="form-control" placeholder="مثال: TRX-98421" value="{{ old('reference_number') }}">
                        </div>

                        <!-- Cheque Details Section (Shown if payment_method == 'cheque') -->
                        <div id="chequeFieldsSection" class="p-3 bg-light border rounded mb-3 d-none">
                            <h6 class="font-bold text-dark mb-3"><i class="bi bi-bank me-2 text-primary"></i>تفاصيل الشيك الصادر للمورد:</h6>
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
                                    <label for="drawer_name" class="form-label font-semibold">اسم الساحب / الحساب <span class="text-danger">*</span></label>
                                    <input type="text" name="drawer_name" id="drawer_name" class="form-control" value="{{ old('drawer_name', setting('facility_name')) }}">
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

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label font-semibold">ملاحظات وقيد السند</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="ملاحظات توضيحية حول هذا السداد...">{{ old('notes', "سداد دفعة فاتورة مشتريات رقم " . $invoice->invoice_number) }}</textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('purchases.payables') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-success btn-lg px-5 font-bold">
                                <i class="bi bi-check-circle me-2"></i>حفظ وتأكيد السداد (إصدار سند صرف)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount');
            const currentDueElem = document.getElementById('currentDueVal');
            const remainingAfterElem = document.getElementById('remainingAfterPay');
            const paymentMethodSelect = document.getElementById('payment_method');
            const chequeFieldsSection = document.getElementById('chequeFieldsSection');

            const dueAmount = parseFloat(currentDueElem ? currentDueElem.dataset.due : 0) || 0;

            function updateRemainingDebt() {
                const currentPay = parseFloat(amountInput.value || 0);
                const remaining = Math.max(0, dueAmount - currentPay);
                if (remainingAfterElem) {
                    remainingAfterElem.textContent = remaining.toFixed(2) + ' {{ setting('currency', 'ر.س') }}';
                }
            }

            function toggleChequeSection() {
                if (paymentMethodSelect.value === 'cheque') {
                    chequeFieldsSection.classList.remove('d-none');
                } else {
                    chequeFieldsSection.classList.add('d-none');
                }
            }

            amountInput.addEventListener('input', updateRemainingDebt);
            paymentMethodSelect.addEventListener('change', toggleChequeSection);

            // Initial run
            updateRemainingDebt();
            toggleChequeSection();
        });
    </script>
</x-app-layout>
