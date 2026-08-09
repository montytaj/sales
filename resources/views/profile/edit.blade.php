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
                <i class="bi bi-person-bounding-box text-primary me-2"></i>{{ __('general.profile') ?? 'الملف الشخصي والحساب' }}
            </h2>
        </div>
    </x-slot>

    <!-- Top Profile Hero Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden position-relative">
        <div style="height: 110px; background: linear-gradient(135deg, var(--primary-color, #2563eb) 0%, #0f172a 100%);"></div>

        <div class="card-body p-4 pt-0 position-relative">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end gap-3" style="margin-top: -45px;">
                <div class="position-relative">
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle border border-4 border-white shadow-sm object-fit-cover bg-white" style="width: 100px; height: 100px;" id="headerAvatarPreview">
                    <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle p-1.5" title="{{ __('general.online') ?? 'متصل' }}"></span>
                </div>

                <div class="flex-grow-1 text-center text-md-start mb-1">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
                        <div>
                            <h4 class="font-bold text-slate-800 mb-0 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                                {{ Auth::user()->name }}
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-medium fs-8 px-2.5 py-1">
                                    <i class="bi bi-shield-check me-1"></i>
                                    {{ Auth::user()->roles->first()?->name ?? (app()->getLocale() == 'ar' ? 'مدير النظام' : 'Administrator') }}
                                </span>
                            </h4>
                            <p class="text-slate-500 fs-7 mb-0 mt-0.5">
                                <i class="bi bi-envelope-at me-1 text-slate-400"></i>{{ Auth::user()->email }}
                            </p>
                        </div>

                        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                            <span class="badge bg-slate-100 text-slate-700 border rounded-3 px-3 py-2 font-medium fs-7">
                                <i class="bi bi-calendar3 me-1 text-primary"></i>
                                {{ app()->getLocale() == 'ar' ? 'تاريخ الانضمام:' : 'Member since:' }} {{ Auth::user()->created_at?->format('Y-m-d') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout Grid -->
    <div class="row g-4">
        <!-- Left Sidebar Column: Overview & Security Tips (col-12 col-lg-4) -->
        <div class="col-12 col-lg-4">
            <!-- Account Summary Card -->
            <div class="card card-custom shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h6 class="font-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge text-primary fs-5"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'معلومات الحساب' : 'Account Summary' }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-slate-50 border border-slate-100">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="bi bi-person-fill fs-6"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <small class="text-slate-500 d-block fs-8">{{ app()->getLocale() == 'ar' ? 'الاسم الكامل' : 'Full Name' }}</small>
                                    <span class="font-bold text-slate-800 fs-7 text-truncate d-block">{{ Auth::user()->name }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-slate-50 border border-slate-100">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <div class="rounded-circle bg-emerald-500-20 text-emerald-600 p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="bi bi-envelope-fill fs-6"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <small class="text-slate-500 d-block fs-8">{{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}</small>
                                    <span class="font-bold text-slate-800 fs-7 text-truncate d-block">{{ Auth::user()->email }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-slate-50 border border-slate-100">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-amber-500-20 text-amber-600 p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="bi bi-shield-lock-fill fs-6"></i>
                                </div>
                                <div>
                                    <small class="text-slate-500 d-block fs-8">{{ app()->getLocale() == 'ar' ? 'الدور والرتبة' : 'Role & Rank' }}</small>
                                    <span class="font-bold text-slate-800 fs-7">{{ Auth::user()->roles->first()?->name ?? (app()->getLocale() == 'ar' ? 'مدير النظام' : 'Admin') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tip Box -->
            <div class="card border-0 shadow-sm rounded-4 bg-primary-subtle border-start border-4 border-primary p-4">
                <div class="d-flex gap-3">
                    <div class="fs-3 text-primary">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h6 class="font-bold text-slate-800 mb-1">{{ app()->getLocale() == 'ar' ? 'أمان الحساب والبيانات' : 'Account Security' }}</h6>
                        <p class="text-slate-600 fs-8 mb-0">
                            {{ app()->getLocale() == 'ar' ? 'احرص على استخدام كلمة مرور قوية تحتوي على حروف كبيرة وصغيرة وأرقام، وعدم مشاركتها مع الآخرين.' : 'Always use a strong password with numbers and letters.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Forms Column: Profile Info & Password (col-12 col-lg-8) -->
        <div class="col-12 col-lg-8">
            <!-- 1. Profile Information & Avatar Upload -->
            <div class="card card-custom shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- 2. Security & Password Update -->
            <div class="card card-custom shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
