<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('users.index'), 'label' => __('users.title')],
                ['url' => route('roles.index'), 'label' => __('roles.roles_list')],
                ['label' => __('roles.edit_role') . ': ' . (Lang::has('roles.' . $role->name) ? __('roles.' . $role->name) : $role->name)]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-primary"></i>
                <span>{{ __('roles.edit_role') }}: {{ Lang::has('roles.' . $role->name) ? __('roles.' . $role->name) : $role->name }}</span>
            </h2>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-right"></i>
                <span>عودة للقائمة</span>
            </a>
        </div>
    </x-slot>

    @php
        $isSystemRole = in_array($role->name, ['system-admin', 'general-manager', 'accountant', 'storekeeper', 'workshop-manager']);
    @endphp

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Role Details Card -->
            <div class="col-12 col-lg-4">
                <div class="card card-custom border-0 shadow-sm sticky-top" style="top: 85px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-primary"></i>
                            <span>تفاصيل الدور الوظيفي</span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Role Name Key -->
                        <div class="mb-3">
                            <label for="name" class="form-label font-semibold">{{ __('roles.role_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" {{ $isSystemRole ? 'readonly' : '' }} required>
                            @if ($isSystemRole)
                                <div class="form-text text-warning fs-7"><i class="bi bi-lock-fill me-1"></i>دور أساسي محمي لا يمكن تغيير اسم المعرف الخاص به.</div>
                            @endif
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 p-3 bg-light rounded border fs-7 text-secondary">
                            <div class="d-flex justify-content-between mb-1">
                                <span>عدد المستخدمين:</span>
                                <strong class="text-dark">{{ $role->users()->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>عدد الصلاحيات الحالية:</span>
                                <strong class="text-primary">{{ $role->permissions()->count() }}</strong>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2.5 fw-bold d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                                <span>تحديث الدور والصلاحيات</span>
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-light border py-2 text-muted">إلغاء</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Selection Card -->
            <div class="col-12 col-lg-8">
                <div class="card card-custom border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-key-fill text-warning"></i>
                            <span>تعديل الصلاحيات الممنوحة</span>
                        </h5>
                        @if ($role->name !== 'system-admin')
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAll">
                                    <i class="bi bi-check-all me-1"></i>تحديد الكل
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                                    <i class="bi bi-x-lg me-1"></i>إلغاء الكل
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        @if ($role->name === 'system-admin')
                            <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
                                <i class="bi bi-shield-check fs-4"></i>
                                <div>
                                    <strong>دور مدير النظام محمي تلقائياً:</strong> يملك مدير النظام جميع الصلاحيات دائماً ولا يمكن إزالة أي صلاحيات منه.
                                </div>
                            </div>
                        @endif

                        @foreach ($groupedPermissions as $moduleKey => $permissions)
                            @php
                                $moduleTitle = Lang::has('permissions.modules.' . $moduleKey) ? __('permissions.modules.' . $moduleKey) : $moduleKey;
                            @endphp
                            <div class="module-permission-group mb-4 pb-3 border-bottom last-border-0">
                                <div class="d-flex align-items-center justify-content-between bg-light p-2.5 rounded-3 mb-3 border">
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-folder2-open text-primary"></i>
                                        <span>{{ $moduleTitle }}</span>
                                    </h6>
                                    @if ($role->name !== 'system-admin')
                                        <div class="form-check">
                                            <input class="form-check-input select-module-all" type="checkbox" id="module_{{ $moduleKey }}" data-module="{{ $moduleKey }}">
                                            <label class="form-check-label fs-7 fw-semibold text-secondary" for="module_{{ $moduleKey }}">تحديد القسم</label>
                                        </div>
                                    @endif
                                </div>

                                <div class="row g-2 px-2">
                                    @foreach ($permissions as $permission)
                                        @php
                                            $permLabel = Lang::has('permissions.' . $permission->name) ? __('permissions.' . $permission->name) : $permission->name;
                                            $isChecked = in_array($permission->name, $rolePermissions) || $role->name === 'system-admin';
                                        @endphp
                                        <div class="col-12 col-md-6">
                                            <div class="form-check custom-permission-check p-2.5 rounded border bg-white hover-bg-light transition-all">
                                                <input class="form-check-input perm-checkbox perm-module-{{ $moduleKey }}" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" {{ $isChecked ? 'checked' : '' }} {{ $role->name === 'system-admin' ? 'disabled' : '' }}>
                                                <label class="form-check-label w-100 cursor-pointer text-dark fs-7 font-medium" for="perm_{{ $permission->id }}">
                                                    <span>{{ $permLabel }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('btnSelectAll')?.addEventListener('click', function() {
                document.querySelectorAll('.perm-checkbox:not(:disabled)').forEach(cb => cb.checked = true);
                document.querySelectorAll('.select-module-all').forEach(cb => cb.checked = true);
            });

            document.getElementById('btnDeselectAll')?.addEventListener('click', function() {
                document.querySelectorAll('.perm-checkbox:not(:disabled)').forEach(cb => cb.checked = false);
                document.querySelectorAll('.select-module-all').forEach(cb => cb.checked = false);
            });

            document.querySelectorAll('.select-module-all').forEach(modCb => {
                modCb.addEventListener('change', function() {
                    const moduleKey = this.getAttribute('data-module');
                    document.querySelectorAll('.perm-module-' + moduleKey + ':not(:disabled)').forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            });
        });
    </script>
</x-app-layout>
