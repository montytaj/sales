<section>
    <div class="pb-2">
        <h5 class="font-bold text-danger mb-1">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>حذف الحساب الشخصي
        </h5>
        <p class="text-muted fs-7 mb-3">
            بمجرد حذف حسابك، سيتم حذف جميع البيانات والموارد المرتبطة به نهائياً.
        </p>

        <button type="button" class="btn btn-outline-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
            <i class="bi bi-trash me-1"></i>حذف الحساب نهائياً
        </button>
    </div>

    <!-- Modal Confirmation -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header bg-danger text-white">
                        <h6 class="modal-title font-bold" id="deleteAccountModalLabel">
                            <i class="bi bi-shield-exclamation me-1"></i>تأكيد حذف الحساب نهائياً
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p class="fs-7 text-slate-700 mb-3">
                            هل أنت تأكد من رغبتك في حذف حسابك؟ بمجرد الحذف لا يمكن استعادة البيانات. يرجى إدخال كلمة المرور لتأكيد الحذف.
                        </p>

                        <div class="mb-3">
                            <label for="delete_password" class="form-label font-medium text-slate-700 fs-7">كلمة المرور لتأكيد الحذف <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="delete_password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="أدخل كلمة المرور الحالية..." required>
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback fs-7">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer bg-slate-50">
                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger px-4">تأكيد الحذف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
