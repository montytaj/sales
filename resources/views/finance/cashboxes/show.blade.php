<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('cashboxes.index'), 'label' => __('payments.cashboxes_list')],
                ['label' => $cashbox->name_ar]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-safe2 text-primary me-2"></i>تفاصيل الخزنة: {{ $cashbox->name_ar }}
            </h2>

            <div class="d-flex gap-2">
                @can('manage-cashbox-shifts')
                    @if ($activeShift)
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
                            <i class="bi bi-door-closed me-1"></i>{{ __('payments.close_shift') }}
                        </button>
                    @else
                        <form method="POST" action="{{ route('cashboxes.open-shift', $cashbox) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-door-open me-1"></i>{{ __('payments.open_shift') }}
                            </button>
                        </form>
                    @endif
                @endcan

                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transferModal">
                    <i class="bi bi-arrow-left-right me-1"></i>تحويل إلى خزنة أخرى
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Cashbox Balance Info -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 p-3 bg-primary text-white">
                <span class="opacity-75 d-block small mb-1">{{ __('payments.current_balance') }}</span>
                <h2 class="mb-0 font-bold">{{ number_format($cashbox->current_balance, 2) }} {{ setting('currency', 'SDG') }}</h2>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 p-3 bg-light">
                <span class="text-muted d-block small mb-1">{{ __('payments.opening_balance') }}</span>
                <h3 class="mb-0 font-bold text-dark">{{ number_format($cashbox->opening_balance, 2) }} {{ setting('currency', 'SDG') }}</h3>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 p-3 bg-light">
                <span class="text-muted d-block small mb-1">{{ __('payments.shift_status') }}</span>
                @if ($activeShift)
                    <span class="badge bg-success-subtle text-success border border-success fs-6"><i class="bi bi-clock-history me-1"></i>وردية مفتوحة (منذ {{ $activeShift->opened_at->format('H:i Y-m-d') }})</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border fs-6">مغلقة</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Shifts History Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 font-bold text-dark"><i class="bi bi-clock-history me-2"></i>سجل الورديات والجرد</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">الموظف</th>
                            <th scope="col">{{ __('payments.opened_at') }}</th>
                            <th scope="col">{{ __('payments.closed_at') }}</th>
                            <th scope="col">{{ __('payments.expected_closing_balance') }}</th>
                            <th scope="col">{{ __('payments.actual_closing_balance') }}</th>
                            <th scope="col">{{ __('payments.difference_amount') }}</th>
                            <th scope="col">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cashbox->shifts as $index => $shift)
                            <tr>
                                <th scope="row" class="ps-3">{{ $index + 1 }}</th>
                                <td class="fw-semibold">{{ $shift->user->name }}</td>
                                <td>{{ $shift->opened_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $shift->closed_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ number_format($shift->expected_closing_balance, 2) }} {{ setting('currency', 'SDG') }}</td>
                                <td>{{ $shift->actual_closing_balance !== null ? number_format($shift->actual_closing_balance, 2) . ' {{ setting('currency', 'SDG') }}' : '-' }}</td>
                                <td>
                                    @if ($shift->difference_amount == 0)
                                        <span class="text-success fw-bold">0.00</span>
                                    @elseif ($shift->difference_amount > 0)
                                        <span class="text-info fw-bold">+{{ number_format($shift->difference_amount, 2) }}</span>
                                    @else
                                        <span class="text-danger fw-bold">{{ number_format($shift->difference_amount, 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $shift->status === 'open' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ __('payments.shift_' . $shift->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">لا توجد ورديات سابقة لهذه الخزنة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Close Shift Modal -->
    @if ($activeShift)
        <div class="modal fade" id="closeShiftModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('cashboxes.close-shift', $cashbox) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title font-bold">{{ __('payments.close_shift') }} والجرد</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>الرصيد المتوقع في النظام: <strong>{{ number_format($cashbox->current_balance, 2) }} {{ setting('currency', 'SDG') }}</strong></p>
                            <div class="mb-3">
                                <label for="actual_closing_balance" class="form-label font-semibold">{{ __('payments.actual_closing_balance') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="actual_closing_balance" id="actual_closing_balance" class="form-control" value="{{ $cashbox->current_balance }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label font-semibold">ملاحظات الجرد</label>
                                <textarea name="notes" id="notes" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-warning">إغلاق الوردية وحفظ للجرد</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Transfer Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('cashboxes.transfer', $cashbox) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-bold">تحويل نقدية بين الخزن</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>الخزنة المصدر: <strong>{{ $cashbox->name_ar }}</strong> (الرصيد الحالي: {{ number_format($cashbox->current_balance, 2) }} {{ setting('currency', 'SDG') }})</p>
                        
                        <div class="mb-3">
                            <label for="target_cashbox_id" class="form-label font-semibold">الخزنة المستلمة <span class="text-danger">*</span></label>
                            <select name="target_cashbox_id" id="target_cashbox_id" class="form-select" required>
                                <option value="">-- اختر الخزنة المستهدفة --</option>
                                @foreach ($allCashboxes as $tb)
                                    <option value="{{ $tb->id }}">{{ $tb->name_ar }} ({{ $tb->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label font-semibold">المبلغ المراد تحويله ({{ setting('currency', 'SDG') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label font-semibold">ملاحظات التحويل</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">تأكيد التحويل</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
