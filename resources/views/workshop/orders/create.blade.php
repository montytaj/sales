<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('work-orders.index'), 'label' => __('workshop.orders_list')],
                ['label' => __('workshop.create_order')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-tools text-primary me-2"></i>{{ __('workshop.create_order') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('work-orders.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="customer_id" class="form-label font-semibold">{{ __('customers.name') }} <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                    <option value="">-- اختر العميل --</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $selectedInvoice?->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} ({{ $customer->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="invoice_id" class="form-label font-semibold">ربط بفاتورة مبيعات</label>
                                <select name="invoice_id" id="invoice_id" class="form-select">
                                    <option value="">-- أمر عمل مباشر (بدون فاتورة) --</option>
                                    @foreach ($invoices as $inv)
                                        <option value="{{ $inv->id }}" {{ old('invoice_id', $selectedInvoice?->id) == $inv->id ? 'selected' : '' }}>
                                            {{ $inv->invoice_number }} - {{ $inv->customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- CNC Sheet Specifications -->
                        <h6 class="font-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-layers me-2"></i>مواصفات وتفاصيل ألواح CNC</h6>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-3">
                                <label for="sheet_count" class="form-label font-semibold">{{ __('workshop.sheet_count') }} <span class="text-danger">*</span></label>
                                <input type="number" min="1" name="sheet_count" id="sheet_count" class="form-control" value="{{ old('sheet_count', '1') }}" required>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="sheet_type" class="form-label font-semibold">{{ __('workshop.sheet_type') }} <span class="text-danger">*</span></label>
                                <input type="text" name="sheet_type" id="sheet_type" class="form-control" placeholder="MDF, Plywood, Acrylic..." value="{{ old('sheet_type', 'MDF') }}" required>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="dimensions" class="form-label font-semibold">{{ __('workshop.dimensions') }}</label>
                                <input type="text" name="dimensions" id="dimensions" class="form-control" placeholder="122x244 cm" value="{{ old('dimensions', '122x244 cm') }}">
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="thickness" class="form-label font-semibold">{{ __('workshop.thickness') }}</label>
                                <input type="text" name="thickness" id="thickness" class="form-control" placeholder="18 mm" value="{{ old('thickness', '18 mm') }}">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="priority" class="form-label font-semibold">{{ __('workshop.priority') }} <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select" required>
                                    @foreach (__('workshop.priorities') as $pKey => $pName)
                                        <option value="{{ $pKey }}" {{ old('priority', 'normal') === $pKey ? 'selected' : '' }}>{{ $pName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="due_date" class="form-label font-semibold">الموعد النهائي المستهدف</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+3 days'))) }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="assigned_to" class="form-label font-semibold">تعيين الفني / المشرّف المسؤول</label>
                                <select name="assigned_to" id="assigned_to" class="form-select">
                                    <option value="">-- اختيار مسؤول بالورشة --</option>
                                    @foreach ($workers as $w)
                                        <option value="{{ $w->id }}" {{ old('assigned_to') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- CAD/CAM File Uploads -->
                        <div class="mb-4">
                            <label for="cad_files" class="form-label font-semibold">رفع ملفات التصميم والقص (CAD/CAM, DXF, DWG, PDF, صور)</label>
                            <input type="file" name="cad_files[]" id="cad_files" class="form-control" multiple>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label font-semibold">تعليمات فنية للقص والتنفيذ</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="ملاحظات تفريغ الحواف، نوع الريشة، تفاصيل الحفر..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('work-orders.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ أمر العمل
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
