<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('projects.surveys')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-geo-alt-fill text-primary me-2"></i>{{ __('projects.surveys') }}
            </h2>
            @can('create-surveys')
                <a href="{{ route('site-surveys.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('projects.create_survey') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">رقم المعاينة</th>
                            <th scope="col">{{ __('customers.name') }}</th>
                            <th scope="col">عنوان الموقع</th>
                            <th scope="col">المعاين المسؤول</th>
                            <th scope="col">تاريخ المعاينة</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($surveys as $survey)
                            <tr>
                                <td class="ps-3"><code>{{ $survey->survey_number }}</code></td>
                                <td class="fw-semibold">{{ $survey->customer->name }}</td>
                                <td>{{ $survey->site_address }}</td>
                                <td>{{ $survey->assignee?->name ?? 'غير محدد' }}</td>
                                <td>{{ $survey->survey_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-primary">{{ $survey->status }}</span></td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('site-surveys.show', $survey) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>عرض التفاصيل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد معاينات موقع مسجلة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($surveys->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $surveys->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
