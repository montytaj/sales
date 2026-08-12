<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-file-earmark-arrow-right text-primary me-2"></i>{{ __('transfers.transfer_details') }} #{{ $transfer->transfer_number }}
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('warehouse-transfers.print', $transfer) }}" target="_blank" class="btn btn-outline-secondary rounded-3">
                    <i class="bi bi-printer me-1"></i>{{ __('transfers.print') }}
                </a>
                <a href="{{ route('warehouse-transfers.index') }}" class="btn btn-light border rounded-3">
                    <i class="bi bi-arrow-right me-1"></i>{{ __('transfers.transfers_list') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Main Details Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-bold text-dark">
                        <i class="bi bi-info-circle me-1 text-primary"></i>بيانات الطلب
                    </h5>
                    <div>{!! $transfer->status_badge !!}</div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-primary-subtle rounded-3 border border-primary-subtle">
                                <small class="text-primary font-semibold d-block mb-1"><i class="bi bi-box-arrow-up-right me-1"></i>{{ __('transfers.from_warehouse') }}</small>
                                <h5 class="fw-bold mb-0 text-dark">{{ $transfer->fromWarehouse?->name }}</h5>
                                <small class="text-muted font-mono">كود: {{ $transfer->fromWarehouse?->code }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-info-subtle rounded-3 border border-info-subtle">
                                <small class="text-info font-semibold d-block mb-1"><i class="bi bi-box-arrow-in-down-left me-1"></i>{{ __('transfers.to_warehouse') }}</small>
                                <h5 class="fw-bold mb-0 text-dark">{{ $transfer->toWarehouse?->name }}</h5>
                                <small class="text-muted font-mono">كود: {{ $transfer->toWarehouse?->code }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-list-task me-1"></i>الأصناف المحولة</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('transfers.item_name') }}</th>
                                    <th>{{ __('transfers.unit') }}</th>
                                    <th>{{ __('transfers.quantity') }}</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfer->items as $itemRow)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold text-dark">{{ $itemRow->item?->name }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $itemRow->item?->unit }}</span></td>
                                        <td><span class="fw-bold text-primary fs-6 font-mono">{{ number_format($itemRow->quantity, 2) }}</span></td>
                                        <td class="text-muted fs-7">{{ $itemRow->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($transfer->notes)
                        <div class="p-3 bg-light rounded-3 border mt-3">
                            <small class="text-muted fw-bold d-block mb-1">{{ __('transfers.notes') }}:</small>
                            <p class="mb-0 text-dark fs-7">{{ $transfer->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Actions & Audit Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-gear me-1 text-primary"></i>الإجراءات والرقابة</h5>
                </div>
                <div class="card-body p-4">
                    @if($transfer->status === 'pending')
                        <div class="alert alert-warning border-0 rounded-3 mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            طلب التحويل حالياً في حالة <strong>قيد الانتظار</strong>. لم يتم خصم وإضافة الكميات المخزنية بعد.
                        </div>

                        @can('approve-warehouse-transfers')
                            <form action="{{ route('warehouse-transfers.complete', $transfer) }}" method="POST" class="mb-2" onsubmit="return confirm('{{ __('transfers.approve_confirm') }}');">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 py-2.5 font-bold shadow-sm rounded-3">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('transfers.complete_transfer') }}
                                </button>
                            </form>
                        @endcan

                        @can('delete-warehouse-transfers')
                            <form action="{{ route('warehouse-transfers.cancel', $transfer) }}" method="POST" class="mb-2" onsubmit="return confirm('{{ __('transfers.cancel_confirm') }}');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 py-2 rounded-3">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('transfers.cancel_transfer') }}
                                </button>
                            </form>
                        @endcan
                    @elseif($transfer->status === 'completed')
                        <div class="alert alert-success border-0 rounded-3 mb-3">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            تم ترحيل واعتماد هذا التحويل المخزني بنجاح، وتحديث أرصدة المخازن المحول منها وإليها.
                        </div>
                        @can('delete-warehouse-transfers')
                            <form action="{{ route('warehouse-transfers.cancel', $transfer) }}" method="POST" class="mb-2" onsubmit="return confirm('هل أنت تأكد من رغبتك في عكس وإلغاء هذا التحويل المخزني المكتمل وإعادة الكميات للمخزن المصدر؟');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 py-2 rounded-3">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>عكس وإلغاء التحويل المكتمل
                                </button>
                            </form>
                        @endcan

                    @else
                        <div class="alert alert-danger border-0 rounded-3 mb-3">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            تم إلغاء طلب التحويل هذا.
                        </div>
                    @endif

                    <hr class="my-3">

                    <!-- Audit info -->
                    <div class="vstack gap-2 fs-7">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('transfers.transfer_date') }}:</span>
                            <span class="font-mono text-dark fw-bold">{{ $transfer->transfer_date?->format('Y-m-d') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('transfers.created_by') }}:</span>
                            <span class="text-dark fw-semibold">{{ $transfer->creator?->name ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('transfers.approved_by') }}:</span>
                            <span class="text-dark fw-semibold">{{ $transfer->approver?->name ?? '-' }}</span>
                        </div>
                        @if($transfer->completed_at)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">{{ __('transfers.completed_at') }}:</span>
                                <span class="font-mono text-dark fs-8">{{ $transfer->completed_at?->format('Y-m-d H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
