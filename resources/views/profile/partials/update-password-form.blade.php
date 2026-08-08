<section>
    <div class="border-bottom pb-3 mb-4">
        <h5 class="font-bold text-slate-800 mb-1">
            <i class="bi bi-shield-lock text-warning me-2"></i>تغيير كلمة المرور
        </h5>
        <p class="text-muted fs-7 mb-0">
            تأكد من استخدام كلمة مرور قوية تحتوي على حروف وأرقام للحفاظ على أمان حسابك.
        </p>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="row g-3">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div class="col-12">
            <label for="update_password_current_password" class="form-label font-medium text-slate-700 fs-7">كلمة المرور الحالية <span class="text-danger">*</span></label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" required />
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback fs-7">{{ $message }}</div>
            @enderror
        </div>

        <!-- New Password -->
        <div class="col-12 col-md-6">
            <label for="update_password_password" class="form-label font-medium text-slate-700 fs-7">كلمة المرور الجديدة <span class="text-danger">*</span></label>
            <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" required />
            @error('password', 'updatePassword')
                <div class="invalid-feedback fs-7">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="col-12 col-md-6">
            <label for="update_password_password_confirmation" class="form-label font-medium text-slate-700 fs-7">تأكيد كلمة المرور الجديدة <span class="text-danger">*</span></label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password" required />
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback fs-7">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 d-flex align-items-center gap-3 pt-2">
            <button type="submit" class="btn btn-warning px-4 text-white font-semibold">
                <i class="bi bi-key me-1"></i>تحديث كلمة المرور
            </button>

            @if (session('status') === 'password-updated')
                <span class="badge bg-success-subtle text-success fs-7 border border-success-subtle px-3 py-2">
                    <i class="bi bi-check-lg me-1"></i>تم تغيير كلمة المرور بنجاح.
                </span>
            @endif
        </div>
    </form>
</section>
