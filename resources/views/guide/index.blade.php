@php
    $isAuth = auth()->check();
    $sysLogo = setting('logo');
    $facilityName = setting('facility_name', app()->getLocale() == 'ar' ? 'إدارة المبيعات والمشتريات والمخازن' : 'Sales & Inventory ERP');
    $hasLogo = $sysLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($sysLogo);
    $primaryColor = setting('primary_color', '#2563eb');
    $secondaryColor = setting('secondary_color', '#0f172a');
    $accentColor = setting('accent_color', '#10b981');
@endphp

@if($isAuth)
    <x-app-layout>
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary text-white p-2.5 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi bi-compass-fill fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-slate-900 mb-0 fs-4">
                            {{ app()->getLocale() == 'ar' ? 'دليل وشرح خصائص المنظومة' : 'System Features & Usage Guide' }}
                        </h4>
                        <p class="text-slate-500 fs-7 mb-0">
                            {{ app()->getLocale() == 'ar' ? 'استعراض شامل لجميع وحدات ومزايا ومفاهيم عمل المنظومة' : 'Comprehensive breakdown of all system modules, workflows, and core features' }}
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-3 px-3 fs-7 font-medium d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'العودة للوحة التحكم' : 'Back to Dashboard' }}</span>
                    </a>
                </div>
            </div>
        </x-slot>

        @include('guide.partials.content')
    </x-app-layout>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $facilityName }} - {{ app()->getLocale() == 'ar' ? 'دليل ومزايا النظام' : 'System Features Guide' }}</title>

        <!-- Google Fonts Cairo & Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Bootstrap 5.3 CSS -->
        @if(app()->getLocale() == 'ar')
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
        @else
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        @endif

        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- App CSS via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --brand-primary: {{ $primaryColor }};
                --brand-secondary: {{ $secondaryColor }};
                --brand-accent: {{ $accentColor }};
                --font-main: 'Cairo', 'Inter', sans-serif;
            }
            body {
                font-family: var(--font-main) !important;
                background-color: #0b1329;
                color: #f8fafc;
                min-height: 100vh;
                overflow-x: hidden;
            }
            .guest-navbar {
                background: rgba(15, 23, 42, 0.85);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                position: sticky;
                top: 0;
                z-index: 1040;
            }
            .btn-login-accent {
                background: linear-gradient(135deg, var(--brand-primary), #1d4ed8);
                color: #ffffff;
                font-weight: 700;
                border-radius: 12px;
                padding: 0.55rem 1.35rem;
                box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
                transition: all 0.25s ease;
                border: none;
            }
            .btn-login-accent:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(37, 99, 235, 0.5);
                color: #ffffff;
            }
            .guide-hero-wrapper {
                position: relative;
                padding: 4rem 1rem 3rem 1rem;
                background: radial-gradient(circle at 50% 20%, #1e293b 0%, #0f172a 70%, #080d1a 100%);
                overflow: hidden;
            }
            .ambient-light-1 {
                position: absolute;
                top: -100px;
                right: -100px;
                width: 500px;
                height: 500px;
                background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, rgba(0,0,0,0) 70%);
                border-radius: 50%;
                filter: blur(70px);
                pointer-events: none;
            }
            .ambient-light-2 {
                position: absolute;
                bottom: -100px;
                left: -100px;
                width: 450px;
                height: 450px;
                background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, rgba(0,0,0,0) 70%);
                border-radius: 50%;
                filter: blur(70px);
                pointer-events: none;
            }
            .footer-guest {
                background: #070b16;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                padding: 2rem 0;
            }
            .hover-lift {
                transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .hover-lift:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25) !important;
            }
        </style>
    </head>
    <body class="d-flex flex-column min-vh-100">

        <!-- Navbar for Unauthenticated Guests -->
        <nav class="guest-navbar py-2.5 px-3 px-md-4">
            <div class="container-fluid max-w-7xl mx-auto d-flex align-items-center justify-content-between">
                <a href="{{ url('/') }}" class="d-flex align-items-center gap-2.5 text-decoration-none">
                    @if($hasLogo)
                        <img src="{{ asset('storage/' . $sysLogo) }}" alt="{{ $facilityName }}" style="max-height: 44px; width: auto; object-fit: contain;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-layers-half fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-white mb-0 fs-6 tracking-tight">{{ $facilityName }}</h5>
                            <span class="text-slate-400 fs-8 d-block">{{ app()->getLocale() == 'ar' ? 'منظومة إدارة المؤسسات' : 'Enterprise ERP' }}</span>
                        </div>
                    @endif
                </a>

                <div class="d-flex align-items-center gap-3">
                    <!-- Language Switcher -->
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        @if($localeCode !== app()->getLocale())
                            <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}" class="btn btn-sm btn-outline-light rounded-pill px-3 fs-7">
                                <i class="bi bi-globe me-1"></i>
                                <span>{{ $properties['native'] }}</span>
                            </a>
                        @endif
                    @endforeach

                    <!-- Login Button -->
                    <a href="{{ route('login') }}" class="btn btn-login-accent fs-7 d-flex align-items-center gap-2">
                        <span>{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول' : 'Sign In' }}</span>
                        <i class="bi {{ app()->getLocale() == 'ar' ? 'bi-arrow-left-short' : 'bi-arrow-right-short' }} fs-5"></i>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Body Content -->
        <main class="flex-grow-1">
            @include('guide.partials.content')
        </main>

        <!-- Guest Footer -->
        <footer class="footer-guest text-center text-slate-400 fs-8 mt-auto">
            <div class="container max-w-7xl mx-auto px-3">
                <p class="mb-1 text-slate-300">
                    &copy; {{ date('Y') }} <strong class="text-white">{{ $facilityName }}</strong>. {{ app()->getLocale() == 'ar' ? 'جميع الحقوق محفوظة - دليل وخصائص المنظومة الإدارية والمحاسبية' : 'All Rights Reserved - Enterprise System Features Guide' }}
                </p>
                <div class="d-flex justify-content-center gap-3 mt-2 fs-7">
                    <a href="{{ route('login') }}" class="text-primary text-decoration-none font-semibold hover-underline">
                        <i class="bi bi-box-arrow-in-right me-1"></i> {{ app()->getLocale() == 'ar' ? 'تسجيل الدخول للنظام' : 'System Login' }}
                    </a>
                </div>
            </div>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
@endif
