<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('suppliers.index'), 'label' => __('suppliers.suppliers_list')],
                ['label' => __('suppliers.create_supplier')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-truck-front-fill text-primary me-2"></i>{{ __('suppliers.create_supplier') }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('suppliers.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Supplier Name & Company Name -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label font-semibold">{{ __('suppliers.name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="company_name" class="form-label font-semibold">{{ __('suppliers.company_name') }}</label>
                                <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name') }}">
                            </div>
                        </div>

                        <!-- Contact Person & Rating -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="contact_person" class="form-label font-semibold">{{ __('suppliers.contact_person') }}</label>
                                <input type="text" name="contact_person" id="contact_person" class="form-control" value="{{ old('contact_person') }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="rating" class="form-label font-semibold">{{ __('suppliers.rating') }} <span class="text-danger">*</span></label>
                                <select name="rating" id="rating" class="form-select" required>
                                    <option value="5" {{ old('rating', 5) == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5 نجوم (ممتاز)</option>
                                    <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ 4 نجوم (جيد جداً)</option>
                                    <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>⭐⭐⭐ 3 نجوم (متوسط)</option>
                                    <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>⭐⭐ 2 نجوم (مقبول)</option>
                                    <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>⭐ 1 نجمة (ضعيف)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Phone Numbers & Email -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="phone" class="form-label font-semibold">{{ __('suppliers.phone') }} <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="phone_secondary" class="form-label font-semibold">{{ __('suppliers.phone_secondary') }}</label>
                                <input type="text" name="phone_secondary" id="phone_secondary" class="form-control" value="{{ old('phone_secondary') }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="email" class="form-label font-semibold">{{ __('suppliers.email') }}</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                            </div>
                        </div>

                        <!-- Address & City -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-8">
                                <label for="address" class="form-label font-semibold">{{ __('suppliers.address') }}</label>
                                <input type="text" name="address" id="address" class="form-control" value="{{ old('address') }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="city" class="form-label font-semibold">{{ __('suppliers.city') }}</label>
                                <input type="text" name="city" id="city" class="form-control" value="{{ old('city', 'الرياض') }}">
                            </div>
                        </div>

                        <!-- CR & VAT Numbers -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="cr_number" class="form-label font-semibold">{{ __('suppliers.cr_number') }}</label>
                                <input type="text" name="cr_number" id="cr_number" class="form-control" value="{{ old('cr_number') }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="vat_number" class="form-label font-semibold">{{ __('suppliers.vat_number') }}</label>
                                <input type="text" name="vat_number" id="vat_number" class="form-control" value="{{ old('vat_number') }}">
                            </div>
                        </div>

                        <!-- Services Provided -->
                        <div class="mb-3">
                            <label for="services_provided" class="form-label font-semibold">{{ __('suppliers.services_provided') }}</label>
                            <textarea name="services_provided" id="services_provided" rows="2" class="form-control" placeholder="مثال: ألواح خشبيات، أكريليك، اكسسوارات مفصلات..."></textarea>
                        </div>

                        <!-- Notes & Attachment -->
                        <div class="mb-3">
                            <label for="notes" class="form-label font-semibold">{{ __('suppliers.notes') }}</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="attachment" class="form-label font-semibold">مرفق عقد أو سند المورد</label>
                            <input type="file" name="attachment" id="attachment" class="form-control">
                        </div>

                        <!-- Active Status Switch -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', 1) ? 'checked' : '' }}>
                                <label class="form-check-label font-semibold" for="is_active">{{ __('suppliers.active') }}</label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>حفظ المورد
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
