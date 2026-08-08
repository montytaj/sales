<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('signage-orders.index'), 'label' => __('projects.signage')],
                ['label' => $signageOrder->order_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <h2 class="h4 mb-0 font-bold text-dark">
                    <i class="bi bi-easel2-fill text-warning me-2"></i>طلب لافتة: {{ $signageOrder->order_number }}
                </h2>
                <span class="badge bg-primary fs-6">{{ $signageOrder->status }}</span>
            </div>

            <div class="d-flex gap-2">
                @can('manage-signage')
                    @if (!$signageOrder->design_approved)
                        <form method="POST" action="{{ route('signage-orders.approve-design', $signageOrder) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>اعتماد التصميم ونقل للتصنيع
                            </button>
                        </form>
                    @endif
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateSignageStatusModal">
                        <i class="bi bi-sliders me-1"></i>تحديث التصنيع والتركيب
                    </button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-info-circle me-2"></i>بيانات ومواصفات اللافتة</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="text-muted d-block small mb-1">{{ __('customers.name') }}</span>
                        <h5 class="font-bold text-dark mb-0">{{ $signageOrder->customer->name }}</h5>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block small mb-1">المقاسات والأبعاد</span>
                        <h4 class="font-bold text-primary mb-0">{{ $signageOrder->dimensions }}</h4>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">حالة التصنيع</span>
                            <span class="badge bg-secondary">{{ $signageOrder->manufacturing_status }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small mb-1">حالة التركيب</span>
                            <span class="badge bg-info text-dark">{{ $signageOrder->installation_status }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-shield-check me-2"></i>الضمان والتصميم</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="text-muted d-block small mb-1">اعتماد التصميم</span>
                        @if ($signageOrder->design_approved)
                            <span class="badge bg-success-subtle text-success fs-6"><i class="bi bi-check-circle me-1"></i>معتمد من {{ $signageOrder->designApprover?->name }}</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning fs-6">بانتظار الاعتماد</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block small mb-1">مدة الضمان والصيانة</span>
                        <strong class="text-dark fs-6">{{ $signageOrder->warranty_months }} شهور ضمان شامل</strong>
                    </div>

                    @if ($signageOrder->installer_name)
                        <div class="p-3 bg-light rounded">
                            <span class="badge bg-success mb-1">تم التركيب الميداني</span>
                            <p class="mb-0 small">الفني المسير: <strong>{{ $signageOrder->installer_name }}</strong></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Update Signage Status Modal -->
    <div class="modal fade" id="updateSignageStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('signage-orders.update-status', $signageOrder) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title font-bold">تحديث مرحلة التصنيع والتركيب</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="mfg_status" class="form-label font-semibold">مرحلة التصنيع بالورشة</label>
                            <select name="manufacturing_status" id="mfg_status" class="form-select">
                                <option value="pending" {{ $signageOrder->manufacturing_status === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="in_progress" {{ $signageOrder->manufacturing_status === 'in_progress' ? 'selected' : '' }}>قيد التصنيع</option>
                                <option value="completed" {{ $signageOrder->manufacturing_status === 'completed' ? 'selected' : '' }}>مكتمل ومستعد للتركيب</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="inst_status" class="form-label font-semibold">مرحلة التركيب الميداني</label>
                            <select name="installation_status" id="inst_status" class="form-select">
                                <option value="pending" {{ $signageOrder->installation_status === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="scheduled" {{ $signageOrder->installation_status === 'scheduled' ? 'selected' : '' }}>مجدول للتركيب</option>
                                <option value="installed" {{ $signageOrder->installation_status === 'installed' ? 'selected' : '' }}>تم التركيب بنجاح</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="installer_name" class="form-label font-semibold">اسم الفني / مسؤول التركيبات</label>
                            <input type="text" name="installer_name" id="installer_name" class="form-control" value="{{ $signageOrder->installer_name }}">
                        </div>

                        <div class="mb-3">
                            <label for="installation_date" class="form-label font-semibold">تاريخ التركيب</label>
                            <input type="date" name="installation_date" id="installation_date" class="form-control" value="{{ $signageOrder->installation_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التحديثات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
