<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('sales.orders_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-inboxes-fill text-primary me-2"></i>{{ __('sales.orders_list') }}
            </h2>
            @can('create-customer-orders')
                <a href="{{ route('customer-orders.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('sales.create_order') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Search & Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customer-orders.index') }}" class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم الطلب، اسم العميل، أو المواصفات..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- كافة الحالات --</option>
                        @foreach (__('sales.order_status') as $statusKey => $statusName)
                            <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>{{ $statusName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('customer-orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('sales.order_number') }}</th>
                            <th scope="col">{{ __('customers.name') }}</th>
                            <th scope="col">{{ __('sales.requirements_summary') }}</th>
                            <th scope="col">الحالة</th>
                            <th scope="col">تاريخ الطلب</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="ps-3"><code>{{ $order->order_number }}</code></td>
                                <td class="fw-semibold">{{ $order->customer->name }}</td>
                                <td>{{ Str::limit($order->requirements_summary, 60) }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        {{ __('sales.order_status.' . $order->status) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('customer-orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>عرض التفاصيل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    لا توجد طلبات عملاء مسجلة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
