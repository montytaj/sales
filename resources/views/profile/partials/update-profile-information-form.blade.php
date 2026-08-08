<section>
    <div class="border-bottom pb-3 mb-4">
        <h5 class="font-bold text-slate-800 mb-1">
            <i class="bi bi-person-lines-fill text-primary me-2"></i>{{ __('users.activity_log') ? 'تعديل البيانات الشخصية وصورة الحساب' : 'تعديل البيانات الشخصية وصورة الحساب' }}
        </h5>
        <p class="text-muted fs-7 mb-0">
            تحديث اسم المستخدم، البريد الإلكتروني وصورة الملف الشخصي الخاصة بك في النظام.
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        @method('patch')

        <!-- Avatar Selection -->
        <div class="col-12 mb-2">
            <label class="form-label font-medium text-slate-700 fs-7">صورة الملف الشخصي</label>
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle border shadow-sm object-fit-cover" style="width: 70px; height: 70px;" id="avatarPreview">
                </div>
                <div class="flex-grow-1">
                    <input type="file" name="avatar" id="avatarInput" class="form-control form-control-sm @error('avatar') is-invalid @enderror" accept="image/*" onchange="previewUserAvatar(event)">
                    <div class="form-text fs-8 text-muted mt-1">
                        الصور المسموح بها: JPG, PNG, WEBP. الحد الأقصى للحجم: 4 ميجابايت.
                    </div>
                    @error('avatar')
                        <div class="invalid-feedback fs-7">{{ $message }}</div>
                    @enderror

                    @if($user->avatar)
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="removeAvatar">
                            <label class="form-check-label fs-7 text-danger" for="removeAvatar">
                                حذف الصورة الحالية والعودة للصورة الافتراضية
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Name -->
        <div class="col-12 col-md-6">
            <label for="name" class="form-label font-medium text-slate-700 fs-7">الاسم الكامل <span class="text-danger">*</span></label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')
                <div class="invalid-feedback fs-7">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email -->
        <div class="col-12 col-md-6">
            <label for="email" class="form-label font-medium text-slate-700 fs-7">البريد الإلكتروني <span class="text-danger">*</span></label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email')
                <div class="invalid-feedback fs-7">{{ $message }}</div>
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

        <div class="col-12 d-flex align-items-center gap-3 pt-2">
            <button type="submit" class="btn btn-primary btn-primary-custom px-4">
                <i class="bi bi-check-circle me-1"></i>حفظ التغييرات
            </button>

            @if (session('status') === 'profile-updated')
                <span class="badge bg-success-subtle text-success fs-7 border border-success-subtle px-3 py-2">
                    <i class="bi bi-check-lg me-1"></i>تم تحديث بيانات الملف الشخصي بنجاح.
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
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }
</script>
