<x-guest-layout>
    <!-- Header Titles -->
    <div class="mb-4 text-center text-lg-start">
        <h3 class="fw-extrabold text-slate-900 tracking-tight fs-2 mb-2">
            {{ __('auth.login_heading') }}
        </h3>
        <p class="text-slate-500 fs-6 font-medium mb-0">
            {{ __('auth.login_subtitle') }}
        </p>
    </div>

    <!-- Session Alerts Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}" class="mt-4">
        @csrf

        <!-- Email / Username Field -->
        <div class="mb-3.5">
            <label for="email" class="form-label font-semibold text-slate-700 fs-7 mb-1.5 d-block">
                {{ __('general.email') }}
            </label>
            <div class="input-group custom-input-group">
                <span class="input-group-text">
                    <i class="bi bi-envelope"></i>
                </span>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="username" 
                       placeholder="{{ __('auth.email_placeholder') }}"
                       class="form-control custom-input-control @error('email') is-invalid @enderror">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password Field -->
        <div class="mb-3.5">
            <label for="password" class="form-label font-semibold text-slate-700 fs-7 mb-1.5 d-block">
                {{ __('general.password') }}
            </label>
            <div class="input-group custom-input-group">
                <span class="input-group-text">
                    <i class="bi bi-lock"></i>
                </span>
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password" 
                       placeholder="{{ __('auth.password_placeholder') }}"
                       class="form-control custom-input-control @error('password') is-invalid @enderror">
                <button type="button" class="btn btn-toggle-password border" onclick="togglePasswordVisibility('password', this)" aria-label="{{ __('auth.show_password') }}">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me Checkbox & Forgot Password Link -->
        <div class="d-flex align-items-center justify-content-between mb-4 pt-1">
            <div class="form-check">
                <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
                <label for="remember_me" class="form-check-label fs-7 font-medium text-slate-600 cursor-pointer">
                    {{ __('general.remember_me') }}
                </label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="fs-7 font-semibold text-primary text-decoration-none hover-underline">
                    {{ __('general.forgot_password') }}
                </a>
            @endif
        </div>


        <!-- Submit Button -->
        <button type="submit" class="btn btn-login-submit w-100 d-flex align-items-center justify-content-center gap-2">
            <span>{{ __('general.login') }}</span>
            <i class="bi {{ app()->getLocale() == 'ar' ? 'bi-arrow-left-short' : 'bi-arrow-right-short' }} fs-4"></i>
        </button>

        <!-- System Guide Public Entry Link -->
        <div class="mt-4 pt-3 text-center border-top">
            <a href="{{ route('system-guide') }}" class="btn btn-outline-primary w-100 rounded-3 py-2.5 fs-7 font-bold d-flex align-items-center justify-content-center gap-2 hover-lift">
                <i class="bi bi-compass fs-5"></i>
                <span>{{ app()->getLocale() == 'ar' ? 'استعراض دليل وخصائص المنظومة' : 'Explore System Guide & Features' }}</span>
            </a>
        </div>
    </form>
</x-guest-layout>

