<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('payments.index'), 'label' => __('payments.vouchers_list')],
                ['label' => $voucher->voucher_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <h2 class="h4 mb-0 font-bold text-dark">
                    <i class="bi bi-receipt-cutoff text-primary me-2"></i>{{ __('payments.types.' . $voucher->type) }}: {{ $voucher->voucher_number }}
                </h2>
                @if ($voucher->status === 'completed')
                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6"><i class="bi bi-check-circle me-1"></i>مكتمل</span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6"><i class="bi bi-x-circle me-1"></i>ملغي</span>
                @endif
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('payments.print', $voucher) }}" target="_blank" class="btn btn-outline-dark">
                    <i class="bi bi-printer me-1"></i>{{ __('payments.print_receipt') }}
                </a>

                @can('cancel-payment-vouchers')
                    @if ($voucher->status === 'completed')
                        <form method="POST" action="{{ route('payments.cancel', $voucher) }}" class="d-inline" onsubmit="return confirm('هل أنت تأكد من إلغاء هذا السند وإعادة رصيد الخزنة/الفاتورة؟');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash me-1"></i>إلغاء السند
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- Details Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <span class="text-muted d-block mb-1">الجهة / العميل / المورد</span>
                    <h5 class="font-bold text-dark mb-0">{{ $voucher->customer?->name ?? ($voucher->supplier?->name ?? 'تحويل بين الخزن') }}</h5>
                </div>

                <div class="col-12 col-md-3">
                    <span class="text-muted d-block mb-1">{{ __('payments.payment_date') }}</span>
                    <strong class="text-dark fs-6">{{ $voucher->payment_date->format('Y-m-d') }}</strong>
                </div>

                <div class="col-12 col-md-3">
                    <span class="text-muted d-block mb-1">الخزنة المسجلة</span>
                    <strong class="text-dark fs-6">{{ $voucher->cashbox?->name_ar ?? '-' }}</strong>
                </div>

                <div class="col-12 col-md-2">
                    <span class="text-muted d-block mb-1">المبلغ الإجمالي</span>
                    <h4 class="font-bold text-success mb-0">{{ number_format($voucher->amount, 2) }} {{ setting('currency', 'SDG') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Split Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 font-bold text-dark"><i class="bi bi-wallet2 me-2"></i>تفاصيل وسائل الدفع المسددة</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">طريقة الدفع</th>
                            <th scope="col">المبلغ المسدد</th>
                            <th scope="col">رقم المرجع / الحوالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($voucher->lines as $index => $line)
                            <tr>
                                <th scope="row" class="ps-3">{{ $index + 1 }}</th>
                                <td>
                                    <span class="badge bg-light text-dark border fs-6">
                                        {{ __('payments.methods.' . $line->payment_method) }}
                                    </span>
                                </td>
                                <td><strong class="text-dark">{{ number_format($line->amount, 2) }} {{ setting('currency', 'SDG') }}</strong></td>
                                <td><code>{{ $line->reference_number ?? '-' }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cheque details if attached -->
    @if ($voucher->cheques->count() > 0)
        <div class="card shadow-sm border-0 mb-4 border-start border-4 border-info">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-bold text-dark"><i class="bi bi-bank me-2"></i>بيانات الشيك المرتبط</h5>
            </div>
            <div class="card-body p-4">
                @foreach ($voucher->cheques as $cheque)
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small mb-1">{{ __('payments.cheque_number') }}</span>
                            <strong class="text-primary font-bold"><code>{{ $cheque->cheque_number }}</code></strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small mb-1">{{ __('payments.bank_name') }}</span>
                            <strong class="text-dark">{{ $cheque->bank_name }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small mb-1">{{ __('payments.drawer_name') }}</span>
                            <strong class="text-dark">{{ $cheque->drawer_name }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small mb-1">حالة الشيك</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary">
                                {{ __('payments.cheque_statuses.' . $cheque->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-app-layout>
