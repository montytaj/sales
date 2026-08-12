<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-box-seam-fill text-primary me-2"></i>{{ __('inventory.title') }} - جرد المخازن
                </h2>
                <p class="text-muted fs-7 mb-0">متابعة أرصدة الأصناف بالوحدات الكبرى والصغرى حسب المخزن</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inventory.item-card') }}" class="btn btn-outline-info font-bold rounded-3 px-3 py-2 fs-7 shadow-2xs">
                    <i class="bi bi-card-checklist me-1.5"></i>جرد الأصناف
                </a>
                <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary font-bold rounded-3 px-3 py-2 fs-7 shadow-2xs">
                    <i class="bi bi-house-gear me-1.5"></i>إدارة وإضافة المخازن
                </a>
                @if($warehouses->isNotEmpty())
                    <a href="{{ route('warehouses.opening-balances', $selectedWarehouseId ?: $warehouses->first()->id) }}" class="btn btn-outline-primary font-bold rounded-3 px-3 py-2 fs-7 shadow-2xs">
                        <i class="bi bi-box-arrow-in-down-left me-1.5"></i>أرصدة أول المدة للمخازن
                    </a>
                @endif
                <a href="{{ route('inventory.create') }}" class="btn btn-primary font-bold rounded-3 px-3 py-2 fs-7 shadow-sm">
                    <i class="bi bi-plus-circle me-1.5"></i>{{ __('inventory.add_new_item') }}
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Warehouse Inventory Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('inventory.index') }}" class="row g-3 align-items-center">
                <div class="col-md-5 col-lg-4">
                    <label class="form-label font-bold text-slate-700 fs-7 mb-1">
                        <i class="bi bi-building me-1 text-primary"></i>تصفية وجرد حسب المخزن:
                    </label>
                    <select name="warehouse_id" class="form-select form-select-md font-bold shadow-sm" onchange="this.form.submit()">
                        <option value="">-- جميع المخازن --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>
                                🏢 {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedWarehouseId)
                    <div class="col-md-7 col-lg-8 d-flex align-items-end gap-2 mt-md-4">
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-7 rounded-3">
                            <i class="bi bi-funnel-fill me-1"></i>نتائج الجرد لمخزن: <strong>{{ $selectedWarehouse?->name }}</strong>
                        </div>
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                            <i class="bi bi-x-circle me-1"></i>إلغاء التصفية (عرض الكل)
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="max-width: 100%;">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive w-100 overflow-x-auto" style="max-width: 100%; -webkit-overflow-scrolling: touch;">
                <table class="table table-hover align-middle datatable w-100 mb-0">
                    <thead class="table-light fs-7">
                        <tr>
                            <th scope="col" style="width: 40px;">#</th>
                            <th scope="col" style="min-width: 260px; width: 25%;">{{ __('inventory.item_name') }}</th>
                            <th scope="col" style="min-width: 110px;">{{ __('inventory.category') }}</th>
                            <th scope="col" style="min-width: 140px;">المخزن</th>
                            <th scope="col" class="bg-primary-subtle text-primary text-center" style="min-width: 110px;">الوحدة الكبرى</th>
                            <th scope="col" class="bg-success-subtle text-success text-center" style="min-width: 110px;">الوحدة الصغرى</th>
                            <th scope="col" style="min-width: 110px;">{{ __('inventory.cost_price') }}</th>
                            <th scope="col" style="min-width: 120px;">{{ __('inventory.prices') }}</th>
                            <th scope="col" style="min-width: 90px;">{{ __('general.status') }}</th>
                            <th scope="col" class="text-end pe-3" style="min-width: 120px;">{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold text-slate-900" style="min-width: 260px;">
                                    {{ $item->name }}
                                    @if((float)$item->conversion_factor > 1 && $item->wholesaleUnit && $item->baseUnit)
                                        <div class="fs-8 text-muted font-mono mt-0.5 fw-normal">
                                            (1 {{ $item->wholesaleUnit->name }} = {{ $item->conversion_factor }} {{ $item->baseUnit->name }})
                                        </div>
                                    @endif
                                </td>
                                <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ $item->category?->name ?? __('inventory.uncategorized') }}</span></td>
                                <td>
                                    @if($selectedWarehouseId)
                                        <span class="badge bg-secondary-subtle text-dark border px-2 py-1">
                                            <i class="bi bi-building me-1"></i>{{ $selectedWarehouse?->name }}
                                        </span>
                                    @else
                                        @if($item->warehouseItems->count() > 0)
                                            <div class="d-flex flex-column gap-1 fs-8">
                                                @foreach($item->warehouseItems as $whItem)
                                                    @if((float)$whItem->qty_in_base_units > 0 && $whItem->warehouse)
                                                        <span class="badge bg-light text-dark border font-mono">
                                                            {{ $whItem->warehouse->name }}: {{ (float)$whItem->qty_in_base_units }} {{ $item->baseUnit?->name ?? 'قطعة' }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted fs-8">جميع المخازن (0)</span>
                                        @endif
                                    @endif
                                </td>
                                <!-- Major Unit Column -->
                                <td class="fw-bold bg-primary-subtle text-center align-middle">
                                    @if($item->wholesaleUnit && (float)$item->conversion_factor > 1)
                                        <span class="badge bg-primary text-white font-mono px-3 py-1.5 fs-7 shadow-2xs">
                                            {{ $item->getWholesaleQtyFormatted($selectedWarehouseId) }}
                                        </span>
                                    @else
                                        <span class="badge bg-slate-100 text-slate-600 border font-normal px-2.5 py-1 fs-8">
                                            وحدة صغرى فقط
                                        </span>
                                    @endif
                                </td>
                                <!-- Minor Unit Column -->
                                @php
                                    $baseQty = $item->getStockInBaseUnits($selectedWarehouseId);
                                    $baseUnitName = $item->baseUnit?->name ?? $item->unit ?? 'قطعة';
                                @endphp
                                <td class="fw-bold bg-success-subtle text-center align-middle">
                                    @if($baseQty > 0)
                                        <span class="badge bg-success text-white font-mono px-3 py-1.5 fs-7 shadow-2xs">
                                            {{ number_format($baseQty, $baseQty == (int)$baseQty ? 0 : 2) }} {{ $baseUnitName }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fs-8 font-medium">
                                            0 {{ $baseUnitName }} (خالي)
                                        </span>
                                    @endif
                                </td>
                                <!-- Cost Price Column -->
                                <td class="font-mono font-bold text-slate-800 text-nowrap align-middle">
                                    {{ number_format($item->cost_price, 2) }} <small class="text-muted fs-8">{{ setting('currency', 'SDG') }}</small>
                                </td>
                                <!-- Sale Prices Column -->
                                <td class="font-mono text-nowrap align-middle">
                                    <div class="font-bold text-primary fs-7">
                                        {{ number_format($item->retail_price ?? $item->default_sale_price ?? 0, 2) }} <small class="fs-8">{{ setting('currency', 'SDG') }}</small>
                                    </div>
                                    @if((float)$item->wholesale_price > 0)
                                        <div class="fs-8 text-muted mt-0.5">
                                            جملة: {{ number_format($item->wholesale_price, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($item->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">{{ __('inventory.available') }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">{{ __('inventory.disabled') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 align-middle text-nowrap">
                                    <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('inventory.item-card', ['item_id' => $item->id]) }}" class="btn btn-action-icon bg-info-subtle text-info border border-info-subtle" title="جرد الصنف وكارت الحركة">
                                            <i class="bi bi-card-checklist"></i>
                                        </a>
                                        <a href="{{ route('inventory.edit', $item) }}" class="btn btn-action-icon btn-action-edit" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('general.confirm_delete') ?? 'هل أنت تأكد من الحذف؟' }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-action-icon btn-action-delete" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>



