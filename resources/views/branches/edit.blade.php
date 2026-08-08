<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('branches.index'), 'label' => __('branches.branches_list')],
                ['label' => __('branches.edit_branch')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-pencil-square text-primary me-2"></i>{{ __('branches.edit_branch') }}: {{ $branch->name }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('branches.update', $branch) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-3">
                            <!-- Branch Code -->
                            <div class="col-12 col-md-4">
                                <label for="code" class="form-label font-semibold">{{ __('branches.code') }} <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $branch->code) }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Branch Name -->
                            <div class="col-12 col-md-8">
                                <label for="name" class="form-label font-semibold">{{ __('branches.name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $branch->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Phone & Email -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label font-semibold">{{ __('branches.phone') }}</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $branch->phone) }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label font-semibold">{{ __('branches.email') }}</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $branch->email) }}">
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-4">
                            <label for="address" class="form-label font-semibold">{{ __('branches.address') }}</label>
                            <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $branch->address) }}">
                        </div>

                        <!-- Main Branch Switch -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_main" value="1" id="is_main" {{ old('is_main', $branch->is_main) ? 'checked' : '' }}>
                                <label class="form-check-label font-semibold" for="is_main">{{ __('branches.is_main') }}</label>
                            </div>
                        </div>

                        <!-- Active Status Switch -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $branch->is_active) ? 'checked' : '' }} {{ $branch->is_main ? 'disabled' : '' }}>
                                <label class="form-check-label font-semibold" for="is_active">{{ __('branches.active') }}</label>
                                @if ($branch->is_main)
                                    <input type="hidden" name="is_active" value="1">
                                    <small class="text-muted d-block me-1">({{ __('branches.cannot_deactivate_main_branch') }})</small>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('branches.index') }}" class="btn btn-light border">إلغاء</a>
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
