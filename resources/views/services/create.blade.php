<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('services.index'), 'label' => __('services.services_list')],
                ['label' => __('services.create_service')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-tools text-primary me-2"></i>{{ __('services.create_service') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('services.store') }}">
                        @csrf

                        <!-- Names AR / EN -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="name_ar" class="form-label font-semibold">{{ __('services.name_ar') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar') }}" placeholder="مثال: قص أخشاب CNC" required autofocus>
                                @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="name_en" class="form-label font-semibold">{{ __('services.name_en') }}</label>
                                <input type="text" name="name_en" id="name_en" class="form-control" value="{{ old('name_en') }}" placeholder="Ex: CNC Wood Cutting">
                            </div>
                        </div>

                        <!-- Type & Price & Unit -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="service_type" class="form-label font-semibold">{{ __('services.service_type') }} <span class="text-danger">*</span></label>
                                <select name="service_type" id="service_type" class="form-select @error('service_type') is-invalid @enderror" required>
                                    @foreach (__('services.types') as $typeKey => $typeName)
                                        <option value="{{ $typeKey }}" {{ old('service_type') === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="default_price" class="form-label font-semibold">{{ __('services.default_price') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="default_price" id="default_price" class="form-control" value="{{ old('default_price', '0.00') }}" required>
                                    <span class="input-group-text">{{ setting('currency', 'SDG') }}</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="unit_of_measure" class="form-label font-semibold">{{ __('services.unit_of_measure') }} <span class="text-danger">*</span></label>
                                <select name="unit_of_measure" id="unit_of_measure" class="form-select" required>
                                    @foreach (__('services.units') as $unitKey => $unitName)
                                        <option value="{{ $unitKey }}" {{ old('unit_of_measure', 'piece') === $unitKey ? 'selected' : '' }}>{{ $unitName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label font-semibold">{{ __('services.description') }}</label>
                            <textarea name="description" id="description" rows="3" class="form-control" placeholder="تفاصيل ومكونات الخدمة وما يشمله السعر الافتراضي...">{{ old('description') }}</textarea>
                        </div>

                        <!-- Switches: Taxable & Active -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="form-check form-switch p-3 bg-light rounded border">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_taxable" value="1" id="is_taxable" {{ old('is_taxable', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label font-semibold" for="is_taxable">
                                        {{ __('services.is_taxable') }} ({{ setting('tax_percentage', 15.00) }}%)
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-check form-switch p-3 bg-light rounded border">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label font-semibold" for="is_active">
                                        {{ __('services.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('services.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ الخدمة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
