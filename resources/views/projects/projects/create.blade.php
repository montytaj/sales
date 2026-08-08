<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('projects.index'), 'label' => __('projects.projects')],
                ['label' => __('projects.create_project')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-diagram-3-fill text-info me-2"></i>{{ __('projects.create_project') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('projects.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label font-semibold">اسم المشروع <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="مشروع تجهيز واجهات وفروع شركة...">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="customer_id" class="form-label font-semibold">{{ __('customers.name') }} <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select" required>
                                    <option value="">-- اختر العميل --</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="manager_id" class="form-label font-semibold">مدير / مسؤول المشروع</label>
                                <select name="manager_id" id="manager_id" class="form-select">
                                    <option value="">-- اختر مدير المشروع --</option>
                                    @foreach ($managers as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="budget" class="form-label font-semibold">الميزانية التقديرية <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="budget" id="budget" class="form-control" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="start_date" class="form-label font-semibold">تاريخ بداية التنفيذ <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="expected_end_date" class="form-label font-semibold">تاريخ الانتهاء المتوقع</label>
                                <input type="date" name="expected_end_date" id="expected_end_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label font-semibold">ملاحظات وتفاصيل التنفيذ</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('projects.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ المشروع
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
