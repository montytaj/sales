<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('general.profile') ?? 'الملف الشخصي']
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h4 mb-0 font-bold text-slate-800">
                <i class="bi bi-person-circle text-primary me-2"></i>{{ __('general.profile') ?? 'الملف الشخصي والحساب' }}
            </h2>
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- 1. Profile Information & Avatar Upload -->
        <div class="col-12 col-lg-7">
            <div class="card card-custom shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card card-custom shadow-sm border-0">
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <!-- 2. Danger Zone (Delete Account) & User Overview Card -->
        <div class="col-12 col-lg-5">
            <div class="card card-custom shadow-sm border-0 mb-4 text-center p-4">
                <div class="d-flex flex-column align-items-center">
                    <div class="position-relative mb-3">
                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle shadow border object-fit-cover" style="width: 110px; height: 110px;">
                    </div>
                    <h5 class="font-bold text-slate-800 mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted fs-7 mb-2">{{ Auth::user()->email }}</p>
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-medium fs-7 px-3 py-1">
                            <i class="bi bi-shield-lock me-1"></i>
                            {{ Auth::user()->roles->first()?->name ?? __('general.administrator') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card card-custom shadow-sm border-0 border-top border-danger border-3">
                <div class="card-body p-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
