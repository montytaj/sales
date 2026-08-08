<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('workshop.kiosk')]
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('workshop.kiosk')" :description="__('workshop.kiosk_desc') ?? 'شاشة التشغيل السريعة للفنيين ومقاطع ماكينات CNC'">
        <x-slot name="actions">
            <span class="badge bg-dark text-warning border border-warning px-3 py-2 fs-6 rounded-pill">
                <i class="bi bi-display me-1"></i> شاشة الفنيين المباشرة
            </span>
        </x-slot>
    </x-page-header>

    <!-- Active Orders for Operators Grid -->
    <div class="row g-4 mb-4">
        @forelse ($activeOrders as $order)
            <div class="col-12 col-md-6 col-lg-6">
                <div class="card card-custom h-100 border-start border-5 {{ $order->status === 'in_progress' ? 'border-success' : ($order->status === 'paused' ? 'border-warning' : 'border-primary') }} shadow-md">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-slate-100 text-slate-800 font-mono fs-7 me-1">{{ $order->work_order_number }}</span>
                                    <span class="badge {{ $order->priority === 'urgent' ? 'bg-danger text-white' : 'bg-info-subtle text-info-emphasis' }} font-bold me-1">
                                        {{ __('workshop.priorities.' . $order->priority) }}
                                    </span>
                                    <h4 class="font-extrabold text-slate-900 mt-2 mb-1">{{ $order->customer->name }}</h4>
                                </div>
                                <x-status-badge :status="$order->status" />
                            </div>

                            <!-- Sheet Details -->
                            <div class="p-3 bg-slate-50 rounded-lg border mb-3">
                                <div class="row g-2 fs-7 text-slate-700">
                                    <div class="col-6"><strong>عدد الألواح:</strong> {{ $order->sheet_count }} ألواح</div>
                                    <div class="col-6"><strong>نوع الخشب:</strong> {{ $order->sheet_type }}</div>
                                    <div class="col-6"><strong>المقاسات:</strong> {{ $order->dimensions ?? '-' }}</div>
                                    <div class="col-6"><strong>السماكة:</strong> {{ $order->thickness ?? '-' }}</div>
                                </div>
                            </div>

                            @if ($order->notes)
                                <div class="alert alert-warning py-2 px-3 small mb-3 border-0 shadow-2xs rounded-md">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <strong>تعليمات القص:</strong> {{ $order->notes }}
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons Kiosk UI (Large Touch Targets) -->
                        <div class="d-flex gap-2 flex-wrap pt-3 border-top">
                            @if ($order->status === 'authorized_to_start' || $order->status === 'pending_execution' || $order->status === 'pending')
                                <form method="POST" action="{{ route('workshop-kiosk.action', $order) }}" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="action" value="start">
                                    <button type="submit" class="btn btn-success btn-lg w-100 py-3 font-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-play-fill fs-3"></i>
                                        <span>بدء التشغيل والقص</span>
                                    </button>
                                </form>
                            @elseif ($order->status === 'in_progress')
                                <form method="POST" action="{{ route('workshop-kiosk.action', $order) }}" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="action" value="pause">
                                    <button type="submit" class="btn btn-warning btn-lg w-100 py-3 font-bold text-dark shadow-sm d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-pause-fill fs-3"></i>
                                        <span>إيقاف مؤقت</span>
                                    </button>
                                </form>

                                <button type="button" class="btn btn-primary btn-lg flex-grow-1 py-3 font-bold shadow-sm d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#completeModal_{{ $order->id }}">
                                    <i class="bi bi-check-all fs-3"></i>
                                    <span>إكمال العمل</span>
                                </button>
                            @elseif ($order->status === 'paused')
                                <form method="POST" action="{{ route('workshop-kiosk.action', $order) }}" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="action" value="resume">
                                    <button type="submit" class="btn btn-info btn-lg w-100 py-3 font-bold text-dark shadow-sm d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-play-circle-fill fs-3"></i>
                                        <span>استئناف العمل</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Complete Modal for Operator -->
            <div class="modal fade" id="completeModal_{{ $order->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content card-custom border-0 shadow-lg">
                        <form method="POST" action="{{ route('workshop-kiosk.action', $order) }}">
                            @csrf
                            <input type="hidden" name="action" value="complete">
                            <div class="modal-header border-bottom">
                                <h5 class="modal-title font-bold text-slate-800">إكمال تشغيل أمر العمل: {{ $order->work_order_number }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label for="good_{{ $order->id }}" class="form-label font-semibold">عدد القطع الناجحة <span class="text-danger">*</span></label>
                                        <input type="number" min="0" name="good_pieces" id="good_{{ $order->id }}" class="form-control form-control-lg text-success fw-bold" value="{{ $order->sheet_count }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label for="waste_{{ $order->id }}" class="form-label font-semibold">عدد القطع التالفة / الهالك</label>
                                        <input type="number" min="0" name="waste_pieces" id="waste_{{ $order->id }}" class="form-control form-control-lg text-danger fw-bold" value="0">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="kiosk_notes_{{ $order->id }}" class="form-label font-semibold">ملاحظات التشغيل والإكمال</label>
                                    <textarea name="notes" id="kiosk_notes_{{ $order->id }}" rows="2" class="form-control" placeholder="أدخل أي ملاحظات حول القص والجودة..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top bg-slate-50">
                                <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-success btn-lg shadow-sm font-bold">تأكيد الإكمال ونقل للتسليم</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <x-empty-state 
                    icon="bi-cpu" 
                    title="لا توجد أوامر عمل قيد التشغيل المباشر حالياً في الورشة" 
                    description="سيتم عرض جميع أوامر العمل المصرح بها والجاهزة للقص والتشغيل فور تجهيزها." />
            </div>
        @endforelse
    </div>
</x-app-layout>

