<section>
    <div class="border-bottom pb-3 mb-4">
        <h5 class="font-bold text-slate-800 mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock text-amber-500 fs-5"></i>
            <span>{{ app()->getLocale() == 'ar' ? 'تغيير كلمة المرور' : 'Change Password' }}</span>
        </h5>
        <p class="text-slate-500 fs-7 mb-0">
            {{ app()->getLocale() == 'ar' ? 'تأكد من استخدام كلمة مرور قوية تحتوي على حروف وأرقام للحفاظ على أمان حسابك.' : 'Ensure your account is using a long, random password to stay secure.' }}
        </p>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="row g-3">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div class="col-12">
            <label for="update_password_current_password" class="form-label font-medium text-slate-700 fs-7">{{ app()->getLocale() == 'ar' ? 'كلمة المرور الحالية' : 'Current Password' }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-slate-50 text-slate-400 border-end-0"><i class="bi bi-lock"></i></span>
                <input id="update_password_current_password" name="current_password" type="password" class="form-control border-start-0 @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" required />
            </div>
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback d-block fs-7">{{ $message }}</div>
            @enderror
        </div>

        <!-- New Password -->
        <div class="col-12 col-md-6">
            <label for="update_password_password" class="form-label font-medium text-slate-700 fs-7">{{ app()->getLocale() == 'ar' ? 'كلمة المرور الجديدة' : 'New Password' }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-slate-50 text-slate-400 border-end-0"><i class="bi bi-key"></i></span>
                <input id="update_password_password" name="password" type="password" class="form-control border-start-0 @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" required />
            </div>
            @error('password', 'updatePassword')
                <div class="invalid-feedback d-block fs-7">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="col-12 col-md-6">
            <label for="update_password_password_confirmation" class="form-label font-medium text-slate-700 fs-7">{{ app()->getLocale() == 'ar' ? 'تأكيد كلمة المرور الجديدة' : 'Confirm New Password' }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-slate-50 text-slate-400 border-end-0"><i class="bi bi-check2-circle"></i></span>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control border-start-0 @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password" required />
            </div>
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback d-block fs-7">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 d-flex align-items-center gap-3 pt-3 border-top mt-4">
            <button type="submit" class="btn btn-warning px-4 text-white font-semibold shadow-sm">
                <i class="bi bi-key me-1"></i>{{ app()->getLocale() == 'ar' ? 'تحديث كلمة المرور' : 'Update Password' }}
            </button>

            @if (session('status') === 'password-updated')
                <span class="badge bg-success-subtle text-success fs-7 border border-success-subtle px-3 py-2">
                    <i class="bi bi-check-lg me-1"></i>{{ app()->getLocale() == 'ar' ? 'تم تغيير كلمة المرور بنجاح.' : 'Password updated successfully.' }}
                </span>
            @endif
        </div>
    </form>
</section>
