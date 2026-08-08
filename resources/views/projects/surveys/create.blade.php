<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('site-surveys.index'), 'label' => __('projects.surveys')],
                ['label' => __('projects.create_survey')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-geo-alt-fill text-primary me-2"></i>{{ __('projects.create_survey') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('site-surveys.store') }}" enctype="multipart/form-data">
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
                                <label for="assigned_to" class="form-label font-semibold">المعاين المسند إليه</label>
                                <select name="assigned_to" id="assigned_to" class="form-select">
                                    <option value="">-- اختر الفني / المهندس --</option>
                                    @foreach ($surveyors as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="site_address" class="form-label font-semibold">عنوان موقع المعاينة <span class="text-danger">*</span></label>
                            <input type="text" name="site_address" id="site_address" class="form-control" required placeholder="الرياض، طريق الملك فهد، حي الصحافة...">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="location_coordinates" class="form-label font-semibold">إحداثيات الموقع (GPS Coordinates / رابط الخريطة)</label>
                                <input type="text" name="location_coordinates" id="location_coordinates" class="form-control" placeholder="24.7136, 46.6753">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="survey_date" class="form-label font-semibold">تاريخ المعاينة <span class="text-danger">*</span></label>
                                <input type="date" name="survey_date" id="survey_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="dimensions_data" class="form-label font-semibold">المقاسات والأبعاد المأخوذة بالموقع</label>
                            <textarea name="dimensions_data" id="dimensions_data" rows="3" class="form-control" placeholder="مقاسات الواجهة، الارتفاع، العمق، زوايا التثبيت..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="photos" class="form-label font-semibold">صور الموقع والمبنى</label>
                            <input type="file" name="photos[]" id="photos" class="form-control" multiple accept="image/*">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('site-surveys.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ المعاينة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
