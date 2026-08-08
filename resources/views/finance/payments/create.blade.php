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
                                <option value="receipt" {{ old('type') === 'receipt' ? 'selected' : '' }}>سند قبض (تحصيل من عميل)</option>
                                <option value="payment" {{ old('type') === 'payment' ? 'selected' : '' }}>سند صرف (دفع لمورد / مصروفات)</option>
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

                        <div class="mb-3">
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

                        <div class="mb-3">
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
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control form-control-lg fw-bold text-success" value="{{ old('amount', $selectedInvoice?->total_amount ?? '0.00') }}" required>
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
                                        <th style="width: 35%;">طريقة الدفع</th>
                                        <th style="width: 30%;">المبلغ ({{ setting('currency', 'SDG') }})</th>
                                        <th style="width: 25%;">رقم المرجع / العملية</th>
                                        <th style="width: 10%;"></th>
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
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <select name="lines[${lineIndex}][payment_method]" class="form-select payment-method-select" required>
                            ${Object.entries(paymentMethods).map(([k, v]) => `<option value="${k}">${v}</option>`).join('')}
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
        });
    </script>
</x-app-layout>
