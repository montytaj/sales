<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('quotations.index'), 'label' => __('sales.quotations_list')],
                ['label' => __('sales.create_quotation')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-file-earmark-plus-fill text-primary me-2"></i>{{ __('sales.create_quotation') }}
        </h2>
    </x-slot>

    <form method="POST" action="{{ route('quotations.store') }}" id="quotationForm">
        @csrf
        @if ($selectedOrder)
            <input type="hidden" name="customer_order_id" value="{{ $selectedOrder->id }}">
        @endif

        <div class="row g-4">
            <!-- Left Info Card -->
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-person me-2"></i>بيانات العميل والتاريخ</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label font-semibold">{{ __('customers.name') }} <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">-- اختر العميل --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $selectedOrder?->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="branch_id" class="form-label font-semibold">الفرع</label>
                            <select name="branch_id" id="branch_id" class="form-select">
                                <option value="">-- الفرع الرئيسي --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $selectedOrder?->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="issue_date" class="form-label font-semibold">{{ __('sales.issue_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="expiry_date" class="form-label font-semibold">{{ __('sales.expiry_date') }}</label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ old('expiry_date', date('Y-m-d', strtotime('+14 days'))) }}">
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label font-semibold">ملاحظات عامة</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="terms_conditions" class="form-label font-semibold">الشروط والأحكام</label>
                            <textarea name="terms_conditions" id="terms_conditions" rows="3" class="form-control" placeholder="الشروط، مدة التوريد، الدفعة المقدمة المطلوبة...">{{ old('terms_conditions', '1. الأسعار تشمل ضريبة القيمة المضافة 15%.\n2. الدفعة المقدمة 50% عند اعتماد العرض.\n3. العرض ساري لمدة 14 يوماً من تاريخه.') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Items Card -->
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-bold text-dark"><i class="bi bi-list-task me-2"></i>{{ __('sales.items') }}</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('sales.add_item') }}
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">اسم البند / الخدمة</th>
                                        <th style="width: 15%;">الكمية</th>
                                        <th style="width: 15%;">الوحدة</th>
                                        <th style="width: 18%;">السعر ({{ setting('currency', 'SDG') }})</th>
                                        <th style="width: 15%;">الخصم</th>
                                        <th style="width: 7%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    <!-- Dynamic rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('quotations.index') }}" class="btn btn-light border">إلغاء</a>
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-check-lg me-2"></i>حفظ عرض السعر
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemsContainer = document.getElementById('itemsContainer');
            const addItemBtn = document.getElementById('addItemBtn');
            let itemIndex = 0;

            const servicesData = @json($services);
            const serviceUnits = @json(__('services.units'));

            function createItemRow(data = {}) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <input type="text" name="items[${itemIndex}][item_name]" class="form-control mb-1" placeholder="اسم البند" value="${data.item_name || ''}" required>
                        <select name="items[${itemIndex}][service_id]" class="form-select form-select-sm service-select">
                            <option value="">-- ربط بخدمة من الدليل --</option>
                            ${servicesData.map(s => `<option value="${s.id}" data-price="${s.default_price}" data-unit="${s.unit_of_measure}">${s.name_ar} (${s.default_price} {{ setting('currency', 'SDG') }})</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.0001" name="items[${itemIndex}][quantity]" class="form-control text-center" value="${data.quantity || '1'}" required>
                    </td>
                    <td>
                        <select name="items[${itemIndex}][unit_of_measure]" class="form-select text-center" required>
                            ${Object.entries(serviceUnits).map(([k, v]) => `<option value="${k}">${v}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control text-center" value="${data.unit_price || '0.00'}" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${itemIndex}][discount_amount]" class="form-control text-center" value="${data.discount_amount || '0.00'}">
                        <input type="hidden" name="items[${itemIndex}][tax_percent]" value="{{ setting('tax_percentage', 15.00) }}">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="bi bi-trash"></i></button>
                    </td>
                `;

                // Handle service selection auto-fill
                const serviceSelect = tr.querySelector('.service-select');
                serviceSelect.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    if (opt.value) {
                        tr.querySelector('input[name$="[item_name]"]').value = opt.text.split(' (')[0];
                        tr.querySelector('input[name$="[unit_price]"]').value = opt.dataset.price;
                        tr.querySelector('select[name$="[unit_of_measure]"]').value = opt.dataset.unit;
                    }
                });

                tr.querySelector('.remove-row-btn').addEventListener('click', function() {
                    if (itemsContainer.children.length > 1) {
                        tr.remove();
                    }
                });

                itemsContainer.appendChild(tr);
                itemIndex++;
            }

            addItemBtn.addEventListener('click', function() {
                createItemRow();
            });

            // Initial row
            createItemRow();
        });
    </script>
</x-app-layout>
