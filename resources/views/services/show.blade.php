<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('services.index'), 'label' => __('services.services_list')],
                ['label' => __('services.show_service')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-tools text-primary me-2"></i>{{ __('services.show_service') }}: {{ $service->name_ar }}
            </h2>
            @can('edit-services')
                <a href="{{ route('services.edit', $service) }}" class="btn btn-outline-primary d-flex align-items-center gap-1">
                    <i class="bi bi-pencil"></i> {{ __('services.edit_service') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 bg-primary-subtle text-primary rounded-circle mb-3">
                        <i class="bi bi-tools fs-1"></i>
                    </div>
                    <h3 class="font-bold text-dark mb-1">{{ $service->name_ar }}</h3>
                    @if ($service->name_en)
                        <p class="text-muted mb-2 font-semibold">{{ $service->name_en }}</p>
                    @endif
                    <p class="text-muted mb-3"><code>{{ $service->code }}</code></p>

                    <div class="mb-4">
                        <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fs-6">
                            {{ __('services.types.' . $service->service_type) }}
                        </span>
                        @if ($service->is_active)
                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6 ms-1">{{ __('services.active') }}</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6 ms-1">{{ __('services.inactive') }}</span>
                        @endif
                    </div>

                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="row">
                            <div class="col-6 border-end">
                                <span class="text-muted d-block mb-1">{{ __('services.default_price') }}</span>
                                <h4 class="font-bold text-success mb-0">{{ number_format($service->default_price, 2) }} {{ setting('currency', 'SDG') }}</h4>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block mb-1">{{ __('services.unit_of_measure') }}</span>
                                <h4 class="font-bold text-dark mb-0">{{ __('services.units.' . $service->unit_of_measure) }}</h4>
                            </div>
                        </div>
                    </div>

                    @if ($service->description)
                        <div class="text-start">
                            <h6 class="font-bold text-dark mb-2"><i class="bi bi-info-circle text-info me-1"></i>{{ __('services.description') }}:</h6>
                            <p class="text-muted mb-0">{{ $service->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
