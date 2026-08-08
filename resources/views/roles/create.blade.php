<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('users.index'), 'label' => __('users.title')],
                ['url' => route('roles.index'), 'label' => __('roles.roles_list')],
                ['label' => __('roles.create_role')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-shield-plus text-primary"></i>
                <span>{{ __('roles.create_role') }}</span>
            </h2>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-right"></i>
                <span>عودة للقائمة</span>
            </a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf

        <div class="row g-4">
            <!-- Role Details Card -->
            <div class="col-12 col-lg-4">
                <div class="card card-custom border-0 shadow-sm sticky-top" style="top: 85px;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-primary"></i>
                            <span>بيانات الدور الوظيفي</span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Role Name Key -->
                        <div class="mb-3">
                            <label for="name" class="form-label font-semibold">{{ __('roles.role_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. sales-manager" required autofocus>
                            <div class="form-text fs-7">{{ __('roles.role_name_help') }}</div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2.5 fw-bold d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                                <span>حفظ الدور الصلاحيات</span>
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
                            <span>تحديد الصلاحيات الممنوحة</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAll">
                                <i class="bi bi-check-all me-1"></i>تحديد الكل
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                                <i class="bi bi-x-lg me-1"></i>إلغاء الكل
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
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
                                    <div class="form-check">
                                        <input class="form-check-input select-module-all" type="checkbox" id="module_{{ $moduleKey }}" data-module="{{ $moduleKey }}">
                                        <label class="form-check-label fs-7 fw-semibold text-secondary" for="module_{{ $moduleKey }}">تحديد القسم</label>
                                    </div>
                                </div>

                                <div class="row g-2 px-2">
                                    @foreach ($permissions as $permission)
                                        @php
                                            $permLabel = Lang::has('permissions.' . $permission->name) ? __('permissions.' . $permission->name) : $permission->name;
                                        @endphp
                                        <div class="col-12 col-md-6">
                                            <div class="form-check custom-permission-check p-2.5 rounded border bg-white hover-bg-light transition-all">
                                                <input class="form-check-input perm-checkbox perm-module-{{ $moduleKey }}" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" {{ is_array(old('permissions')) && in_array($permission->name, old('permissions')) ? 'checked' : '' }}>
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
            // Select / Deselect All
            document.getElementById('btnSelectAll')?.addEventListener('click', function() {
                document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
                document.querySelectorAll('.select-module-all').forEach(cb => cb.checked = true);
            });

            document.getElementById('btnDeselectAll')?.addEventListener('click', function() {
                document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
                document.querySelectorAll('.select-module-all').forEach(cb => cb.checked = false);
            });

            // Select Module All
            document.querySelectorAll('.select-module-all').forEach(modCb => {
                modCb.addEventListener('change', function() {
                    const moduleKey = this.getAttribute('data-module');
                    document.querySelectorAll('.perm-module-' + moduleKey).forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            });
        });
    </script>
</x-app-layout>
