<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('cheques.index'), 'label' => __('payments.cheques_list')],
                ['label' => $cheque->cheque_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-bank2 text-primary me-2"></i>تفاصيل الشيك رقم: {{ $cheque->cheque_number }}
            </h2>

            @can('manage-cheques')
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        تحديث حالة الشيك
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach (__('payments.cheque_statuses') as $statusKey => $statusName)
                            <li>
                                <form method="POST" action="{{ route('cheques.update-status', $cheque) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $statusKey }}">
                                    <button type="submit" class="dropdown-item">{{ $statusName }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endcan
        </div>
    </x-slot>

    <!-- Details Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <span class="text-muted d-block mb-1">{{ __('payments.cheque_number') }}</span>
                    <h4 class="font-bold text-primary mb-0"><code>{{ $cheque->cheque_number }}</code></h4>
                </div>

                <div class="col-12 col-md-4">
                    <span class="text-muted d-block mb-1">{{ __('payments.bank_name') }}</span>
                    <h5 class="font-bold text-dark mb-0">{{ $cheque->bank_name }}</h5>
                </div>

                <div class="col-12 col-md-4">
                    <span class="text-muted d-block mb-1">{{ __('payments.drawer_name') }}</span>
                    <h5 class="font-bold text-dark mb-0">{{ $cheque->drawer_name }}</h5>
                </div>

                <div class="col-12 col-md-4">
                    <span class="text-muted d-block mb-1">{{ __('payments.issue_date') }}</span>
                    <strong class="text-dark fs-6">{{ $cheque->issue_date->format('Y-m-d') }}</strong>
                </div>

                <div class="col-12 col-md-4">
                    <span class="text-muted d-block mb-1">{{ __('payments.due_date') }}</span>
                    <strong class="text-dark fs-6">{{ $cheque->due_date->format('Y-m-d') }}</strong>
                </div>

                <div class="col-12 col-md-4">
                    <span class="text-muted d-block mb-1">الحالة الحالية</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary fs-6">
                        {{ __('payments.cheque_statuses.' . $cheque->status) }}
                    </span>
                </div>

                <div class="col-12">
                    <span class="text-muted d-block mb-1">المبلغ الإجمالي للشيك</span>
                    <h3 class="font-bold text-success mb-0">{{ number_format($cheque->amount, 2) }} {{ setting('currency', 'SDG') }}</h3>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
