<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('payments.vouchers_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-cash-stack text-primary me-2"></i>{{ __('payments.vouchers_list') }}
            </h2>
            @can('create-payment-vouchers')
                <a href="{{ route('payments.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('payments.create_voucher') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Search & Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم السند أو اسم العميل..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <select name="type" class="form-select">
                        <option value="">-- كافة أنواع السندات --</option>
                        @foreach (__('payments.types') as $typeKey => $typeName)
                            <option value="{{ $typeKey }}" {{ request('type') === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- كافة الحالات --</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>مكتمل</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'type', 'status']))
                        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Vouchers Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('payments.voucher_number') }}</th>
                            <th scope="col">{{ __('payments.voucher_type') }}</th>
                            <th scope="col">الطرف</th>
                            <th scope="col">الخزنة</th>
                            <th scope="col">{{ __('payments.payment_date') }}</th>
                            <th scope="col">{{ __('payments.amount') }}</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vouchers as $voucher)
                            <tr>
                                <td class="ps-3"><code>{{ $voucher->voucher_number }}</code></td>
                                <td>
                                    <span class="badge {{ $voucher->type === 'receipt' ? 'bg-success-subtle text-success border border-success' : ($voucher->type === 'payment' ? 'bg-danger-subtle text-danger border border-danger' : 'bg-info-subtle text-info border border-info') }}">
                                        {{ __('payments.types.' . $voucher->type) }}
                                    </span>
                                </td>
                                <td class="fw-semibold">
                                    {{ $voucher->customer?->name ?? ($voucher->supplier?->name ?? '-') }}
                                </td>
                                <td>{{ $voucher->cashbox?->name_ar ?? '-' }}</td>
                                <td>{{ $voucher->payment_date->format('Y-m-d') }}</td>
                                <td><strong class="text-dark">{{ number_format($voucher->amount, 2) }} {{ setting('currency', 'SAR') }}</strong></td>
                                <td>
                                    @if ($voucher->status === 'completed')
                                        <span class="badge bg-success-subtle text-success border border-success">مكتمل</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger">ملغي</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route('payments.show', $voucher) }}" class="btn btn-action-icon btn-action-show" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('payments.print', $voucher) }}" target="_blank" class="btn btn-action-icon btn-action-print" title="طباعة الإيصال">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-receipt-cutoff fs-3 d-block mb-2"></i>
                                    لا توجد سندات مالية مسجلة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($vouchers->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
