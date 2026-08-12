<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('inventory.warehouses') ?? 'المخازن', 'url' => route('warehouses.index')],
                ['label' => $warehouse->name, 'url' => route('warehouses.show', $warehouse)],
                ['label' => 'إدخال/تعديل بضاعة أول المدة']
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-box-arrow-in-down-left text-primary me-2"></i>إدخال وتسوية بضاعة أول المدة - {{ $warehouse->name }}
                </h2>
                <p class="text-muted fs-7 mb-0">تحديد كميات المخزون الافتتاحي بالوحدة الكبرى والصغرى وأسعار التكلفة للمخزن</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-outline-secondary font-semibold rounded-3 px-3 py-2 fs-7">
                    <i class="bi bi-arrow-right me-1"></i>الرجوع للمخزن
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Warehouse Selector Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-md-6 col-lg-5">
                    <label class="form-label font-bold text-slate-700 fs-7 mb-1">
                        <i class="bi bi-building me-1 text-primary"></i>تغيير المخزن المستهدف:
                    </label>
                    <select class="form-select font-bold shadow-sm" onchange="window.location.href = this.value">
                        @foreach($warehouses as $wh)
                            <option value="{{ route('warehouses.opening-balances', $wh->id) }}" {{ $warehouse->id == $wh->id ? 'selected' : '' }}>
                                🏢 {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-7 text-md-end">
                    <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-7 rounded-3">
                        <i class="bi bi-info-circle me-1"></i>يتم تسجيل الفروقات آلياً في سجل حركات المخزون بنوع <strong>(رصيد افتتاحي)</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Opening Balances Form -->
    <form action="{{ route('warehouses.store-opening-balances', $warehouse->id) }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom border-slate-100 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 font-bold text-slate-900 fs-6">
                    <i class="bi bi-list-check text-primary me-2"></i>قائمة أصناف المنشأة ({{ $items->count() }} صنف)
                </h5>
                <button type="submit" class="btn btn-primary font-bold rounded-3 px-4 py-2 fs-7 shadow-sm">
                    <i class="bi bi-check-circle me-1.5"></i>حفظ الأرصدة الافتتاحية للمخزن
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light fs-7">
                            <tr>
                                <th scope="col" style="width: 45px;" class="ps-3">#</th>
                                <th scope="col" style="min-width: 220px;">اسم الصنف والتصنيف</th>
                                <th scope="col" style="min-width: 140px;">الوحدة الصغرى / الكبرى</th>
                                <th scope="col" style="min-width: 130px;" class="bg-slate-100 text-center">الرصيد الحالي بالمخزن</th>
                                <th scope="col" style="min-width: 140px;" class="bg-primary-subtle text-primary">الكمية الافتتاحية (وحدة كبرى)</th>
                                <th scope="col" style="min-width: 140px;" class="bg-success-subtle text-success">الكمية الافتتاحية (وحدة صغرى)</th>
                                <th scope="col" style="min-width: 130px;">سعر التكلفة (للوحدة)</th>
                                <th scope="col" style="min-width: 160px;" class="pe-3">ملاحظات والتسوية</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($items as $index => $item)
                                @php
                                    $currentBaseQty = (float)($existingStock[$item->id] ?? 0);
                                    $factor = (float)($item->conversion_factor ?: 1);
                                    
                                    $currentWholesaleQty = $factor > 1 ? floor($currentBaseQty / $factor) : 0;
                                    $currentRemainderBaseQty = $factor > 1 ? fmod($currentBaseQty, $factor) : $currentBaseQty;
                                @endphp
                                <tr>
                                    <td class="ps-3 font-mono text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="font-bold text-slate-900">{{ $item->name }}</div>
                                        <small class="text-muted fs-8">{{ $item->category?->name ?? 'غير مصنف' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-slate-100 text-slate-700 border font-mono fs-8">
                                            {{ $item->baseUnit?->name ?? 'قطعة' }}
                                        </span>
                                        @if($item->wholesaleUnit && $factor > 1)
                                            <div class="fs-8 text-muted mt-0.5 font-mono">
                                                1 {{ $item->wholesaleUnit->name }} = {{ $factor }} {{ $item->baseUnit?->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <!-- Current Stock Badge -->
                                    <td class="text-center bg-slate-50">
                                        @if($currentBaseQty > 0)
                                            <span class="badge bg-slate-800 text-white font-mono px-2.5 py-1 fs-8">
                                                {{ $item->formatted_stock ?? ($currentBaseQty . ' ' . ($item->baseUnit?->name ?? 'قطعة')) }}
                                            </span>
                                        @else
                                            <span class="badge bg-slate-200 text-slate-600 font-mono fs-8">0</span>
                                        @endif
                                    </td>
                                    <!-- Wholesale Qty Input -->
                                    <td class="bg-primary-subtle p-2">
                                        @if($item->wholesaleUnit && $factor > 1)
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" min="0" 
                                                    name="balances[{{ $item->id }}][wholesale_qty]" 
                                                    class="form-control font-mono font-bold text-primary" 
                                                    value="{{ $currentWholesaleQty > 0 ? $currentWholesaleQty : 0 }}"
                                                    placeholder="0">
                                                <span class="input-group-text bg-white text-muted fs-8">{{ $item->wholesaleUnit->name }}</span>
                                            </div>
                                        @else
                                            <input type="hidden" name="balances[{{ $item->id }}][wholesale_qty]" value="0">
                                            <span class="text-muted fs-8 opacity-75">فرادي فقط</span>
                                        @endif
                                    </td>
                                    <!-- Base Qty Input -->
                                    <td class="bg-success-subtle p-2">
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.01" min="0" 
                                                name="balances[{{ $item->id }}][base_qty]" 
                                                class="form-control font-mono font-bold text-success" 
                                                value="{{ $currentRemainderBaseQty > 0 ? $currentRemainderBaseQty : 0 }}"
                                                placeholder="0">
                                            <span class="input-group-text bg-white text-muted fs-8">{{ $item->baseUnit?->name ?? 'قطعة' }}</span>
                                        </div>
                                    </td>
                                    <!-- Cost Price Input -->
                                    <td class="p-2">
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.01" min="0" 
                                                name="balances[{{ $item->id }}][cost_price]" 
                                                class="form-control font-mono" 
                                                value="{{ (float)$item->cost_price }}"
                                                placeholder="0.00">
                                            <span class="input-group-text bg-light text-muted fs-8">{{ setting('currency', 'SDG') }}</span>
                                        </div>
                                    </td>
                                    <!-- Notes Input -->
                                    <td class="pe-3 p-2">
                                        <input type="text" 
                                            name="balances[{{ $item->id }}][notes]" 
                                            class="form-control form-control-sm fs-8" 
                                            placeholder="ملاحظات رصيد أول المدة">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">لا توجد أصناف معرفة حالياً بالنظام.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white p-3 text-end border-top border-slate-100">
                <button type="submit" class="btn btn-primary btn-lg font-bold rounded-3 px-5 py-2.5 fs-6 shadow-sm">
                    <i class="bi bi-check-circle me-1.5"></i>حفظ وتطبيق أرصدة أول المدة
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
