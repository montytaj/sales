<section>
    <div class="border-bottom pb-3 mb-4">
        <h5 class="font-bold text-slate-800 mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-person-lines-fill text-primary fs-5"></i>
            <span>{{ app()->getLocale() == 'ar' ? 'تعديل البيانات الشخصية وصورة الحساب' : 'Update Profile & Avatar' }}</span>
        </h5>
        <p class="text-slate-500 fs-7 mb-0">
            {{ app()->getLocale() == 'ar' ? 'تحديث اسم المستخدم، البريد الإلكتروني وصورة الملف الشخصي الخاصة بك في المنظومة.' : 'Update your account name, email address, and avatar.' }}
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        @method('patch')

        <!-- Avatar Selection -->
        <div class="col-12 mb-3">
            <label class="form-label font-medium text-slate-700 fs-7 mb-2">{{ app()->getLocale() == 'ar' ? 'صورة الملف الشخصي' : 'Profile Avatar' }}</label>
            <div class="d-flex align-items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-3">
                <div class="position-relative flex-shrink-0">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle border border-2 border-white shadow-sm object-fit-cover bg-white" style="width: 75px; height: 75px;" id="avatarPreview">
                </div>
                <div class="flex-grow-1">
                    <input type="file" name="avatar" id="avatarInput" class="form-control form-control-sm @error('avatar') is-invalid @enderror" accept="image/*" onchange="previewUserAvatar(event)">
                    <div class="form-text fs-8 text-slate-500 mt-1">
                        {{ app()->getLocale() == 'ar' ? 'الصور المسموح بها: JPG, PNG, WEBP. الحد الأقصى للحجم: 4 ميجابايت.' : 'Allowed formats: JPG, PNG, WEBP. Max size: 4MB.' }}
                    </div>
                    @error('avatar')
                        <div class="invalid-feedback fs-7">{{ $message }}</div>
                    @enderror

                    @if($user->avatar)
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="removeAvatar">
                            <label class="form-check-label fs-7 text-danger font-medium" for="removeAvatar">
                                <i class="bi bi-trash me-1"></i>{{ app()->getLocale() == 'ar' ? 'حذف الصورة الحالية والعودة للصورة الافتراضية' : 'Remove current avatar' }}
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Name -->
        <div class="col-12 col-md-6">
            <label for="name" class="form-label font-medium text-slate-700 fs-7">{{ app()->getLocale() == 'ar' ? 'الاسم الكامل' : 'Full Name' }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-slate-50 text-slate-400 border-end-0"><i class="bi bi-person-vcard"></i></span>
                <input id="name" name="name" type="text" class="form-control border-start-0 @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            </div>
            @error('name')
                <div class="invalid-feedback d-block fs-7">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email -->
        <div class="col-12 col-md-6">
            <label for="email" class="form-label font-medium text-slate-700 fs-7">{{ app()->getLocale() == 'ar' ? 'البريد الإلكتروني' : 'Email Address' }} <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-slate-50 text-slate-400 border-end-0"><i class="bi bi-envelope-at"></i></span>
                <input id="email" name="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            </div>
            @error('email')
                <div class="invalid-feedback d-block fs-7">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="fs-7 text-warning">
                        البريد الإلكتروني غير مؤكد حالياً.
                        <button form="send-verification" class="btn btn-link p-0 fs-7 text-primary text-decoration-underline">
                            انقر هنا لإعادة إرسال رابط التحقق.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium fs-7 text-success">
                            تم إرسال رابط تحقق جديد إلى عنوان بريدك الإلكتروني.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="col-12 d-flex align-items-center gap-3 pt-3 border-top mt-4">
            <button type="submit" class="btn btn-primary btn-primary-custom px-4 font-semibold">
                <i class="bi bi-check-circle me-1"></i>{{ app()->getLocale() == 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
            </button>

            @if (session('status') === 'profile-updated')
                <span class="badge bg-success-subtle text-success fs-7 border border-success-subtle px-3 py-2">
                    <i class="bi bi-check-lg me-1"></i>{{ app()->getLocale() == 'ar' ? 'تم تحديث بيانات الملف الشخصي بنجاح.' : 'Profile updated successfully.' }}
                </span>
            @endif
        </div>
    </form>
</section>

<script>
    function previewUserAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img1 = document.getElementById('avatarPreview');
                const img2 = document.getElementById('headerAvatarPreview');
                if (img1) img1.src = e.target.result;
                if (img2) img2.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }
</script>
