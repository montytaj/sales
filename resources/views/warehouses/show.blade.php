<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-box-seam text-primary me-2"></i>مخزون المخزن: {{ $warehouse->name }}
            </h2>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('warehouses.opening-balances', $warehouse) }}" class="btn btn-primary font-semibold rounded-3">
                    <i class="bi bi-box-arrow-in-down-left me-1"></i>أرصدة أول المدة
                </a>
                <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary font-semibold rounded-3">
                    <i class="bi bi-arrow-right me-1"></i>الرجوع للمخازن
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Warehouse Details Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted fs-7 font-bold">كود المخزن</span>
                <span class="fs-5 font-mono text-dark fw-bold">{{ $warehouse->code }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted fs-7 font-bold">أمين المخزن</span>
                <span class="fs-6 text-dark fw-bold">{{ $warehouse->keeper_name ?? 'غير محدد' }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted fs-7 font-bold">رقم الهاتف</span>
                <span class="fs-6 text-dark dir-ltr text-end fw-bold">{{ $warehouse->phone ?? '-' }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted fs-7 font-bold">عدد أنواع الأصناف المخزنة</span>
                <span class="fs-5 text-primary fw-bold">{{ $warehouse->warehouseItems->count() }} صنف</span>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 font-bold text-dark"><i class="bi bi-boxes text-primary me-2"></i>تفاصيل الأصناف والكميات بالمخزن (بالجملة والفرادي)</h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            <th>التصنيف</th>
                            <th>الكمية بالوحدة الصغرى</th>
                            <th>الكمية المعروضة (جملة + فرادي)</th>
                            <th>الوحدات (كبرى / صغرى)</th>
                            <th>سعر الجملة / الفرادي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouse->warehouseItems as $wItem)
                            @php $item = $wItem->item; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold text-dark">{{ $item?->name ?? '-' }}</td>
                                <td><span class="badge bg-info-subtle text-info">{{ $item?->category?->name ?? '-' }}</span></td>
                                <td class="fw-bold text-primary fs-6">{{ number_format($wItem->qty_in_base_units, 2) }} {{ $item?->baseUnit?->name ?? 'قطعة' }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success fs-7 p-2">
                                        {{ $wItem->formatted_stock }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        1 {{ $item?->wholesaleUnit?->name ?? 'كرتونة' }} = {{ $item?->conversion_factor }} {{ $item?->baseUnit?->name ?? 'قطعة' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fs-7">
                                        <span class="text-dark font-bold">جملة:</span> {{ number_format($item?->wholesale_price ?? 0, 2) }} <br>
                                        <span class="text-dark font-bold">فرادي:</span> {{ number_format($item?->retail_price ?? 0, 2) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">لا توجد أصناف في هذا المخزن حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
