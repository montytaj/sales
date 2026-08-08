<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('customer-orders.index'), 'label' => __('sales.orders_list')],
                ['label' => $customerOrder->order_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-inboxes text-primary me-2"></i>طلب عميل: {{ $customerOrder->order_number }}
            </h2>
            @can('create-quotations')
                <a href="{{ route('quotations.create', ['order_id' => $customerOrder->id]) }}" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-plus"></i>
                    <span>إنشاء عرض سعر لهذا الطلب</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="row g-4">
        <div class="col-12 col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-person me-2"></i>بيانات العميل</h5>
                </div>
                <div class="card-body p-4">
                    <h5 class="font-bold text-dark mb-1">{{ $customerOrder->customer->name }}</h5>
                    <p class="text-muted mb-2"><code>{{ $customerOrder->customer->code }}</code></p>
                    <p class="mb-2"><strong>الهاتف:</strong> {{ $customerOrder->customer->phone }}</p>
                    <p class="mb-0"><strong>المدينة:</strong> {{ $customerOrder->customer->city }}</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-card-text me-2"></i>المواصفات المطلوبة</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-3 text-dark fs-6">{{ $customerOrder->requirements_summary }}</p>
                    @if ($customerOrder->notes)
                        <div class="alert alert-light border">
                            <strong>ملاحظات:</strong> {{ $customerOrder->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
