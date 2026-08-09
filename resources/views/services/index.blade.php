<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('services.services_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-tools text-primary me-2"></i>{{ __('services.services_list') }}
            </h2>
            @can('create-services')
                <a href="{{ route('services.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('services.create_service') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Search & Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('services.index') }}" class="row g-3">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث باسم الخدمة أو الكود أو الوصف..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <select name="service_type" class="form-select">
                        <option value="">-- نوع الخدمة --</option>
                        @foreach (__('services.types') as $typeKey => $typeName)
                            <option value="{{ $typeKey }}" {{ request('service_type') === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- كافة الحالات --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('services.active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('services.inactive') }}</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'service_type', 'status']))
                        <a href="{{ route('services.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Services Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('services.code') }}</th>
                            <th scope="col">{{ __('services.name_ar') }}</th>
                            <th scope="col">{{ __('services.service_type') }}</th>
                            <th scope="col">{{ __('services.default_price') }}</th>
                            <th scope="col">{{ __('services.unit_of_measure') }}</th>
                            <th scope="col">{{ __('services.is_taxable') }}</th>
                            <th scope="col">{{ __('services.status') }}</th>
                            <th scope="col" class="text-end pe-3">{{ __('services.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td class="ps-3"><code>{{ $service->code }}</code></td>
                                <td class="fw-semibold">
                                    <a href="{{ route('services.show', $service) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $service->name }}
                                        @if ($service->name_en)
                                            <small class="text-muted d-block">{{ $service->name_en }}</small>
                                        @endif
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary">
                                        {{ __('services.types.' . $service->service_type) }}
                                    </span>
                                </td>
                                <td><strong class="text-success">{{ number_format($service->default_price, 2) }} {{ setting('currency', 'SDG') }}</strong></td>
                                <td><span class="badge bg-light text-dark border">{{ __('services.units.' . $service->unit_of_measure) }}</span></td>
                                <td>
                                    @if ($service->is_taxable)
                                        <span class="badge bg-success-subtle text-success">خاضعة ({{ setting('tax_percentage', 15.00) }}%)</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">معفاة</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($service->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success">{{ __('services.active') }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger">{{ __('services.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('services.show', $service) }}" class="btn btn-outline-secondary" title="{{ __('services.show_service') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @can('edit-services')
                                            <a href="{{ route('services.edit', $service) }}" class="btn btn-outline-primary" title="{{ __('services.edit_service') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('delete-services')
                                            <form method="POST" action="{{ route('services.destroy', $service) }}" class="d-inline" onsubmit="return confirm('هل أنت تأكد من حذف هذه الخدمة؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-gear-wide-connected fs-3 d-block mb-2"></i>
                                    {{ __('services.no_services_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($services->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
