<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('site-surveys.index'), 'label' => __('projects.surveys')],
                ['label' => $siteSurvey->survey_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-geo-alt-fill text-primary me-2"></i>معاينة موقع: {{ $siteSurvey->survey_number }}
        </h2>
    </x-slot>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <span class="text-muted d-block small mb-1">{{ __('customers.name') }}</span>
                    <h5 class="font-bold text-dark mb-0">{{ $siteSurvey->customer->name }}</h5>
                </div>
                <div class="col-12 col-md-4">
                    <span class="text-muted d-block small mb-1">عنوان الموقع</span>
                    <strong class="text-dark">{{ $siteSurvey->site_address }}</strong>
                </div>
                <div class="col-12 col-md-4">
                    <span class="text-muted d-block small mb-1">تاريخ المعاينة والمعاين</span>
                    <strong>{{ $siteSurvey->survey_date->format('Y-m-d') }} ({{ $siteSurvey->assignee?->name ?? 'غير مسند' }})</strong>
                </div>
            </div>

            @if ($siteSurvey->dimensions_data)
                <hr class="my-3">
                <h6 class="font-bold text-primary mb-2">المقاسات والأبعاد الميدانية</h6>
                <p class="mb-0 text-dark">{{ $siteSurvey->dimensions_data }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
