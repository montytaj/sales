<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('work-orders.index'), 'label' => __('workshop.orders_list')],
                ['label' => $workOrder->work_order_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <h2 class="h4 mb-0 font-bold text-dark">
                    <i class="bi bi-tools text-primary me-2"></i>أمر عمل CNC: {{ $workOrder->work_order_number }}
                </h2>
                <span class="badge bg-primary-subtle text-primary border border-primary fs-6">
                    {{ __('workshop.statuses.' . $workOrder->status) }}
                </span>
            </div>

            <div class="d-flex gap-2">
                @can('authorize-work-order-start')
                    @if (!$workOrder->authorization)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#authorizeModal">
                            <i class="bi bi-shield-check me-1"></i>{{ __('workshop.issue_authorization') }}
                        </button>
                    @endif
                @endcan

                @can('override-work-order-start')
                    @if (!$workOrder->authorization)
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#overrideModal">
                            <i class="bi bi-shield-exclamation me-1"></i>{{ __('workshop.override_authorization') }}
                        </button>
                    @endif
                @endcan

                @can('deliver-work-orders')
                    @if (in_array($workOrder->status, ['completed', 'ready_for_delivery']))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deliverModal">
                            <i class="bi bi-box-seam me-1"></i>{{ __('workshop.deliver_order') }}
                        </button>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- Start Authorization Banner / Status -->
    @if ($workOrder->authorization)
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center justify-content-between mb-4">
            <div>
                <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                <strong>تصريح بدء العمل معتمد:</strong> تم الإصدار بواسطة ({{ $workOrder->authorization->authorizer->name }}) بتاريخ ({{ $workOrder->authorization->authorized_at->format('Y-m-d H:i') }}).
                @if ($workOrder->authorization->is_override)
                    <span class="badge bg-warning text-dark ms-2">تجاوز استثنائي: {{ $workOrder->authorization->override_reason }}</span>
                @endif
            </div>
            <div>
                <span class="small">المبلغ المدفوع: <strong>{{ number_format($workOrder->authorization->paid_amount, 2) }} {{ setting('currency', 'SDG') }}</strong> | الرصيد: <strong>{{ number_format($workOrder->authorization->remaining_balance, 2) }} {{ setting('currency', 'SDG') }}</strong></span>
            </div>
        </div>
    @else
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center justify-content-between mb-4">
            <div>
                <i class="bi bi-shield-x fs-4 me-2"></i>
                <strong>تنبيه صارم:</strong> لم يتم إصدار تصريح بدء العمل بعد. يمنع فني الورشة من البدء التشغيلي دون تصريح معتمد أو تجاوز استثنائي.
            </div>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- CNC Specifications -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-layers me-2"></i>مواصفات القص والألواح</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">{{ __('workshop.sheet_count') }}</span>
                            <h4 class="font-bold text-dark mb-0">{{ $workOrder->sheet_count }} ألواح</h4>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">{{ __('workshop.sheet_type') }}</span>
                            <h5 class="font-bold text-primary mb-0">{{ $workOrder->sheet_type }}</h5>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">{{ __('workshop.dimensions') }}</span>
                            <strong class="text-dark">{{ $workOrder->dimensions ?? '-' }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">{{ __('workshop.thickness') }}</span>
                            <strong class="text-dark">{{ $workOrder->thickness ?? '-' }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">{{ __('workshop.good_pieces') }}</span>
                            <span class="badge bg-success-subtle text-success fs-6">{{ $workOrder->good_pieces }} قطعة</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">{{ __('workshop.waste_pieces') }}</span>
                            <span class="badge bg-danger-subtle text-danger fs-6">{{ $workOrder->waste_pieces }} تالف</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Assignment Info -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-person me-2"></i>العميل والمسؤول بالورشة</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="text-muted d-block small mb-1">{{ __('customers.name') }}</span>
                        <h5 class="font-bold text-dark mb-0">{{ $workOrder->customer->name }}</h5>
                        <small class="text-muted">{{ $workOrder->customer->phone }}</small>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block small mb-1">الفني / المسؤول بالورشة</span>
                        <strong class="text-dark fs-6">{{ $workOrder->assignee?->name ?? 'غير محدد' }}</strong>
                    </div>

                    @if ($workOrder->delivered_at)
                        <div class="p-3 bg-light rounded border border-success">
                            <span class="badge bg-success mb-1">تم التسليم رسمياً</span>
                            <p class="mb-1 small">المستلم: <strong>{{ $workOrder->delivery_receiver_name }}</strong></p>
                            <small class="text-muted">تاريخ التسليم: {{ $workOrder->delivered_at->format('Y-m-d H:i') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Time Logs Execution Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 font-bold text-dark"><i class="bi bi-stopwatch me-2"></i>سجل الأوقات وحركات التشغيل</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">الفني / العامل</th>
                            <th scope="col">إجراء التشغيل</th>
                            <th scope="col">التاريخ والوقت</th>
                            <th scope="col">ملاحظات التشغيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workOrder->timeLogs as $index => $log)
                            <tr>
                                <th scope="row" class="ps-3">{{ $index + 1 }}</th>
                                <td class="fw-semibold">{{ $log->user->name }}</td>
                                <td>
                                    <span class="badge {{ $log->action === 'start' ? 'bg-success' : ($log->action === 'pause' ? 'bg-warning text-dark' : ($log->action === 'resume' ? 'bg-info text-dark' : 'bg-primary')) }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td>{{ $log->logged_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $log->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">لا توجد حركات تشغيل مسجلة بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Authorize Start Modal -->
    <div class="modal fade" id="authorizeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('work-orders.authorize-start', $workOrder) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-bold">{{ __('workshop.issue_authorization') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>هل أنت تأكد من اعتماد تصريح بدء العمل لأمر العمل رقم <strong>{{ $workOrder->work_order_number }}</strong>؟</p>
                        <div class="mb-3">
                            <label for="auth_notes" class="form-label font-semibold">ملاحظات التصريح</label>
                            <textarea name="notes" id="auth_notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success">إصدار التصريح الآن</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Override Start Modal -->
    <div class="modal fade" id="overrideModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('work-orders.override-start', $workOrder) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-bold text-warning">{{ __('workshop.override_authorization') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            تنبيه: أنت تقوم بإصدار تصريح تجاوز استثنائي دون اكتمال الدفعة المالية المطلوب.
                        </div>
                        <div class="mb-3">
                            <label for="override_reason" class="form-label font-semibold">سبب التجاوز الاستثنائي <span class="text-danger">*</span></label>
                            <textarea name="override_reason" id="override_reason" rows="3" class="form-control" placeholder="كتابة السبب بالتفصيل للاعتماد والحفظ بسجل المراجعة..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-warning">اعتماد التجاوز وحفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Deliver Modal -->
    <div class="modal fade" id="deliverModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('work-orders.deliver', $workOrder) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-bold">{{ __('workshop.deliver_order') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="delivery_receiver_name" class="form-label font-semibold">{{ __('workshop.receiver_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="delivery_receiver_name" id="delivery_receiver_name" class="form-control" value="{{ $workOrder->customer->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="delivery_notes" class="form-label font-semibold">ملاحظات التسليم</label>
                            <textarea name="delivery_notes" id="delivery_notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">تأكيد التسليم الرسمي</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
