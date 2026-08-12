<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => 'إدارة الشيكات والبنوك']
            ];
        @endphp
    </x-slot>

    <x-page-header title="إدارة وتتبع الشيكات والبنوك" description="متابعة الشيكات الواردة والصادرة وحالات التحصيل والارتداد والربط بالحسابات الخزنية والبنائية">
        <x-slot name="actions">
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="printSelectedDepositSlip()" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i> طباعة حافظة الشيكات
                </button>
            </div>
        </x-slot>
    </x-page-header>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <div class="card card-custom border-0 shadow-sm p-3 h-100">
                <span class="fs-8 font-bold text-slate-500 uppercase"><i class="bi bi-grid-fill me-1 text-primary"></i> كافة الشيكات</span>
                <h5 class="font-bold text-slate-900 mb-0 mt-2 fs-5">{{ number_format($counts['all']) }} <small class="fs-8 text-slate-500">شيك</small></h5>
                <small class="text-slate-500 fs-8 mt-1">{{ number_format($counts['all_amount'], 2) }} ر.س</small>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card card-custom border-0 shadow-sm p-3 h-100 border-start border-4 border-info">
                <span class="fs-8 font-bold text-info uppercase"><i class="bi bi-arrow-down-left-circle me-1"></i> شيكات واردة</span>
                <h5 class="font-bold text-slate-900 mb-0 mt-2 fs-5">{{ number_format($counts['incoming']) }} <small class="fs-8 text-slate-500">شيك</small></h5>
                <small class="text-slate-500 fs-8 mt-1">{{ number_format($counts['incoming_amount'], 2) }} ر.س</small>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card card-custom border-0 shadow-sm p-3 h-100 border-start border-4 border-warning">
                <span class="fs-8 font-bold text-warning uppercase"><i class="bi bi-arrow-up-right-circle me-1"></i> شيكات صادرة</span>
                <h5 class="font-bold text-slate-900 mb-0 mt-2 fs-5">{{ number_format($counts['outgoing']) }} <small class="fs-8 text-slate-500">شيك</small></h5>
                <small class="text-slate-500 fs-8 mt-1">{{ number_format($counts['outgoing_amount'], 2) }} ر.س</small>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card card-custom border-0 shadow-sm p-3 h-100 border-start border-4 border-primary">
                <span class="fs-8 font-bold text-primary uppercase"><i class="bi bi-clock-history me-1"></i> تحت التحصيل</span>
                <h5 class="font-bold text-slate-900 mb-0 mt-2 fs-5">{{ number_format($counts['pending']) }} <small class="fs-8 text-slate-500">شيك</small></h5>
                <small class="text-slate-500 fs-8 mt-1">{{ number_format($counts['pending_amount'], 2) }} ر.س</small>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card card-custom border-0 shadow-sm p-3 h-100 border-start border-4 border-success">
                <span class="fs-8 font-bold text-success uppercase"><i class="bi bi-check-circle me-1"></i> {{ __('payments.cheque_statuses.collected') }}</span>
                <h5 class="font-bold text-slate-900 mb-0 mt-2 fs-5">{{ number_format($counts['cleared']) }} <small class="fs-8 text-slate-500">شيك</small></h5>
                <small class="text-slate-500 fs-8 mt-1">{{ number_format($counts['cleared_amount'], 2) }} ر.س</small>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Tabs -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-2 overflow-x-auto">
            <ul class="nav nav-pills gap-1 flex-nowrap min-w-max">
                <li class="nav-item">
                    <a href="{{ route('cheques.index') }}" class="nav-link btn-sm rounded-3 py-2 px-3 {{ !request()->has('tab') && !request()->has('type') ? 'active font-bold' : 'text-slate-600' }}">
                        <i class="bi bi-grid me-1"></i> كافة الشيكات <span class="badge bg-slate-200 text-slate-800 ms-1">{{ $counts['all'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('cheques.index', ['tab' => 'incoming']) }}" class="nav-link btn-sm rounded-3 py-2 px-3 {{ request('tab') === 'incoming' ? 'active font-bold' : 'text-slate-600' }}">
                        <i class="bi bi-arrow-down-left-circle text-info me-1"></i> شيكات واردة <span class="badge bg-info-subtle text-info border border-info-subtle ms-1">{{ $counts['incoming'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('cheques.index', ['tab' => 'outgoing']) }}" class="nav-link btn-sm rounded-3 py-2 px-3 {{ request('tab') === 'outgoing' ? 'active font-bold' : 'text-slate-600' }}">
                        <i class="bi bi-arrow-up-right-circle text-warning me-1"></i> شيكات صادرة <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1">{{ $counts['outgoing'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('cheques.index', ['tab' => 'pending']) }}" class="nav-link btn-sm rounded-3 py-2 px-3 {{ request('tab') === 'pending' ? 'active font-bold' : 'text-slate-600' }}">
                        <i class="bi bi-clock-history text-primary me-1"></i> تحت التحصيل <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">{{ $counts['pending'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('cheques.index', ['tab' => 'cleared']) }}" class="nav-link btn-sm rounded-3 py-2 px-3 {{ request('tab') === 'cleared' ? 'active font-bold' : 'text-slate-600' }}">
                        <i class="bi bi-check-circle text-success me-1"></i> محصلة <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">{{ $counts['cleared'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('cheques.index', ['tab' => 'bounced']) }}" class="nav-link btn-sm rounded-3 py-2 px-3 {{ request('tab') === 'bounced' ? 'active font-bold' : 'text-slate-600' }}">
                        <i class="bi bi-exclamation-triangle text-danger me-1"></i> مرتدة <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1">{{ $counts['bounced'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Search & Filter Form -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('cheques.index') }}" class="row g-2 align-items-center">
                @if(request('tab'))
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                @endif
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-slate-400"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم الشيك، اسم البنك، أو الساحب..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">جميع الحالات</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>{{ __('payments.cheque_statuses.received') }}</option>
                        <option value="under_collection" {{ request('status') === 'under_collection' ? 'selected' : '' }}>{{ __('payments.cheque_statuses.under_collection') }}</option>
                        <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>{{ __('payments.cheque_statuses.collected') }}</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>{{ __('payments.cheque_statuses.returned') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('payments.cheque_statuses.cancelled') }}</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light fs-8">تاريخ الاستحقاق</span>
                        <input type="date" name="due_from" class="form-control" value="{{ request('due_from') }}" title="من تاريخ">
                        <input type="date" name="due_to" class="form-control" value="{{ request('due_to') }}" title="إلى تاريخ">
                    </div>
                </div>

                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm w-100 rounded-3">بحث</button>
                    @if (request()->hasAny(['search', 'status', 'due_from', 'due_to']))
                        <a href="{{ route('cheques.index', request()->only('tab')) }}" class="btn btn-light btn-sm text-slate-600 rounded-3"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Cheques Table Card -->
    <div class="card card-custom border-0 shadow-sm mb-5">
        <div class="card-body p-0">
            <form id="chequesBatchForm" method="GET" action="{{ route('cheques.deposit-slip') }}" target="_blank">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 border-slate-100 fs-7">
                        <thead class="bg-slate-50 text-slate-700 font-semibold border-bottom">
                            <tr>
                                <th style="width: 35px;" class="ps-3">
                                    <input type="checkbox" id="selectAllCheques" class="form-check-input">
                                </th>
                                <th>رقم الشيك</th>
                                <th>البنك</th>
                                <th>الطرف</th>
                                <th>الاستحقاق</th>
                                <th>النوع</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th class="pe-3 text-end">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cheques as $cheque)
                                @php
                                    $isCollected = $cheque->status === 'collected';
                                    $isReturned = $cheque->status === 'returned';
                                    $isCancelled = $cheque->status === 'cancelled';
                                    $statusBadge = $isCollected ? 'bg-success-subtle text-success border-success-subtle' : ($isReturned ? 'bg-danger-subtle text-danger border-danger-subtle' : ($isCancelled ? 'bg-slate-200 text-slate-700' : 'bg-primary-subtle text-primary border-primary-subtle'));
                                    $statusText = __('payments.cheque_statuses.' . $cheque->status);
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <input type="checkbox" name="cheque_ids[]" value="{{ $cheque->id }}" class="form-check-input cheque-checkbox">
                                    </td>
                                    <td>
                                        <a href="{{ route('cheques.show', $cheque) }}" class="font-bold font-mono text-primary text-decoration-none">
                                            {{ $cheque->cheque_number }}
                                        </a>
                                    </td>
                                    <td class="font-medium text-slate-800">{{ $cheque->bank_name }}</td>
                                    <td>
                                        <div class="font-bold text-slate-900">{{ $cheque->drawer_name ?? '-' }}</div>
                                        @if($cheque->voucher?->customer)
                                            <small class="text-slate-400 d-block fs-8"><i class="bi bi-person me-1"></i>عميل: {{ $cheque->voucher->customer->name }}</small>
                                        @elseif($cheque->voucher?->supplier)
                                            <small class="text-slate-400 d-block fs-8"><i class="bi bi-truck me-1"></i>مورد: {{ $cheque->voucher->supplier->name }}</small>
                                        @endif
                                    </td>
                                    <td class="font-mono text-slate-600">{{ $cheque->due_date ? $cheque->due_date->format('Y-m-d') : '-' }}</td>
                                    <td>
                                        <span class="badge {{ $cheque->type === 'outgoing' ? 'bg-warning-subtle text-warning border-warning-subtle' : 'bg-info-subtle text-info border-info-subtle' }} rounded-pill px-2 py-0.5">
                                            {{ $cheque->type === 'outgoing' ? 'صادر' : 'وارد' }}
                                        </span>
                                    </td>
                                    <td><strong class="text-emerald-700 font-bold fs-6">{{ number_format($cheque->amount, 2) }} <small class="fs-8">ر.س</small></strong></td>
                                    <td>
                                        <span class="badge border {{ $statusBadge }} rounded-pill px-2.5 py-1">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="pe-3 text-end text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            @if(!$isCollected && !$isCancelled)
                                                <button type="button" class="btn btn-action-icon btn-action-success" data-bs-toggle="modal" data-bs-target="#clearChequeModal{{ $cheque->id }}" title="تحصيل">
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-action-icon btn-action-delete" data-bs-toggle="modal" data-bs-target="#bounceChequeModal{{ $cheque->id }}" title="ارتداد">
                                                    <i class="bi bi-arrow-return-right"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('cheques.show', $cheque) }}" class="btn btn-action-icon btn-action-show" title="عرض">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>

                                        <!-- Clear Cheque Modal -->
                                        <div class="modal fade text-start" id="clearChequeModal{{ $cheque->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-3">
                                                    <form method="POST" action="{{ route('cheques.clear', $cheque) }}">
                                                        @csrf
                                                        <div class="modal-header bg-success text-white">
                                                            <h6 class="modal-title font-bold"><i class="bi bi-bank me-2"></i>تحصيل وإيداع الشيك رقم {{ $cheque->cheque_number }}</h6>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-4 fs-7">
                                                            <div class="alert alert-info bg-info-subtle border-0 rounded-3 p-3 mb-3">
                                                                <div><strong>المبلغ:</strong> {{ number_format($cheque->amount, 2) }} ر.س</div>
                                                                <div><strong>البنك/الساحب:</strong> {{ $cheque->bank_name }} - {{ $cheque->drawer_name }}</div>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label font-bold text-slate-700">اختر حساب البنك / الخزينة المودع فيها الشيك *</label>
                                                                <select name="cashbox_id" class="form-select" required>
                                                                    <option value="">-- حدد البنك/الخزينة --</option>
                                                                    @foreach($cashboxes as $cb)
                                                                        <option value="{{ $cb->id }}">{{ $cb->name }} (الرصيد: {{ number_format($cb->current_balance, 2) }} ر.س)</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label font-bold text-slate-700">تاريخ التحصيل الفعلي *</label>
                                                                <input type="date" name="clear_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label text-slate-700">ملاحظات التحصيل / رقم الإيداع</label>
                                                                <textarea name="notes" rows="2" class="form-control" placeholder="ملاحظات اختيارية..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">إلغاء</button>
                                                            <button type="submit" class="btn btn-success btn-sm font-bold px-4">تأكيد التحصيل وإصدار القيد</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bounce Cheque Modal -->
                                        <div class="modal fade text-start" id="bounceChequeModal{{ $cheque->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-3">
                                                    <form method="POST" action="{{ route('cheques.bounce', $cheque) }}">
                                                        @csrf
                                                        <div class="modal-header bg-danger text-white">
                                                            <h6 class="modal-title font-bold"><i class="bi bi-exclamation-triangle me-2"></i>إثبات ارتداد الشيك رقم {{ $cheque->cheque_number }}</h6>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-4 fs-7">
                                                            <div class="mb-3">
                                                                <label class="form-label font-bold text-slate-700">سبب ارتداد الشيك (عدم كفاية رصيد / اختلاف التوقيع ...) *</label>
                                                                <textarea name="notes" rows="3" class="form-control" required placeholder="اذكر سبب الإرجاع بالتفصيل..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">إلغاء</button>
                                                            <button type="submit" class="btn btn-danger btn-sm font-bold px-4">تسجيل ارتداد الشيك</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-slate-400">
                                        <i class="bi bi-credit-card-2-front fs-1 d-block mb-2"></i>
                                        لا توجد شيكات تطابق معايير البحث والفلترة المحددة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        @if ($cheques->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $cheques->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAllCheques');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    document.querySelectorAll('.cheque-checkbox').forEach(cb => cb.checked = this.checked);
                });
            }
        });

        function printSelectedDepositSlip() {
            const checked = document.querySelectorAll('.cheque-checkbox:checked');
            if (checked.length === 0) {
                // Submit empty to print all pending deposit slip
                document.getElementById('chequesBatchForm').submit();
            } else {
                document.getElementById('chequesBatchForm').submit();
            }
        }
    </script>
    @endpush
</x-app-layout>
