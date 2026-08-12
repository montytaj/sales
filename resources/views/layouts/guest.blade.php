<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $sysLogo = setting('logo');
        $facilityName = setting('facility_name', config('app.name', 'Workshop ERP'));
        $hasLogo = $sysLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($sysLogo);
        $primaryColor = setting('primary_color', '#2563eb');
        $secondaryColor = setting('secondary_color', '#0f172a');
    @endphp

    <title>{{ $facilityName }} - {{ __('general.login') }}</title>

    <!-- Google Fonts Cairo & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS (RTL / LTR) -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @endif

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts & Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand-primary: {{ $primaryColor }};
            --brand-secondary: {{ $secondaryColor }};
            --font-main: 'Cairo', 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-main) !important;
            background-color: #0b1329;
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Ambient Animated Blurs */
        .login-bg-wrapper {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
            background: radial-gradient(circle at 20% 20%, #1e293b 0%, #0f172a 60%, #080d1a 100%);
        }

        .ambient-node {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
            will-change: transform;
        }

        .ambient-node-1 {
            width: 500px;
            height: 500px;
            top: -100px;
            right: -100px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.6) 0%, rgba(15, 23, 42, 0) 70%);
            animation: floatNode1 18s ease-in-out infinite alternate;
        }

        .ambient-node-2 {
            width: 450px;
            height: 450px;
            bottom: -80px;
            left: -80px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.5) 0%, rgba(15, 23, 42, 0) 70%);
            animation: floatNode2 22s ease-in-out infinite alternate;
        }

        @keyframes floatNode1 {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(-40px, 50px, 0) scale(1.1); }
            100% { transform: translate3d(30px, -20px, 0) scale(0.95); }
        }

        @keyframes floatNode2 {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(50px, -40px, 0) scale(1.12); }
            100% { transform: translate3d(-20px, 30px, 0) scale(0.9); }
        }

        /* Entry Keyframe Animations */
        .anim-fade-up {
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 24px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        /* Glass Cards & Containers */
        .login-card-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1100px;
            margin: auto;
            border-radius: 28px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.15);
        }

        .hero-side-panel {
            background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 50%, #090d16 100%);
            color: #ffffff;
            position: relative;
            overflow: hidden;
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hero-side-panel::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.18), transparent 60%);
            pointer-events: none;
        }

        /* Platform Large Logo Styling */
        .platform-logo-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
        }

        .platform-logo-container:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
        }

        .platform-logo-img {
            max-height: 100px;
            width: auto;
            max-width: 320px;
            object-fit: contain;
            filter: drop-shadow(0 4px 14px rgba(0, 0, 0, 0.3));
        }

        .platform-logo-emblem {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--brand-primary), #4f46e5);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }

        /* Feature Pills */
        .feature-glass-pill {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.09);
            backdrop-filter: blur(8px);
            border-radius: 16px;
            padding: 1rem 1.25rem;
            transition: all 0.25s ease;
        }

        .feature-glass-pill:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(99, 102, 241, 0.4);
            transform: translateY(-3px);
        }

        .feature-icon-badge {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.2);
            color: #60a5fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        /* Form Side Panel */
        .form-side-panel {
            padding: 3.5rem 3rem;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        @media (max-width: 991.98px) {
            .hero-side-panel, .form-side-panel {
                padding: 2.25rem 1.75rem;
            }
        }

        /* Input Controls Polish */
        .custom-input-group .input-group-text {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #64748b;
            font-size: 1.1rem;
            padding: 0.75rem 1rem;
            border-radius: 14px;
        }

        [dir="rtl"] .custom-input-group .input-group-text:first-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
        }
        [dir="rtl"] .custom-input-group .form-control:last-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-top-left-radius: 14px;
            border-bottom-left-radius: 14px;
        }

        [dir="ltr"] .custom-input-group .input-group-text:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-top-left-radius: 14px;
            border-bottom-left-radius: 14px;
        }
        [dir="ltr"] .custom-input-group .form-control:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
        }

        .custom-input-control {
            border-color: #e2e8f0;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            border-radius: 14px;
            transition: all 0.2s ease;
        }

        .custom-input-control:focus {
            border-color: var(--brand-primary) !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12) !important;
            background-color: #ffffff;
        }

        .custom-input-group:focus-within .input-group-text {
            border-color: var(--brand-primary) !important;
            color: var(--brand-primary) !important;
            background-color: #eff6ff !important;
        }

        /* Password Toggle Button */
        .btn-toggle-password {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-toggle-password:hover {
            color: var(--brand-primary);
            background-color: #f1f5f9;
        }

        /* Submit Button */
        .btn-login-submit {
            background: linear-gradient(135deg, var(--brand-primary) 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            font-size: 1.05rem;
            padding: 0.85rem 1.5rem;
            border-radius: 14px;
            box-shadow: 0 8px 20px -3px rgba(37, 99, 235, 0.4);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -4px rgba(37, 99, 235, 0.5);
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            color: #ffffff;
        }

        .btn-login-submit:active {
            transform: translateY(0);
        }

        /* Language Switcher Pills */
        .lang-switch-btn {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            font-size: 0.825rem;
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .lang-switch-btn:hover {
            background-color: var(--brand-primary);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }
    </style>
</head>
<body class="min-vh-100 d-flex align-items-center justify-content-center p-3 p-md-4 position-relative">

    <!-- Background Ambient Animated Layer -->
    <div class="login-bg-wrapper">
        <div class="ambient-node ambient-node-1"></div>
        <div class="ambient-node ambient-node-2"></div>
    </div>

    <!-- Main Card Container -->
    <div class="login-card-wrapper anim-fade-up">
        <div class="row g-0">
            
            <!-- Hero Side Panel (Left in LTR / Right in RTL) -->
            <div class="col-lg-6 hero-side-panel">
                <!-- Top Brand Badge -->
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-7 font-bold">
                            <i class="bi bi-patch-check-fill me-1"></i>
                            {{ __('auth.marketing_badge') }}
                        </span>
                        <div class="text-white-50 fs-8 d-flex align-items-center gap-1">
                            <i class="bi bi-shield-lock-fill text-emerald-400"></i>
                            <span>{{ __('auth.secure_login_notice') }}</span>
                        </div>
                    </div>

                    <!-- Platform Logo (Large Size from Settings) -->
                    <div class="mb-4 text-center text-lg-start">
                        <a href="{{ url('/') }}" class="text-decoration-none d-inline-block">
                            @if($hasLogo)
                                <div class="platform-logo-container">
                                    <img src="{{ asset('storage/' . $sysLogo) }}" alt="{{ $facilityName }}" class="platform-logo-img">
                                </div>
                            @else
                                <div class="platform-logo-container gap-3">
                                    <div class="platform-logo-emblem">
                                        <i class="bi bi-layers-half"></i>
                                    </div>
                                    <div class="text-start">
                                        <h3 class="fw-bold text-white mb-0 tracking-tight" style="font-size: 1.65rem;">
                                            {{ $facilityName }}
                                        </h3>
                                        <span class="text-slate-400 fs-7 font-medium">
                                            {{ __('general.workshop_management') }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </a>
                    </div>

                    <!-- Marketing Slogan Headline -->
                    <div class="my-4 my-lg-5">
                        <h2 class="fw-extrabold text-white lh-base tracking-tight mb-3 fs-3 fs-lg-2">
                            {{ __('auth.marketing_slogan') }}
                        </h2>
                        <p class="text-slate-300 fs-6 fw-normal mb-0 opacity-90">
                            {{ __('auth.login_subtitle') }}
                        </p>
                    </div>

                    <!-- Feature Pillars -->
                    <div class="d-flex flex-column gap-3 d-none d-md-flex">
                        <div class="feature-glass-pill d-flex align-items-center gap-3">
                            <div class="feature-icon-badge">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-white mb-0 fs-6">{{ __('auth.feature_1_title') }}</h6>
                                <p class="text-slate-400 mb-0 fs-7">{{ __('auth.feature_1_desc') }}</p>
                            </div>
                        </div>

                        <div class="feature-glass-pill d-flex align-items-center gap-3">
                            <div class="feature-icon-badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399;">
                                <i class="bi bi-sliders"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-white mb-0 fs-6">{{ __('auth.feature_2_title') }}</h6>
                                <p class="text-slate-400 mb-0 fs-7">{{ __('auth.feature_2_desc') }}</p>
                            </div>
                        </div>

                        <div class="feature-glass-pill d-flex align-items-center gap-3">
                            <div class="feature-icon-badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24;">
                                <i class="bi bi-pie-chart-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-white mb-0 fs-6">{{ __('auth.feature_3_title') }}</h6>
                                <p class="text-slate-400 mb-0 fs-7">{{ __('auth.feature_3_desc') }}</p>
                            </div>
                        </div>

                        <!-- Public System Guide Callout Link Card -->
                        <a href="{{ route('system-guide') }}" class="text-decoration-none mt-1">
                            <div class="feature-glass-pill d-flex align-items-center justify-content-between gap-3" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="feature-icon-badge" style="background: rgba(16, 185, 129, 0.3); color: #34d399;">
                                        <i class="bi bi-compass-fill"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-white mb-0 fs-6">
                                            {{ app()->getLocale() == 'ar' ? 'دليل وخصائص المنظومة' : 'Explore System Guide & Features' }}
                                        </h6>
                                        <p class="text-emerald-300 mb-0 fs-8">
                                            {{ app()->getLocale() == 'ar' ? 'تعرف على كافة عناصر ومزايا النظام دون الحاجة للدخول' : 'Discover all system modules without login' }}
                                        </p>
                                    </div>
                                </div>
                                <i class="bi {{ app()->getLocale() == 'ar' ? 'bi-arrow-left-circle-fill' : 'bi-arrow-right-circle-fill' }} fs-3 text-emerald-400 flex-shrink-0"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Hero Footer System Status -->
                <div class="mt-4 pt-3 border-top border-white-10 d-flex align-items-center justify-content-between text-slate-400 fs-7">
                    <div class="d-flex align-items-center gap-2">
                        <span class="spinner-grow spinner-grow-sm text-emerald-400" role="status" style="width: 8px; height: 8px;"></span>
                        <span class="text-emerald-400 fw-semibold">{{ __('auth.system_status') }}</span>
                    </div>
                    <span>{{ date('Y') }} &copy; {{ $facilityName }}</span>
                </div>
            </div>

            <!-- Form Side Panel (Right in LTR / Left in RTL) -->
            <div class="col-lg-6 form-side-panel">
                
                <!-- Language Switcher Bar Top -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <!-- Mobile Logo Display -->
                    <div class="d-lg-none">
                        @if($hasLogo)
                            <img src="{{ asset('storage/' . $sysLogo) }}" alt="{{ $facilityName }}" style="max-height: 55px; width: auto;">
                        @else
                            <span class="fw-bold text-slate-900 fs-5">{{ $facilityName }}</span>
                        @endif
                    </div>

                    <div class="ms-auto d-flex align-items-center gap-2">
                        <i class="bi bi-globe text-slate-400 fs-6"></i>
                        <span class="fs-7 text-slate-500 font-medium me-1">{{ __('general.change_language') }}:</span>
                        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            @if($localeCode !== app()->getLocale())
                                <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}" class="lang-switch-btn">
                                    <span>{{ $properties['native'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Form Content Slot -->
                <div class="my-auto py-2">
                    {{ $slot }}
                </div>

                <!-- Form Footer -->
                <div class="mt-4 pt-3 text-center border-top text-slate-400 fs-7">
                    <p class="mb-0">
                        {{ __('general.copyright_zoal') }} &copy; {{ date('Y') }}
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Interactive Password Visibility Toggle -->
    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        }
    </script>
</body>
</html>

