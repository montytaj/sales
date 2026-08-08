<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('signage-orders.index'), 'label' => __('projects.signage')],
                ['label' => __('projects.create_signage')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-easel2-fill text-warning me-2"></i>{{ __('projects.create_signage') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('signage-orders.store') }}" enctype="multipart/form-data">
                        @csrf

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
                                <label for="project_id" class="form-label font-semibold">ربط بمشروع قائم (اختياري)</label>
                                <select name="project_id" id="project_id" class="form-select">
                                    <option value="">-- طلب لافتة مستقلة (بدون مشروع) --</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->project_number }} - {{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="dimensions" class="form-label font-semibold">المقاسات والأبعاد (مثل: 4x2 متر) <span class="text-danger">*</span></label>
                                <input type="text" name="dimensions" id="dimensions" class="form-control" placeholder="400x200 cm" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="warranty_months" class="form-label font-semibold">مدة الضمان (شهور)</label>
                                <input type="number" min="0" name="warranty_months" id="warranty_months" class="form-control" value="12">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="design_file" class="form-label font-semibold">ملف التصميم الأولي / الهوية</label>
                            <input type="file" name="design_file" id="design_file" class="form-control">
                        </div>

                        <div class="mb-4">
                            <label for="maintenance_notes" class="form-label font-semibold">ملاحظات الصيانة والتركيب</label>
                            <textarea name="maintenance_notes" id="maintenance_notes" rows="3" class="form-control" placeholder="تفاصيل الإضاءة، المحولات، الإكريليك، وحوامل التثبيت..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('signage-orders.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ طلب اللافتة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
