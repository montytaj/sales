<x-app-layout>
    <div class="min-vh-75 d-flex align-items-center justify-content-center py-5">
        <div class="text-center max-w-lg mx-auto card-custom p-5">
            <div class="rounded-circle bg-danger-subtle text-danger mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="bi bi-bug-fill fs-1"></i>
            </div>
            <h1 class="display-5 font-extrabold text-slate-900 mb-2">500</h1>
            <h2 class="h4 font-bold text-slate-800 mb-3">{{ __('general.server_error') ?? 'خطأ في الخادم' }}</h2>
            <p class="text-muted fs-6 mb-4">
                {{ __('general.server_error_message') ?? 'حدث خطأ غير متوقع في الخادم أثناء معالجة طلبك. يرجى المحاولة لاحقاً.' }}
            </p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary-custom px-4 shadow-sm">
                <i class="bi bi-house-door-fill"></i>
                <span>{{ __('general.back_to_dashboard') ?? 'العودة للرئيسية' }}</span>
            </a>
        </div>
    </div>
</x-app-layout>
