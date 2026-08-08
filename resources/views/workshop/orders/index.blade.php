<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('workshop.orders_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-tools text-primary me-2"></i>{{ __('workshop.orders_list') }}
            </h2>
            @can('create-work-orders')
                <a href="{{ route('work-orders.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('workshop.create_order') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Search & Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('work-orders.index') }}" class="row g-3">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم أمر العمل، نوع اللوح، أو اسم العميل..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- كافة الحالات --</option>
                        @foreach (__('workshop.statuses') as $statusKey => $statusName)
                            <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>{{ $statusName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">-- كافة الأولويات --</option>
                        @foreach (__('workshop.priorities') as $priorityKey => $priorityName)
                            <option value="{{ $priorityKey }}" {{ request('priority') === $priorityKey ? 'selected' : '' }}>{{ $priorityName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'status', 'priority']))
                        <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Work Orders Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('workshop.work_order_number') }}</th>
                            <th scope="col">{{ __('customers.name') }}</th>
                            <th scope="col">مواصفات CNC</th>
                            <th scope="col">{{ __('workshop.priority') }}</th>
                            <th scope="col">تصريح البدء</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workOrders as $order)
                            <tr>
                                <td class="ps-3"><code>{{ $order->work_order_number }}</code></td>
                                <td class="fw-semibold">{{ $order->customer->name }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border me-1">{{ $order->sheet_count }} ألواح</span>
                                    <span class="badge bg-secondary-subtle text-secondary me-1">{{ $order->sheet_type }}</span>
                                    <small class="text-muted">{{ $order->thickness }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $order->priority === 'urgent' ? 'bg-danger' : ($order->priority === 'high' ? 'bg-warning text-dark' : 'bg-info text-dark') }}">
                                        {{ __('workshop.priorities.' . $order->priority) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($order->authorization)
                                        <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-shield-check me-1"></i>مصرح بالبدء</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-shield-x me-1"></i>بدون تصريح</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary">
                                        {{ __('workshop.statuses.' . $order->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('work-orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>عرض التفاصيل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-tools fs-3 d-block mb-2"></i>
                                    لا توجد أوامر عمل مسجلة بالورشة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($workOrders->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $workOrders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
