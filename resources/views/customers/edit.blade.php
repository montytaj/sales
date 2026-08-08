<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('customers.index'), 'label' => __('customers.customers_list')],
                ['label' => __('customers.edit_customer')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-pencil-square text-primary me-2"></i>{{ __('customers.edit_customer') }}: {{ $customer->name }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('customers.update', $customer) }}">
                        @csrf
                        @method('PUT')

                        <!-- Customer Type & Category -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="type" class="form-label font-semibold">{{ __('customers.type') }} <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="individual" {{ old('type', $customer->type) === 'individual' ? 'selected' : '' }}>{{ __('customers.individual') }}</option>
                                    <option value="company" {{ old('type', $customer->type) === 'company' ? 'selected' : '' }}>{{ __('customers.company') }}</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="category" class="form-label font-semibold">{{ __('customers.category') }} <span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="regular" {{ old('category', $customer->category) === 'regular' ? 'selected' : '' }}>{{ __('customers.regular') }}</option>
                                    <option value="vip" {{ old('category', $customer->category) === 'vip' ? 'selected' : '' }}>{{ __('customers.vip') }}</option>
                                    <option value="corporate" {{ old('category', $customer->category) === 'corporate' ? 'selected' : '' }}>{{ __('customers.corporate') }}</option>
                                    <option value="wholesale" {{ old('category', $customer->category) === 'wholesale' ? 'selected' : '' }}>{{ __('customers.wholesale') }}</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="branch_id" class="form-label font-semibold">الفرع التابع له</label>
                                <select name="branch_id" id="branch_id" class="form-select">
                                    <option value="">-- فرع عام --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $customer->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Name & Company Name -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label font-semibold">{{ __('customers.name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="company_name" class="form-label font-semibold">{{ __('customers.company_name') }}</label>
                                <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name', $customer->company_name) }}">
                            </div>
                        </div>

                        <!-- Phone Numbers & Email -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="phone" class="form-label font-semibold">{{ __('customers.phone') }} <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone) }}" required>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="phone_secondary" class="form-label font-semibold">{{ __('customers.phone_secondary') }}</label>
                                <input type="text" name="phone_secondary" id="phone_secondary" class="form-control" value="{{ old('phone_secondary', $customer->phone_secondary) }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="email" class="form-label font-semibold">{{ __('customers.email') }}</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $customer->email) }}">
                            </div>
                        </div>

                        <!-- Address & City -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-8">
                                <label for="address" class="form-label font-semibold">{{ __('customers.address') }}</label>
                                <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $customer->address) }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="city" class="form-label font-semibold">{{ __('customers.city') }}</label>
                                <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $customer->city) }}">
                            </div>
                        </div>

                        <!-- CR & VAT Numbers -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="cr_number" class="form-label font-semibold">{{ __('customers.cr_number') }}</label>
                                <input type="text" name="cr_number" id="cr_number" class="form-control" value="{{ old('cr_number', $customer->cr_number) }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="vat_number" class="form-label font-semibold">{{ __('customers.vat_number') }}</label>
                                <input type="text" name="vat_number" id="vat_number" class="form-control" value="{{ old('vat_number', $customer->vat_number) }}">
                            </div>
                        </div>

                        <!-- Credit Limit & Credit Period -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="credit_limit" class="form-label font-semibold">{{ __('customers.credit_limit') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="credit_limit" id="credit_limit" class="form-control" value="{{ old('credit_limit', $customer->credit_limit) }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="credit_period_days" class="form-label font-semibold">{{ __('customers.credit_period_days') }} <span class="text-danger">*</span></label>
                                <input type="number" name="credit_period_days" id="credit_period_days" class="form-control" value="{{ old('credit_period_days', $customer->credit_period_days) }}" required>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label font-semibold">{{ __('customers.notes') }}</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control">{{ old('notes', $customer->notes) }}</textarea>
                        </div>

                        <!-- Active Status Switch -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label font-semibold" for="is_active">{{ __('customers.active') }}</label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('customers.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>تحديث البيانات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
