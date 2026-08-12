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
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-3 px-3 fs-7 font-medium d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'العودة للوحة التحكم' : 'Back to Dashboard' }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary rounded-3 px-3 fs-7 font-medium d-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول للنظام' : 'Sign In to System' }}</span>
                    </a>
                @endauth
            </div>
        </div>
    </x-slot>

    @include('guide.partials.content')
</x-app-layout>
