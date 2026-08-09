<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('settings.title')]
            ];
        @endphp
    </x-slot>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" id="settingsForm">
        @csrf

        <!-- Settings Header Bar -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary p-2.5 rounded-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-sliders2-vertical fs-3"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-bold text-slate-900 fs-5">
                            {{ __('settings.title') }}
                        </h4>
                        <p class="text-muted fs-7 mb-0">
                            {{ app()->getLocale() == 'ar' ? 'تخصيص هوية النظام، الألوان، السياسات المالية، وميزات التشغيل' : 'Customize system branding, colors, financial policies, and feature flags' }}
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary font-bold rounded-3 px-4 py-2 shadow-sm hover-lift d-flex align-items-center gap-2">
                        <i class="bi bi-floppy-fill fs-6"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 4-Card Navigation Grid (Ultra-Clean & Short Titles) -->
        <div class="row row-cols-2 row-cols-md-4 g-2.5 mb-4" id="settingsTabs" role="tablist">
            <!-- 1. Branding & Theme Card -->
            <div class="col" role="presentation">
                <button type="button" class="card border-0 shadow-sm rounded-3 w-100 h-100 text-start transition-all hover-lift p-2.5 px-3 cursor-pointer tab-nav-card active bg-primary text-white" 
                        id="theme-tab" data-bs-toggle="tab" data-bs-target="#theme-tab-pane" role="tab" aria-controls="theme-tab-pane" aria-selected="true">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-palette-fill fs-5 card-icon text-white"></i>
                        <strong class="fs-7 font-bold card-title mb-0 text-truncate">
                            {{ app()->getLocale() == 'ar' ? 'المظهر والثيم' : 'Branding & Theme' }}
                        </strong>
                    </div>
                    <small class="card-desc text-white-50 fs-8 d-block text-truncate">
                        {{ app()->getLocale() == 'ar' ? 'الشعار والألوان' : 'Logo & Colors' }}
                    </small>
                </button>
            </div>

            <!-- 2. General Settings Card -->
            <div class="col" role="presentation">
                <button type="button" class="card border-0 shadow-2xs rounded-3 w-100 h-100 text-start transition-all hover-lift p-2.5 px-3 cursor-pointer tab-nav-card bg-white text-slate-800" 
                        id="general-tab" data-bs-toggle="tab" data-bs-target="#general-tab-pane" role="tab" aria-controls="general-tab-pane" aria-selected="false">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-gear-wide-connected fs-5 card-icon text-amber-500"></i>
                        <strong class="fs-7 font-bold card-title mb-0 text-truncate">
                            {{ app()->getLocale() == 'ar' ? 'الإعدادات العامة' : 'General Settings' }}
                        </strong>
                    </div>
                    <small class="card-desc text-muted fs-8 d-block text-truncate">
                        {{ app()->getLocale() == 'ar' ? 'المنشأة والعملة' : 'Facility & Currency' }}
                    </small>
                </button>
            </div>

            <!-- 3. Financial & Documents Card -->
            <div class="col" role="presentation">
                <button type="button" class="card border-0 shadow-2xs rounded-3 w-100 h-100 text-start transition-all hover-lift p-2.5 px-3 cursor-pointer tab-nav-card bg-white text-slate-800" 
                        id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial-tab-pane" role="tab" aria-controls="financial-tab-pane" aria-selected="false">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-receipt-cutoff fs-5 card-icon text-success"></i>
                        <strong class="fs-7 font-bold card-title mb-0 text-truncate">
                            {{ app()->getLocale() == 'ar' ? 'المالية والبادئات' : 'Financial & Prefixes' }}
                        </strong>
                    </div>
                    <small class="card-desc text-muted fs-8 d-block text-truncate">
                        {{ app()->getLocale() == 'ar' ? 'الضريبة والترميز' : 'Tax & Prefixes' }}
                    </small>
                </button>
            </div>

            <!-- 4. Modules & Features Card -->
            <div class="col" role="presentation">
                <button type="button" class="card border-0 shadow-2xs rounded-3 w-100 h-100 text-start transition-all hover-lift p-2.5 px-3 cursor-pointer tab-nav-card bg-white text-slate-800" 
                        id="features-tab" data-bs-toggle="tab" data-bs-target="#features-tab-pane" role="tab" aria-controls="features-tab-pane" aria-selected="false">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-toggle-on fs-5 card-icon text-info"></i>
                        <strong class="fs-7 font-bold card-title mb-0 text-truncate">
                            {{ app()->getLocale() == 'ar' ? 'الوحدات والميزات' : 'Modules & Features' }}
                        </strong>
                    </div>
                    <small class="card-desc text-muted fs-8 d-block text-truncate">
                        {{ app()->getLocale() == 'ar' ? 'المخازن والمحاسبة' : 'Inventory & Modules' }}
                    </small>
                </button>
            </div>
        </div>

        <!-- Tab Content Area -->
        <div class="tab-content" id="settingsTabContent">
            
            <!-- TAB 1: Theme & Branding -->
            <div class="tab-pane fade show active" id="theme-tab-pane" role="tabpanel" aria-labelledby="theme-tab" tabindex="0">
                
                <!-- CARD 1: SEPARATE DEDICATED LOGO CARD -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="font-bold text-slate-900 mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-card-image text-primary fs-5"></i>
                            <span>{{ app()->getLocale() == 'ar' ? 'شعار الهوية التجارية للنظام' : 'System Logo & Branding' }}</span>
                        </h6>
                        <span class="badge bg-slate-100 text-slate-700 font-semibold px-2.5 py-1 rounded-pill fs-7">
                            {{ app()->getLocale() == 'ar' ? 'شعار المؤسسة' : 'Logo Settings' }}
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row align-items-center g-4">
                            <!-- Left Preview Box -->
                            <div class="col-12 col-md-4 col-lg-3 text-center border-end-md">
                                @php $currentLogo = setting('logo'); @endphp
                                <div class="position-relative mx-auto rounded-4 p-3 bg-slate-50 border border-dashed border-slate-300 d-flex flex-column align-items-center justify-content-center" style="width: 140px; height: 140px;">
                                    @if($currentLogo)
                                        <img src="{{ asset('storage/' . $currentLogo) }}" alt="Logo" id="logoPreview" class="img-fluid max-h-100 object-contain rounded-3 shadow-2xs">
                                        <i class="bi bi-gear-wide-connected fs-1 text-slate-400 d-none" id="logoFallback"></i>
                                    @else
                                        <i class="bi bi-gear-wide-connected fs-1 text-slate-400" id="logoFallback"></i>
                                        <img src="" alt="Logo" id="logoPreview" class="img-fluid max-h-100 object-contain rounded-3 shadow-2xs d-none">
                                    @endif
                                </div>
                                <div class="mt-2">
                                    <span class="badge {{ $currentLogo ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill px-3 py-1 fs-8 font-semibold">
                                        {{ $currentLogo ? (app()->getLocale() == 'ar' ? 'شعار مخصص نشط' : 'Custom Logo Active') : (app()->getLocale() == 'ar' ? 'الشعار الافتراضي' : 'Default System Logo') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Right Upload Controls & Instructions -->
                            <div class="col-12 col-md-8 col-lg-9">
                                <label for="logoInput" class="form-label font-bold text-slate-800 mb-1">
                                    {{ app()->getLocale() == 'ar' ? 'رفع ملف الشعار الجديد' : 'Upload New Logo File' }}
                                </label>
                                <input type="file" name="logo" id="logoInput" class="form-control form-control-lg rounded-3 @error('logo') is-invalid @enderror" accept="image/*" onchange="previewLogo(event)">
                                <div class="form-text fs-7 text-muted mt-2 d-flex align-items-center gap-2">
                                    <i class="bi bi-info-circle-fill text-primary"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'الصيغ المدعومة: PNG, JPG, WEBP, SVG بحجم أقل من 2 ميجابايت' : 'Supported: PNG, JPG, WEBP, SVG Max 2MB' }}</span>
                                </div>
                                @error('logo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                                @if($currentLogo)
                                    <div class="form-check mt-3 p-2.5 bg-danger-subtle border border-danger-subtle rounded-3 d-inline-flex align-items-center gap-2">
                                        <input class="form-check-input ms-0 me-1" type="checkbox" name="remove_logo" value="1" id="removeLogoCheck">
                                        <label class="form-check-label fs-7 text-danger font-bold cursor-pointer mb-0" for="removeLogoCheck">
                                            <i class="bi bi-trash me-1"></i>{{ app()->getLocale() == 'ar' ? 'حذف الشعار المخصص الحالي والعودة للشعار الافتراضي' : 'Remove current logo and restore default' }}
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: DEDICATED COLOR CUSTOMIZER GRID CARD -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="font-bold text-slate-900 mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-sliders text-indigo-600 fs-5"></i>
                            <span>{{ app()->getLocale() == 'ar' ? 'تخصيص ألوان ثيم النظام' : 'Dynamic System Color Palette' }}</span>
                        </h6>
                        <span class="badge bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-1 rounded-pill fs-7">
                            {{ app()->getLocale() == 'ar' ? 'حقول الألوان' : 'Color Fields' }}
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Primary Color -->
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="p-3 bg-slate-50 border rounded-3 hover-shadow transition-all">
                                    <label for="primary_color" class="form-label fs-7 text-slate-700 font-bold mb-1.5 d-flex align-items-center justify-content-between">
                                        <span>{{ app()->getLocale() == 'ar' ? 'اللون الرئيسي' : 'Primary Color' }}</span>
                                        <small class="text-muted font-mono fs-8">Buttons & Active</small>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color p-1 rounded-start-3" id="primaryColorPicker" value="{{ old('primary_color', setting('primary_color', '#2563eb')) }}" title="اختر اللون" oninput="updateColorFromPicker('primaryColorPicker', 'primary_color')">
                                        <input type="text" name="primary_color" id="primary_color" class="form-control font-mono text-uppercase fw-bold rounded-end-3 @error('primary_color') is-invalid @enderror" value="{{ old('primary_color', setting('primary_color', '#2563eb')) }}" placeholder="#2563EB" oninput="updateColorFromText('primary_color', 'primaryColorPicker')">
                                    </div>
                                    @error('primary_color') <div class="invalid-feedback d-block fs-8">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Secondary Color -->
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="p-3 bg-slate-50 border rounded-3 hover-shadow transition-all">
                                    <label for="secondary_color" class="form-label fs-7 text-slate-700 font-bold mb-1.5 d-flex align-items-center justify-content-between">
                                        <span>{{ app()->getLocale() == 'ar' ? 'اللون الثانوي' : 'Secondary Color' }}</span>
                                        <small class="text-muted font-mono fs-8">Navbar & Dark</small>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color p-1 rounded-start-3" id="secondaryColorPicker" value="{{ old('secondary_color', setting('secondary_color', '#0f172a')) }}" title="اختر اللون" oninput="updateColorFromPicker('secondaryColorPicker', 'secondary_color')">
                                        <input type="text" name="secondary_color" id="secondary_color" class="form-control font-mono text-uppercase fw-bold rounded-end-3 @error('secondary_color') is-invalid @enderror" value="{{ old('secondary_color', setting('secondary_color', '#0f172a')) }}" placeholder="#0F172A" oninput="updateColorFromText('secondary_color', 'secondaryColorPicker')">
                                    </div>
                                    @error('secondary_color') <div class="invalid-feedback d-block fs-8">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Accent / Success Color -->
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="p-3 bg-slate-50 border rounded-3 hover-shadow transition-all">
                                    <label for="accent_color" class="form-label fs-7 text-slate-700 font-bold mb-1.5 d-flex align-items-center justify-content-between">
                                        <span>{{ app()->getLocale() == 'ar' ? 'لون التمييز والنجاح' : 'Accent Color' }}</span>
                                        <small class="text-muted font-mono fs-8">Badges & Highlights</small>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color p-1 rounded-start-3" id="accentColorPicker" value="{{ old('accent_color', setting('accent_color', '#10b981')) }}" title="اختر اللون" oninput="updateColorFromPicker('accentColorPicker', 'accent_color')">
                                        <input type="text" name="accent_color" id="accent_color" class="form-control font-mono text-uppercase fw-bold rounded-end-3 @error('accent_color') is-invalid @enderror" value="{{ old('accent_color', setting('accent_color', '#10b981')) }}" placeholder="#10B981" oninput="updateColorFromText('accent_color', 'accentColorPicker')">
                                    </div>
                                    @error('accent_color') <div class="invalid-feedback d-block fs-8">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Sidebar Background -->
                            <div class="col-12 col-md-6 col-lg-6">
                                <div class="p-3 bg-slate-50 border rounded-3 hover-shadow transition-all">
                                    <label for="sidebar_bg" class="form-label fs-7 text-slate-700 font-bold mb-1.5 d-flex align-items-center justify-content-between">
                                        <span>{{ app()->getLocale() == 'ar' ? 'خلفية القائمة الجانبية' : 'Sidebar Background' }}</span>
                                        <small class="text-muted font-mono fs-8">Navigation Container</small>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color p-1 rounded-start-3" id="sidebarBgPicker" value="{{ old('sidebar_bg', setting('sidebar_bg', '#0f172a')) }}" title="اختر اللون" oninput="updateColorFromPicker('sidebarBgPicker', 'sidebar_bg')">
                                        <input type="text" name="sidebar_bg" id="sidebar_bg" class="form-control font-mono text-uppercase fw-bold rounded-end-3 @error('sidebar_bg') is-invalid @enderror" value="{{ old('sidebar_bg', setting('sidebar_bg', '#0f172a')) }}" placeholder="#0F172A" oninput="updateColorFromText('sidebar_bg', 'sidebarBgPicker')">
                                    </div>
                                    @error('sidebar_bg') <div class="invalid-feedback d-block fs-8">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Sidebar Icon Color -->
                            <div class="col-12 col-md-6 col-lg-6">
                                <div class="p-3 bg-slate-50 border rounded-3 hover-shadow transition-all">
                                    <label for="sidebar_icon_color" class="form-label fs-7 text-slate-700 font-bold mb-1.5 d-flex align-items-center justify-content-between">
                                        <span>{{ app()->getLocale() == 'ar' ? 'لون أيقونات القائمة الجانبية' : 'Sidebar Icons Color' }}</span>
                                        <small class="text-muted font-mono fs-8">Menu Icons Accent</small>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-color p-1 rounded-start-3" id="sidebarIconColorPicker" value="{{ old('sidebar_icon_color', setting('sidebar_icon_color', '#3b82f6')) }}" title="اختر اللون" oninput="updateColorFromPicker('sidebarIconColorPicker', 'sidebar_icon_color')">
                                        <input type="text" name="sidebar_icon_color" id="sidebar_icon_color" class="form-control font-mono text-uppercase fw-bold rounded-end-3 @error('sidebar_icon_color') is-invalid @enderror" value="{{ old('sidebar_icon_color', setting('sidebar_icon_color', '#3b82f6')) }}" placeholder="#3B82F6" oninput="updateColorFromText('sidebar_icon_color', 'sidebarIconColorPicker')">
                                    </div>
                                    @error('sidebar_icon_color') <div class="invalid-feedback d-block fs-8">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: FULL-WIDTH LIVE UI PREVIEW & EXPANDED PRESETS CARD (AT THE BOTTOM IN A FULL SINGLE ROW) -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden col-12">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="font-bold text-slate-900 mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-eye-fill text-primary fs-5"></i>
                            <span>{{ app()->getLocale() == 'ar' ? 'معاينة تفاعلية حية للشاشة والثيمات الجاهزة' : 'Live Interactive UI Preview & Presets' }}</span>
                        </h6>
                        <span class="badge bg-primary-subtle text-primary font-semibold px-2.5 py-1 rounded-pill fs-7">
                            {{ app()->getLocale() == 'ar' ? 'معاينة في سطر كامل منفرد' : 'Full Width Live Mockup' }}
                        </span>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Presets Header & Grid -->
                        <div class="mb-4">
                            <div class="fs-6 font-bold text-slate-900 mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-stars text-amber-500 fs-5"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'اختيار ثيم جاهز احترافي بضغطة واحدة' : '1-Click Professional Theme Presets' }}</span>
                            </div>

                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-4 g-3">
                                <!-- 1. Sapphire Corporate -->
                                <div class="col">
                                    <button type="button" class="btn btn-outline-light text-slate-800 border w-100 p-3 rounded-4 hover-lift text-start d-flex flex-column gap-2 shadow-2xs" 
                                            onclick="applyPreset('#2563eb', '#0f172a', '#10b981', '#0f172a', '#3b82f6')">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <strong class="fs-7 font-bold text-slate-900">{{ app()->getLocale() == 'ar' ? 'الأزرق الياقوتي' : 'Sapphire Corporate' }}</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #2563eb;" title="Primary"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #0f172a;" title="Secondary"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #10b981;" title="Accent"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #0f172a;" title="Sidebar BG"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #3b82f6;" title="Sidebar Icon"></span>
                                        </div>
                                    </button>
                                </div>

                                <!-- 2. Emerald Classic -->
                                <div class="col">
                                    <button type="button" class="btn btn-outline-light text-slate-800 border w-100 p-3 rounded-4 hover-lift text-start d-flex flex-column gap-2 shadow-2xs" 
                                            onclick="applyPreset('#059669', '#111827', '#f59e0b', '#111827', '#10b981')">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <strong class="fs-7 font-bold text-slate-900">{{ app()->getLocale() == 'ar' ? 'الزمرد الكلاسيكي' : 'Emerald Classic' }}</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #059669;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #111827;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #f59e0b;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #111827;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #10b981;"></span>
                                        </div>
                                    </button>
                                </div>

                                <!-- 3. Royal Purple -->
                                <div class="col">
                                    <button type="button" class="btn btn-outline-light text-slate-800 border w-100 p-3 rounded-4 hover-lift text-start d-flex flex-column gap-2 shadow-2xs" 
                                            onclick="applyPreset('#7c3aed', '#18181b', '#06b6d4', '#18181b', '#c084fc')">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <strong class="fs-7 font-bold text-slate-900">{{ app()->getLocale() == 'ar' ? 'البنفسجي الملكي' : 'Royal Purple' }}</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #7c3aed;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #18181b;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #06b6d4;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #18181b;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #c084fc;"></span>
                                        </div>
                                    </button>
                                </div>

                                <!-- 4. Sunset Warmth -->
                                <div class="col">
                                    <button type="button" class="btn btn-outline-light text-slate-800 border w-100 p-3 rounded-4 hover-lift text-start d-flex flex-column gap-2 shadow-2xs" 
                                            onclick="applyPreset('#ea580c', '#1c1917', '#10b981', '#1c1917', '#fb923c')">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <strong class="fs-7 font-bold text-slate-900">{{ app()->getLocale() == 'ar' ? 'الغروب الدافئ' : 'Sunset Warmth' }}</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #ea580c;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #1c1917;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #10b981;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #1c1917;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #fb923c;"></span>
                                        </div>
                                    </button>
                                </div>

                                <!-- 5. Ocean Coal -->
                                <div class="col">
                                    <button type="button" class="btn btn-outline-light text-slate-800 border w-100 p-3 rounded-4 hover-lift text-start d-flex flex-column gap-2 shadow-2xs" 
                                            onclick="applyPreset('#0284c7', '#0f172a', '#14b8a6', '#090d16', '#38bdf8')">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <strong class="fs-7 font-bold text-slate-900">{{ app()->getLocale() == 'ar' ? 'الفحم والمحيط' : 'Ocean Coal' }}</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #0284c7;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #0f172a;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #14b8a6;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #090d16;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #38bdf8;"></span>
                                        </div>
                                    </button>
                                </div>

                                <!-- 6. Modern Lavender -->
                                <div class="col">
                                    <button type="button" class="btn btn-outline-light text-slate-800 border w-100 p-3 rounded-4 hover-lift text-start d-flex flex-column gap-2 shadow-2xs" 
                                            onclick="applyPreset('#9333ea', '#1e1b4b', '#f43f5e', '#1e1b4b', '#a855f7')">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <strong class="fs-7 font-bold text-slate-900">{{ app()->getLocale() == 'ar' ? 'اللافندر الحديث' : 'Modern Lavender' }}</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #9333ea;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #1e1b4b;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #f43f5e;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #1e1b4b;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #a855f7;"></span>
                                        </div>
                                    </button>
                                </div>

                                <!-- 7. Teal Mint -->
                                <div class="col">
                                    <button type="button" class="btn btn-outline-light text-slate-800 border w-100 p-3 rounded-4 hover-lift text-start d-flex flex-column gap-2 shadow-2xs" 
                                            onclick="applyPreset('#0d9488', '#0f172a', '#84cc16', '#0f172a', '#2dd4bf')">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <strong class="fs-7 font-bold text-slate-900">{{ app()->getLocale() == 'ar' ? 'النعناع الهادئ' : 'Teal Mint' }}</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #0d9488;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #0f172a;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #84cc16;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #0f172a;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #2dd4bf;"></span>
                                        </div>
                                    </button>
                                </div>

                                <!-- 8. Crimson Velvet -->
                                <div class="col">
                                    <button type="button" class="btn btn-outline-light text-slate-800 border w-100 p-3 rounded-4 hover-lift text-start d-flex flex-column gap-2 shadow-2xs" 
                                            onclick="applyPreset('#dc2626', '#18181b', '#eab308', '#18181b', '#f87171')">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <strong class="fs-7 font-bold text-slate-900">{{ app()->getLocale() == 'ar' ? 'الروبي الفاخر' : 'Crimson Velvet' }}</strong>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #dc2626;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #18181b;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #eab308;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #18181b;"></span>
                                            <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #f87171;"></span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Full Width Live Interactive Screen Mockup -->
                        <div class="p-3 bg-slate-100 rounded-4 border">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-slate-200">
                                <span class="font-bold text-slate-800 fs-7 d-flex align-items-center gap-2">
                                    <i class="bi bi-display-fill text-primary"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'معاينة الواجهة التفاعلية الحية مع الألوان المحددة' : 'Full Width Real-Time Screen Preview' }}</span>
                                </span>
                                <span class="badge bg-white text-slate-700 border fs-8">Real-time Live Sync</span>
                            </div>

                            <!-- Mockup Container -->
                            <div class="preview-mockup-screen rounded-3 overflow-hidden border shadow-sm bg-white d-flex flex-column" style="min-height: 280px;">
                                <!-- Navbar Mockup -->
                                <div class="p-2.5 border-bottom d-flex align-items-center justify-content-between px-3 text-white transition-all" id="prevNavbar" style="background: {{ setting('secondary_color', '#0f172a') }};">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-list fs-5"></i>
                                        <span class="font-bold fs-7">{{ setting('facility_name', app()->getLocale() == 'ar' ? 'إدارة المنظومة' : 'ERP System') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-pill fs-8 px-2.5 py-1" id="prevBadge" style="background: {{ setting('accent_color', '#10b981') }};">
                                            {{ app()->getLocale() == 'ar' ? 'النظام نشط' : 'System Active' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Body Mockup: Sidebar + Content -->
                                <div class="d-flex flex-grow-1 overflow-hidden" style="min-height: 220px;">
                                    <!-- Sidebar Mockup -->
                                    <div class="p-2.5 d-flex flex-column gap-3 border-end transition-all" id="prevSidebar" style="width: 140px; background: {{ setting('sidebar_bg', '#0f172a') }};">
                                        <div class="p-2 rounded d-flex align-items-center gap-2 bg-white-10" id="prevSidebarIcon1">
                                            <i class="bi bi-speedometer2 fs-6" id="prevIconColor1" style="color: {{ setting('sidebar_icon_color', '#3b82f6') }};"></i>
                                            <small class="text-white fs-8 font-bold">{{ app()->getLocale() == 'ar' ? 'الرئيسية' : 'Dashboard' }}</small>
                                        </div>
                                        <div class="p-2 rounded d-flex align-items-center gap-2" id="prevSidebarIcon2">
                                            <i class="bi bi-cart3 fs-6" id="prevIconColor2" style="color: {{ setting('sidebar_icon_color', '#3b82f6') }};"></i>
                                            <small class="text-slate-300 fs-8">{{ app()->getLocale() == 'ar' ? 'المبيعات' : 'Sales' }}</small>
                                        </div>
                                        <div class="p-2 rounded d-flex align-items-center gap-2" id="prevSidebarIcon3">
                                            <i class="bi bi-box-seam fs-6" id="prevIconColor3" style="color: {{ setting('sidebar_icon_color', '#3b82f6') }};"></i>
                                            <small class="text-slate-300 fs-8">{{ app()->getLocale() == 'ar' ? 'المخزن' : 'Inventory' }}</small>
                                        </div>
                                    </div>

                                    <!-- Content Area Mockup -->
                                    <div class="p-4 bg-slate-50 flex-grow-1 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div>
                                                    <h6 class="font-bold text-slate-900 mb-1 fs-7">{{ app()->getLocale() == 'ar' ? 'معاينة كروت الإحصائيات والأزرار' : 'Dashboard Cards & Buttons Preview' }}</h6>
                                                    <small class="text-muted fs-8">{{ app()->getLocale() == 'ar' ? 'هكذا ستبدو عناصر الشاشة عند اعتماد هذه الألوان' : 'How UI components render with selected palette' }}</small>
                                                </div>

                                                <button type="button" class="btn btn-sm text-white font-bold px-3 py-1.5 rounded-3 shadow-2xs transition-all" id="prevPrimaryBtn" style="background: {{ setting('primary_color', '#2563eb') }};">
                                                    <i class="bi bi-plus-circle me-1"></i>{{ app()->getLocale() == 'ar' ? 'إجراء جديد' : 'New Action' }}
                                                </button>
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <div class="p-2.5 bg-white rounded-3 border">
                                                        <small class="text-muted fs-8 d-block mb-1">{{ app()->getLocale() == 'ar' ? 'إجمالي المبيعات' : 'Total Sales' }}</small>
                                                        <strong class="font-mono text-slate-900 fs-7">12,450.00 {{ setting('currency', 'SDG') }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-2.5 bg-white rounded-3 border">
                                                        <small class="text-muted fs-8 d-block mb-1">{{ app()->getLocale() == 'ar' ? 'حالة الطلبات' : 'Orders Status' }}</small>
                                                        <span class="badge fs-8 text-white px-2 py-1" id="prevAccentBadge" style="background: {{ setting('accent_color', '#10b981') }};">
                                                            {{ app()->getLocale() == 'ar' ? 'مكتملة' : 'Completed' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- TAB 2: General Settings -->
            <div class="tab-pane fade" id="general-tab-pane" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-bold text-slate-900 mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-sliders text-amber-500 fs-5"></i>
                            <span>{{ __('settings.general_settings') }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Facility Name -->
                            <div class="col-12 col-md-6">
                                <label for="facility_name" class="form-label font-bold text-slate-800">{{ __('settings.facility_name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-500"><i class="bi bi-building"></i></span>
                                    <input type="text" name="facility_name" id="facility_name" class="form-control @error('facility_name') is-invalid @enderror" value="{{ old('facility_name', setting('facility_name', 'مؤسسة أثاث وديكور وورش CNC')) }}" required>
                                </div>
                                @error('facility_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <!-- Currency -->
                            <div class="col-12 col-md-6">
                                <label for="currency" class="form-label font-bold text-slate-800">{{ __('settings.currency') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-slate-500"><i class="bi bi-cash-coin"></i></span>
                                    <input type="text" name="currency" id="currency" class="form-control font-mono font-bold @error('currency') is-invalid @enderror" value="{{ old('currency', setting('currency', 'SDG')) }}" required>
                                </div>
                                <div class="form-text fs-7 text-muted">{{ app()->getLocale() == 'ar' ? 'رمز العملة الأساسية الافتراضية للنظام' : 'Default base currency code' }}</div>
                                @error('currency') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <!-- Timezone -->
                            <div class="col-12 col-md-6">
                                <label for="timezone" class="form-label font-bold text-slate-800">{{ __('settings.timezone') }} <span class="text-danger">*</span></label>
                                <select name="timezone" id="timezone" class="form-select @error('timezone') is-invalid @enderror" required>
                                    <option value="Africa/Khartoum" {{ setting('timezone', 'Africa/Khartoum') == 'Africa/Khartoum' ? 'selected' : '' }}>Africa/Khartoum</option>
                                    <option value="Asia/Riyadh" {{ setting('timezone') == 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh</option>
                                    <option value="Asia/Dubai" {{ setting('timezone') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                                    <option value="UTC" {{ setting('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                </select>
                            </div>

                            <!-- Default Locale -->
                            <div class="col-12 col-md-6">
                                <label for="default_locale" class="form-label font-bold text-slate-800">{{ __('settings.default_locale') }} <span class="text-danger">*</span></label>
                                <select name="default_locale" id="default_locale" class="form-select @error('default_locale') is-invalid @enderror" required>
                                    <option value="ar" {{ setting('default_locale', 'ar') == 'ar' ? 'selected' : '' }}>العربية</option>
                                    <option value="en" {{ setting('default_locale') == 'en' ? 'selected' : '' }}>English</option>
                                </select>
                            </div>

                            <!-- Sales System Mode -->
                            <div class="col-12 col-md-12">
                                <div class="p-3 bg-slate-50 rounded-4 border">
                                    <label for="sales_system_mode" class="form-label font-bold text-slate-800 fs-6">
                                        <i class="bi bi-display text-primary me-1.5"></i>{{ app()->getLocale() == 'ar' ? 'نمط نظام واجهة المبيعات الرئيسي' : 'Main Sales Mode' }}
                                    </label>
                                    <select name="sales_system_mode" id="sales_system_mode" class="form-select form-select-lg rounded-3 fs-6 @error('sales_system_mode') is-invalid @enderror">
                                        <option value="standard" {{ setting('sales_system_mode', 'standard') == 'standard' ? 'selected' : '' }}>
                                            {{ app()->getLocale() == 'ar' ? 'نظام الفواتير النمطية التقليدية' : 'Standard Sales Invoices' }}
                                        </option>
                                        <option value="pos" {{ setting('sales_system_mode') == 'pos' ? 'selected' : '' }}>
                                            {{ app()->getLocale() == 'ar' ? 'نظام الكاشير ونقطة البيع اللمسية السريعة' : 'POS Touch-Screen Cashier' }}
                                        </option>
                                    </select>
                                    <div class="form-text fs-7 text-muted mt-2">
                                        {{ app()->getLocale() == 'ar' ? 'نمط الكاشير يعرض المنتجات كشبكة بصريات مع أزرار إضافة سريعة وسلة شراء فورية.' : 'POS mode presents items in an interactive visual grid for instant checkout.' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Financial & Documents -->
            <div class="tab-pane fade" id="financial-tab-pane" role="tabpanel" aria-labelledby="financial-tab" tabindex="0">
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-bold text-slate-900 mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-receipt-cutoff text-success fs-5"></i>
                            <span>{{ __('settings.financial_settings') }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <!-- Rates & Formats -->
                        <div class="row g-4 mb-4">
                            <!-- VAT Percentage -->
                            <div class="col-12 col-md-4">
                                <label for="tax_percentage" class="form-label font-bold text-slate-800">{{ __('settings.tax_percentage') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="tax_percentage" id="tax_percentage" class="form-control font-mono font-bold" value="{{ old('tax_percentage', setting('tax_percentage', 15.00)) }}" required>
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                                <div class="form-text fs-7 text-muted">{{ app()->getLocale() == 'ar' ? 'نسبة ضريبة القيمة المضافة التي تطبق على الفواتير' : 'VAT percentage applied to invoices' }}</div>
                            </div>

                            <!-- Number Format / Decimal Places -->
                            <div class="col-12 col-md-4">
                                <label for="number_format" class="form-label font-bold text-slate-800">{{ __('settings.number_format') }} <span class="text-danger">*</span></label>
                                <select name="number_format" id="number_format" class="form-select font-mono" required>
                                    <option value="2" {{ setting('number_format', 2) == 2 ? 'selected' : '' }}>2 (100.00)</option>
                                    <option value="3" {{ setting('number_format') == 3 ? 'selected' : '' }}>3 (100.000)</option>
                                </select>
                            </div>

                            <!-- Min Downpayment Percentage -->
                            <div class="col-12 col-md-4">
                                <label for="min_downpayment_percentage" class="form-label font-bold text-slate-800">{{ __('settings.min_downpayment_percentage') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="min_downpayment_percentage" id="min_downpayment_percentage" class="form-control font-mono font-bold" value="{{ old('min_downpayment_percentage', setting('min_downpayment_percentage', 50.00)) }}" required>
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Document Prefixes & Live Preview Badges -->
                        <div class="p-3 bg-slate-50 rounded-4 border mb-4">
                            <h6 class="font-bold text-slate-800 fs-7 text-uppercase tracking-wider mb-3">
                                <i class="bi bi-hash text-primary me-1.5"></i>{{ app()->getLocale() == 'ar' ? 'ترميز وبادئات المستندات والسلع' : 'Document Prefixes' }}
                            </h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label for="doc_prefix_quotation" class="form-label font-bold text-slate-700 fs-7">{{ __('settings.doc_prefix_quotation') }}</label>
                                    <input type="text" name="doc_prefix_quotation" id="doc_prefix_quotation" class="form-control font-mono font-bold" value="{{ old('doc_prefix_quotation', setting('doc_prefix_quotation', 'OFFER-')) }}" required oninput="updatePrefixPreview()">
                                    <small class="text-muted fs-8 d-block mt-1">{{ app()->getLocale() == 'ar' ? 'مثال:' : 'Sample:' }} <span class="badge bg-white text-primary border font-mono" id="prefixQuotationPrev">OFFER-2026-00001</span></small>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="doc_prefix_invoice" class="form-label font-bold text-slate-700 fs-7">{{ __('settings.doc_prefix_invoice') }}</label>
                                    <input type="text" name="doc_prefix_invoice" id="doc_prefix_invoice" class="form-control font-mono font-bold" value="{{ old('doc_prefix_invoice', setting('doc_prefix_invoice', 'INV-')) }}" required oninput="updatePrefixPreview()">
                                    <small class="text-muted fs-8 d-block mt-1">{{ app()->getLocale() == 'ar' ? 'مثال:' : 'Sample:' }} <span class="badge bg-white text-success border font-mono" id="prefixInvoicePrev">INV-2026-00001</span></small>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="doc_prefix_work_order" class="form-label font-bold text-slate-700 fs-7">{{ __('settings.doc_prefix_work_order') }}</label>
                                    <input type="text" name="doc_prefix_work_order" id="doc_prefix_work_order" class="form-control font-mono font-bold" value="{{ old('doc_prefix_work_order', setting('doc_prefix_work_order', 'JOB-')) }}" required oninput="updatePrefixPreview()">
                                    <small class="text-muted fs-8 d-block mt-1">{{ app()->getLocale() == 'ar' ? 'مثال:' : 'Sample:' }} <span class="badge bg-white text-info border font-mono" id="prefixJobPrev">JOB-2026-00001</span></small>
                                </div>
                            </div>
                        </div>

                        <!-- Policy Switches -->
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-check form-switch p-3 bg-light rounded-3 border h-100 d-flex align-items-center justify-content-between">
                                    <div>
                                        <label class="form-check-label font-bold text-slate-800 mb-0 cursor-pointer" for="allow_negative_inventory">
                                            {{ __('settings.allow_negative_inventory') }}
                                        </label>
                                        <small class="text-danger d-block fs-8 font-bold"><i class="bi bi-shield-lock me-1"></i>ممنوع نهائياً وحظر دائم لمنع البيع بدون رصيد</small>
                                    </div>
                                    <input class="form-check-input ms-3 fs-5" type="checkbox" name="allow_negative_inventory" value="0" id="allow_negative_inventory" disabled>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-check form-switch p-3 bg-light rounded-3 border h-100 d-flex align-items-center justify-content-between">
                                    <div>
                                        <label class="form-check-label font-bold text-slate-800 mb-0 cursor-pointer" for="allow_delivery_with_balance">
                                            {{ __('settings.allow_delivery_with_balance') }}
                                        </label>
                                        <small class="text-muted d-block fs-8">{{ app()->getLocale() == 'ar' ? 'السماح لتسليم أوامر العمل قبل السداد الكامل' : 'Allow delivery before full settlement' }}</small>
                                    </div>
                                    <input class="form-check-input ms-3 fs-5" type="checkbox" name="allow_delivery_with_balance" value="1" id="allow_delivery_with_balance" {{ setting('allow_delivery_with_balance', false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Feature Flags & Modules -->
            <div class="tab-pane fade" id="features-tab-pane" role="tabpanel" aria-labelledby="features-tab" tabindex="0">
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-bold text-slate-900 mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-toggle-on text-cyan-500 fs-5"></i>
                            <span>{{ __('settings.feature_flags') }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Inventory Module -->
                            <div class="col-12 col-md-6">
                                <div class="p-3.5 bg-slate-50 border rounded-4 hover-shadow transition-all d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2.5 rounded-3 bg-primary-subtle text-primary">
                                            <i class="bi bi-box-seam fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-bold text-slate-900 fs-7">{{ __('settings.inventory_enabled') }}</h6>
                                            <small class="text-muted fs-8">{{ app()->getLocale() == 'ar' ? 'تفعيل إدارة المخازن والأصناف والمستودعات' : 'Enable Inventory & Warehouses' }}</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input fs-4" type="checkbox" name="inventory_enabled" value="1" id="inventory_enabled" {{ feature_enabled('inventory_enabled') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <!-- Accounting Module -->
                            <div class="col-12 col-md-6">
                                <div class="p-3.5 bg-slate-50 border rounded-4 hover-shadow transition-all d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2.5 rounded-3 bg-success-subtle text-success">
                                            <i class="bi bi-diagram-3 fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-bold text-slate-900 fs-7">{{ __('settings.accounting_enabled') }}</h6>
                                            <small class="text-muted fs-8">{{ app()->getLocale() == 'ar' ? 'تفعيل شجرة الحسابات والقيود اليومية الآلية' : 'Enable Accounting & Journal Entries' }}</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input fs-4" type="checkbox" name="accounting_enabled" value="1" id="accounting_enabled" {{ feature_enabled('accounting_enabled') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <!-- Cheques Module -->
                            <div class="col-12 col-md-6">
                                <div class="p-3.5 bg-slate-50 border rounded-4 hover-shadow transition-all d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2.5 rounded-3 bg-warning-subtle text-warning">
                                            <i class="bi bi-credit-card fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-bold text-slate-900 fs-7">{{ __('settings.cheques_enabled') }}</h6>
                                            <small class="text-muted fs-8">{{ app()->getLocale() == 'ar' ? 'تفعيل حافظة الشيكات الصادرة والواردة' : 'Enable Cheques Management' }}</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input fs-4" type="checkbox" name="cheques_enabled" value="1" id="cheques_enabled" {{ feature_enabled('cheques_enabled') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <!-- Projects Module -->
                            <div class="col-12 col-md-6">
                                <div class="p-3.5 bg-slate-50 border rounded-4 hover-shadow transition-all d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2.5 rounded-3 bg-info-subtle text-info">
                                            <i class="bi bi-folder-check fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-bold text-slate-900 fs-7">{{ __('settings.projects_enabled') }}</h6>
                                            <small class="text-muted fs-8">{{ app()->getLocale() == 'ar' ? 'تفعيل إدارة العقود والمشاريع والتوريدات' : 'Enable Contracts & Projects' }}</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input fs-4" type="checkbox" name="projects_enabled" value="1" id="projects_enabled" {{ feature_enabled('projects_enabled') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <!-- Signage Module -->
                            <div class="col-12 col-md-6">
                                <div class="p-3.5 bg-slate-50 border rounded-4 hover-shadow transition-all d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2.5 rounded-3 bg-purple-subtle text-purple-600">
                                            <i class="bi bi-signpost-split fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-bold text-slate-900 fs-7">{{ __('settings.signage_enabled') }}</h6>
                                            <small class="text-muted fs-8">{{ app()->getLocale() == 'ar' ? 'تفعيل وحدة الدعاوي واللوحات الإعلانية' : 'Enable Signage Orders' }}</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input fs-4" type="checkbox" name="signage_enabled" value="1" id="signage_enabled" {{ feature_enabled('signage_enabled') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <!-- JavaScript Interactions for Settings Page -->
    <script>
        // Logo preview handler
        function previewLogo(event) {
            const input = event.target;
            const preview = document.getElementById('logoPreview');
            const fallback = document.getElementById('logoFallback');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    if (fallback) fallback.classList.add('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Synchronize Color Pickers and Text Inputs
        function updateColorFromPicker(pickerId, textId) {
            const val = document.getElementById(pickerId).value;
            document.getElementById(textId).value = val.toUpperCase();
            triggerLiveMockupUpdate();
        }

        function updateColorFromText(textId, pickerId) {
            let val = document.getElementById(textId).value.trim();
            if (val && !val.startsWith('#')) val = '#' + val;
            if (/^#(?:[0-9a-fA-F]{3}){1,2}$/.test(val)) {
                document.getElementById(pickerId).value = val;
                triggerLiveMockupUpdate();
            }
        }

        // Apply Preset Palettes
        function applyPreset(primary, secondary, accent, sidebarBg, sidebarIcon) {
            document.getElementById('primary_color').value = primary;
            document.getElementById('primaryColorPicker').value = primary;

            document.getElementById('secondary_color').value = secondary;
            document.getElementById('secondaryColorPicker').value = secondary;

            document.getElementById('accent_color').value = accent;
            document.getElementById('accentColorPicker').value = accent;

            document.getElementById('sidebar_bg').value = sidebarBg;
            document.getElementById('sidebarBgPicker').value = sidebarBg;

            document.getElementById('sidebar_icon_color').value = sidebarIcon;
            document.getElementById('sidebarIconColorPicker').value = sidebarIcon;

            triggerLiveMockupUpdate();
        }

        // Live Mockup Screen Updates
        function triggerLiveMockupUpdate() {
            const primary = document.getElementById('primary_color').value || '#2563eb';
            const secondary = document.getElementById('secondary_color').value || '#0f172a';
            const accent = document.getElementById('accent_color').value || '#10b981';
            const sidebarBg = document.getElementById('sidebar_bg').value || '#0f172a';
            const sidebarIcon = document.getElementById('sidebar_icon_color').value || '#3b82f6';

            document.getElementById('prevNavbar').style.background = secondary;
            document.getElementById('prevBadge').style.background = accent;
            document.getElementById('prevAccentBadge').style.background = accent;
            document.getElementById('prevSidebar').style.background = sidebarBg;

            document.getElementById('prevIconColor1').style.color = sidebarIcon;
            document.getElementById('prevIconColor2').style.color = sidebarIcon;
            document.getElementById('prevIconColor3').style.color = sidebarIcon;

            document.getElementById('prevPrimaryBtn').style.background = primary;
        }

        // Update Document Prefix Previews Live
        function updatePrefixPreview() {
            const q = document.getElementById('doc_prefix_quotation').value || 'OFFER-';
            const i = document.getElementById('doc_prefix_invoice').value || 'INV-';
            const j = document.getElementById('doc_prefix_work_order').value || 'JOB-';

            document.getElementById('prefixQuotationPrev').innerText = q + '2026-00001';
            document.getElementById('prefixInvoicePrev').innerText = i + '2026-00001';
            document.getElementById('prefixJobPrev').innerText = j + '2026-00001';
        }

        // Tab Persistence via Hash
        document.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash;
            if (hash) {
                const targetBtn = document.querySelector(`button[data-bs-target="${hash}"]`);
                if (targetBtn) {
                    const tab = new bootstrap.Tab(targetBtn);
                    tab.show();
                }
            }

            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
                btn.addEventListener('shown.bs.tab', (e) => {
                    const target = e.target.getAttribute('data-bs-target');
                    if (target) history.replaceState(null, null, target);

                    // Reset all cards to inactive style
                    document.querySelectorAll('.tab-nav-card').forEach(card => {
                        card.classList.remove('active', 'bg-primary', 'text-white', 'shadow-sm');
                        card.classList.add('bg-white', 'text-slate-800', 'shadow-2xs');
                        
                        const desc = card.querySelector('.card-desc');
                        if (desc) desc.className = 'card-desc text-muted fs-8 d-block text-truncate';

                        const icon = card.querySelector('.card-icon');
                        if (icon) {
                            if (card.id === 'theme-tab') icon.className = 'bi bi-palette-fill fs-5 card-icon text-primary';
                            if (card.id === 'general-tab') icon.className = 'bi bi-gear-wide-connected fs-5 card-icon text-amber-500';
                            if (card.id === 'financial-tab') icon.className = 'bi bi-receipt-cutoff fs-5 card-icon text-success';
                            if (card.id === 'features-tab') icon.className = 'bi bi-toggle-on fs-5 card-icon text-info';
                        }
                    });

                    // Set selected active card style
                    const activeCard = e.target.closest('.tab-nav-card');
                    if (activeCard) {
                        activeCard.classList.remove('bg-white', 'text-slate-800', 'shadow-2xs');
                        activeCard.classList.add('active', 'bg-primary', 'text-white', 'shadow-sm');

                        const desc = activeCard.querySelector('.card-desc');
                        if (desc) desc.className = 'card-desc text-white-50 fs-8 d-block text-truncate';

                        const icon = activeCard.querySelector('.card-icon');
                        if (icon) icon.className = 'bi ' + icon.classList[1] + ' fs-5 card-icon text-white';
                    }
                });
            });

            triggerLiveMockupUpdate();
            updatePrefixPreview();
        });
    </script>
</x-app-layout>
