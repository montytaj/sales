<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('payments.cashboxes_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-safe2-fill text-primary me-2"></i>{{ __('payments.cashboxes_list') }}
            </h2>
            @can('create-cashboxes')
                <a href="{{ route('cashboxes.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('payments.create_cashbox') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Cashboxes Cards Grid -->
    <div class="row g-4 mb-4">
        @forelse ($cashboxes as $cashbox)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-secondary-subtle text-secondary mb-1"><code>{{ $cashbox->code }}</code></span>
                                <h5 class="font-bold text-dark mb-0">{{ $cashbox->name_ar }}</h5>
                                <small class="text-muted">{{ $cashbox->branch?->name ?? 'كافة الفروع' }}</small>
                            </div>
                            @if ($cashbox->activeShift())
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-clock-history me-1"></i>وردية مفتوحة</span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1">مغلقة</span>
                            @endif
                        </div>

                        <div class="p-3 bg-light rounded mb-3">
                            <span class="text-muted d-block small mb-1">{{ __('payments.current_balance') }}</span>
                            <h3 class="mb-0 font-bold text-success">{{ number_format($cashbox->current_balance, 2) }} {{ setting('currency', 'SDG') }}</h3>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('cashboxes.show', $cashbox) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye me-1"></i>تفاصيل الخزنة والورديات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-safe fs-1 d-block mb-2"></i>
                لا توجد خزن أو صناديق مالية مسجلة.
            </div>
        @endforelse
    </div>

    @if ($cashboxes->hasPages())
        <div class="py-3">
            {{ $cashboxes->links() }}
        </div>
    @endif
</x-app-layout>
