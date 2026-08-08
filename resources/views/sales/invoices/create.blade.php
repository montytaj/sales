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
                <select name="payment_type" class="form-select" required>
                    <option value="cash">نقدي (Cash)</option>
                    <option value="bank">تحويل بنكي / شبكة (Bank)</option>
                    <option value="credit">آجل (Credit)</option>
                </select>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 font-bold text-dark"><i class="bi bi-cart-check text-primary me-2"></i>بنود الفاتورة والأصناف (تصفية حسب التصنيف والوحدة)</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addInvoiceRow()">
                    <i class="bi bi-plus-circle me-1"></i>إضافة بند آخر
                </button>
            </div>
            <div class="card-body p-4">
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

        function addInvoiceRow() {
            const tableBody = document.querySelector('#invoiceItemsTable tbody');
            const tr = document.createElement('tr');
            tr.id = `row_${rowIndex}`;

            let catOptions = `<option value="">-- كل التصنيفات --</option>`;
            categories.forEach(cat => {
                catOptions += `<option value="${cat.id}">${cat.name}</option>`;
            });

            tr.innerHTML = `
                <td>
                    <select class="form-select category-select" onchange="onCategoryChange(${rowIndex})">
                        ${catOptions}
                    </select>
                </td>
                <td>
                    <select name="items[${rowIndex}][inventory_item_id]" class="form-select item-select" onchange="onItemChange(${rowIndex})" required>
                        <option value="">-- اختر الصنف --</option>
                    </select>
                </td>
                <td>
                    <select name="items[${rowIndex}][unit_id]" class="form-select unit-select" onchange="onUnitChange(${rowIndex})" required>
                        <option value="">-- حدد الوحدة --</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${rowIndex}][quantity]" class="form-control qty-input" value="1" oninput="calculateRowTotal(${rowIndex})" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control price-input" value="0.00" oninput="calculateRowTotal(${rowIndex})" required>
                </td>
                <td>
                    <span class="row-subtotal font-mono fw-bold text-dark">0.00</span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${rowIndex})"><i class="bi bi-trash"></i></button>
                </td>
            `;

            tableBody.appendChild(tr);
            populateItemsForCategory(rowIndex, '');
            rowIndex++;
        }

        function onCategoryChange(idx) {
            const row = document.getElementById(`row_${idx}`);
            const catId = row.querySelector('.category-select').value;
            populateItemsForCategory(idx, catId);
        }

        function populateItemsForCategory(idx, catId) {
            const row = document.getElementById(`row_${idx}`);
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
            onItemChange(idx);
        }

        function onItemChange(idx) {
            const row = document.getElementById(`row_${idx}`);
            const itemSelect = row.querySelector('.item-select');
            const unitSelect = row.querySelector('.unit-select');

            const itemId = itemSelect.value;
            const item = inventoryItems.find(i => i.id == itemId);

            unitSelect.innerHTML = '';
            if (item) {
                if (item.wholesale_unit) {
                    unitSelect.innerHTML += `<option value="${item.wholesale_unit_id}" data-price="${item.wholesale_price}">كرتونة / بالجملة (${item.wholesale_unit.name})</option>`;
                }
                if (item.base_unit) {
                    unitSelect.innerHTML += `<option value="${item.base_unit_id}" data-price="${item.retail_price}">قطعة / بالفرادي (${item.base_unit.name})</option>`;
                }
                onUnitChange(idx);
            } else {
                unitSelect.innerHTML = '<option value="">-- حدد الوحدة --</option>';
            }
        }

        function onUnitChange(idx) {
            const row = document.getElementById(`row_${idx}`);
            const unitSelect = row.querySelector('.unit-select');
            const priceInput = row.querySelector('.price-input');
            const selectedOpt = unitSelect.options[unitSelect.selectedIndex];

            if (selectedOpt && selectedOpt.getAttribute('data-price')) {
                priceInput.value = parseFloat(selectedOpt.getAttribute('data-price')).toFixed(2);
            }
            calculateRowTotal(idx);
        }

        function calculateRowTotal(idx) {
            const row = document.getElementById(`row_${idx}`);
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = qty * price;

            row.querySelector('.row-subtotal').innerText = subtotal.toFixed(2);
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
        });
    </script>
</x-app-layout>
