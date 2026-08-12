<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-plus-circle text-primary me-2"></i>{{ __('transfers.new_transfer') }}
            </h2>
            <a href="{{ route('warehouse-transfers.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-right me-1"></i>{{ __('transfers.transfers_list') }}
            </a>
        </div>
    </x-slot>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('warehouse-transfers.store') }}" method="POST" id="transferForm">
                @csrf

                <!-- Basic Data Header -->
                <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                    <div class="col-md-4">
                        <label class="form-label font-semibold text-gray-700 required">{{ __('transfers.from_warehouse') }}</label>
                        <select name="from_warehouse_id" id="from_warehouse_id" class="form-select border-primary shadow-sm @error('from_warehouse_id') is-invalid @enderror" required>
                            <option value="">{{ __('transfers.select_source_warehouse') }}</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} ({{ $wh->code }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted fs-8 d-block mt-1">حدد المخزن المراد الخصم منه لعرض الأرصدة المتاحة فيه</small>
                        @error('from_warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label font-semibold text-gray-700 required">{{ __('transfers.to_warehouse') }}</label>
                        <select name="to_warehouse_id" id="to_warehouse_id" class="form-select @error('to_warehouse_id') is-invalid @enderror" required>
                            <option value="">{{ __('transfers.select_target_warehouse') }}</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} ({{ $wh->code }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger d-none font-semibold mt-1 d-block" id="sameWarehouseWarning">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>عفواً، لا يمكن التحويل من المخزن إلى نفسه! يرجى اختيار مخزن آخر كهدف.
                        </small>
                        @error('to_warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label font-semibold text-gray-700 required">{{ __('transfers.transfer_date') }}</label>
                        <input type="date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" class="form-control @error('transfer_date') is-invalid @enderror" required>
                        @error('transfer_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label font-semibold text-gray-700">{{ __('transfers.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="أدخل سبب أو تفاصيل عملية التحويل المخزني...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Alert when no warehouse is selected -->
                <div id="noWarehouseSelectedAlert" class="alert alert-info border border-info-subtle rounded-3 py-4 text-center mb-4">
                    <i class="bi bi-building-down fs-2 text-info d-block mb-2"></i>
                    <h6 class="font-bold text-dark mb-1">يرجى تحديد "المخزن المصدر (من)" أولاً</h6>
                    <p class="text-muted mb-0 fs-7">تظهر الأصناف المخزنية وأرصدتها المتاحة فور اختيار المخزن المراد التحويل منه.</p>
                </div>

                <!-- Items Section Wrapper (Hidden until warehouse is selected) -->
                <div id="itemsSectionWrapper" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-bold text-dark mb-0">
                            <i class="bi bi-boxes text-primary me-1"></i>الأصناف المراد تحويلها
                            <span id="selectedWarehouseBadge" class="badge bg-primary-subtle text-primary ms-2 fs-7"></span>
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="addItemRowBtn" disabled>
                            <i class="bi bi-plus-lg me-1"></i>{{ __('transfers.add_item') }}
                        </button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle" id="transferItemsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 42%;">{{ __('transfers.item_name') }}</th>
                                    <th style="width: 13%;">{{ __('transfers.unit') }}</th>
                                    <th style="width: 15%;">{{ __('transfers.available_stock') }} بالمخزن</th>
                                    <th style="width: 20%;">{{ __('transfers.quantity') }} المحولة</th>
                                    <th style="width: 10%;" class="text-center">#</th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                <!-- Dynamic Item Rows -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Auto Approve & Submit Buttons -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pt-3 border-top">
                    <div>
                        @can('approve-warehouse-transfers')
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="auto_approve" id="auto_approve" value="1" checked>
                                <label class="form-check-label font-semibold text-emerald-700" for="auto_approve">
                                    <i class="bi bi-lightning-charge-fill me-1"></i>{{ __('transfers.auto_approve') }}
                                </label>
                            </div>
                        @endcan
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('warehouse-transfers.index') }}" class="btn btn-light border px-4">{{ __('general.cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4 font-bold shadow-sm" id="submitTransferBtn" disabled>
                            <i class="bi bi-check-circle me-1"></i>{{ __('transfers.create_transfer') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Items JS Script -->
    <script>
        const inventoryItems = @json($items);
        let rowCounter = 0;

        function getItemStockInWarehouse(itemId, warehouseId) {
            if (!itemId || !warehouseId) return 0;
            const item = inventoryItems.find(i => i.id == itemId);
            if (!item || !item.warehouse_items) return 0;
            const whItem = item.warehouse_items.find(w => w.warehouse_id == warehouseId);
            return whItem ? parseFloat(whItem.qty_in_base_units || 0) : 0;
        }

        function syncWarehouseDropdowns() {
            const fromSelect = document.getElementById('from_warehouse_id');
            const toSelect = document.getElementById('to_warehouse_id');
            const fromId = fromSelect.value;
            const toId = toSelect.value;
            const sameWarning = document.getElementById('sameWarehouseWarning');

            // Disable option in toSelect matching fromId
            Array.from(toSelect.options).forEach(opt => {
                if (opt.value && opt.value === fromId) {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });

            // Disable option in fromSelect matching toId
            Array.from(fromSelect.options).forEach(opt => {
                if (opt.value && opt.value === toId) {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });

            if (fromId && toId && fromId === toId) {
                toSelect.value = '';
                if (sameWarning) sameWarning.classList.remove('d-none');
            } else {
                if (sameWarning) sameWarning.classList.add('d-none');
            }
        }

        function buildItemOptions(warehouseId, selectedItemId = null) {
            let optionsHtml = `<option value="">اختر الصنف من المخزن المصدر...</option>`;
            
            inventoryItems.forEach(item => {
                const stock = getItemStockInWarehouse(item.id, warehouseId);
                const isSelected = selectedItemId && selectedItemId == item.id ? 'selected' : '';
                const stockText = stock > 0 
                    ? `[الرصيد المتاح: ${stock} ${item.unit}]` 
                    : `[الرصيد: 0 ${item.unit} - غير متوفر]`;

                optionsHtml += `<option value="${item.id}" data-unit="${item.unit}" data-stock="${stock}" ${isSelected}>
                    ${item.name} (${item.item_code}) - ${stockText}
                </option>`;
            });

            return optionsHtml;
        }

        function createItemRow() {
            const warehouseId = document.getElementById('from_warehouse_id').value;
            if (!warehouseId) return;

            rowCounter++;
            const optionsHtml = buildItemOptions(warehouseId);

            const rowHtml = `
                <tr id="row_${rowCounter}">
                    <td>
                        <select name="items[${rowCounter}][inventory_item_id]" class="form-select item-select" required onchange="handleItemChange(${rowCounter})">
                            ${optionsHtml}
                        </select>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border px-2.5 py-1 item-unit" id="unit_${rowCounter}">-</span>
                    </td>
                    <td>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 font-mono fs-7 item-stock" id="stock_${rowCounter}">0</span>
                    </td>
                    <td>
                        <input type="number" name="items[${rowCounter}][quantity]" class="form-control qty-input" step="0.01" min="0.01" value="1" required id="qty_${rowCounter}" oninput="validateQty(${rowCounter})">
                        <small class="text-danger d-none qty-warning font-semibold mt-1 d-block" id="warning_${rowCounter}">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>تنبيه: الكمية أكثر من الرصيد المتاح بالمخزن!
                        </small>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${rowCounter})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', rowHtml);
        }

        function updateRowOptions(rowId, warehouseId) {
            const select = document.querySelector(`#row_${rowId} .item-select`);
            if (!select) return;
            const currentSelectedValue = select.value;
            select.innerHTML = buildItemOptions(warehouseId, currentSelectedValue);
            handleItemChange(rowId);
        }

        function handleItemChange(rowId) {
            const select = document.querySelector(`#row_${rowId} .item-select`);
            if (!select) return;

            const selectedOption = select.options[select.selectedIndex];
            const warehouseId = document.getElementById('from_warehouse_id').value;
            
            if (selectedOption && selectedOption.value) {
                const itemId = selectedOption.value;
                const stock = getItemStockInWarehouse(itemId, warehouseId);
                const unit = selectedOption.getAttribute('data-unit') || 'pcs';

                document.getElementById(`unit_${rowId}`).textContent = unit;
                const stockBadge = document.getElementById(`stock_${rowId}`);
                stockBadge.textContent = stock;

                if (stock > 0) {
                    stockBadge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 font-mono fs-7 item-stock';
                } else {
                    stockBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 font-mono fs-7 item-stock';
                }

                validateQty(rowId);
            } else {
                document.getElementById(`unit_${rowId}`).textContent = '-';
                const stockBadge = document.getElementById(`stock_${rowId}`);
                stockBadge.textContent = '0';
                stockBadge.className = 'badge bg-secondary-subtle text-secondary border px-2.5 py-1 font-mono fs-7 item-stock';
                document.getElementById(`warning_${rowId}`).classList.add('d-none');
            }
        }

        function validateQty(rowId) {
            const stock = parseFloat(document.getElementById(`stock_${rowId}`).textContent || 0);
            const qty = parseFloat(document.getElementById(`qty_${rowId}`).value || 0);
            const warningEl = document.getElementById(`warning_${rowId}`);

            if (qty > stock) {
                warningEl.classList.remove('d-none');
            } else {
                warningEl.classList.add('d-none');
            }
        }

        function removeRow(rowId) {
            const rows = document.querySelectorAll('#itemsContainer tr');
            if (rows.length > 1) {
                document.getElementById(`row_${rowId}`).remove();
            } else {
                alert('يجب الإبقاء على صنف واحد على الأقل.');
            }
        }

        function updateWarehouseUI() {
            syncWarehouseDropdowns();

            const fromWhSelect = document.getElementById('from_warehouse_id');
            const warehouseId = fromWhSelect.value;
            const selectedText = fromWhSelect.options[fromWhSelect.selectedIndex]?.text || '';
            
            const wrapper = document.getElementById('itemsSectionWrapper');
            const alertBox = document.getElementById('noWarehouseSelectedAlert');
            const addBtn = document.getElementById('addItemRowBtn');
            const submitBtn = document.getElementById('submitTransferBtn');
            const badge = document.getElementById('selectedWarehouseBadge');

            if (!warehouseId) {
                wrapper.classList.add('d-none');
                alertBox.classList.remove('d-none');
                addBtn.disabled = true;
                submitBtn.disabled = true;
                document.getElementById('itemsContainer').innerHTML = '';
                rowCounter = 0;
            } else {
                wrapper.classList.remove('d-none');
                alertBox.classList.add('d-none');
                addBtn.disabled = false;
                submitBtn.disabled = false;
                badge.textContent = `المخزن المحدد: ${selectedText}`;

                const rows = document.querySelectorAll('#itemsContainer tr');
                if (rows.length === 0) {
                    createItemRow();
                } else {
                    rows.forEach(r => {
                        const rowId = r.id.replace('row_', '');
                        updateRowOptions(rowId, warehouseId);
                    });
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateWarehouseUI();

            document.getElementById('from_warehouse_id').addEventListener('change', updateWarehouseUI);
            document.getElementById('to_warehouse_id').addEventListener('change', syncWarehouseDropdowns);
            document.getElementById('addItemRowBtn').addEventListener('click', createItemRow);
        });
    </script>
</x-app-layout>
