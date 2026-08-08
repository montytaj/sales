<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('users.index'), 'label' => __('users.title')],
                ['label' => __('roles.roles_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 w-100">
            <div>
                <h2 class="h4 mb-1 font-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-primary"></i>
                    <span>{{ __('roles.title') }}</span>
                </h2>
                <p class="text-muted small mb-0">إدارة ومتابعة الأدوار الوظيفية والصلاحيات الممنوحة لكل دور في المنصة</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @can('manage-permissions')
                    <a href="{{ route('roles.matrix') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        <span>{{ __('roles.matrix_title') }}</span>
                    </a>
                @endcan
                @can('manage-roles')
                    <a href="{{ route('roles.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ __('roles.create_role') }}</span>
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- Filters & Search Form -->
    <div class="card shadow-sm border-0 mb-4 rounded-3 overflow-hidden">
        <div class="card-body p-3 bg-white">
            <form method="GET" action="{{ route('roles.index') }}" class="row g-3 align-items-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="input-group search-input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="بحث باسم الدور الوظيفي..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-funnel-fill"></i>
                        <span>تصفية</span>
                    </button>
                    @if (request()->filled('search'))
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary" title="إلغاء التصفية">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Roles Grid Cards -->
    <div class="row g-3 mb-4">
        @forelse ($roles as $role)
            @php
                $isTranslated = Lang::has('roles.' . $role->name);
                $displayName = $isTranslated ? __('roles.' . $role->name) : $role->name;
                $description = Lang::has('roles.descriptions.' . $role->name) ? __('roles.descriptions.' . $role->name) : null;
                $isSystemRole = in_array($role->name, ['system-admin', 'general-manager', 'accountant', 'storekeeper', 'workshop-manager']);
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-custom h-100 border-0 shadow-sm hover-shadow transition-all position-relative overflow-hidden">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">{{ $displayName }}</h5>
                                </div>
                                @if ($role->name === 'system-admin')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small rounded">
                                        <i class="bi bi-lock-fill me-1"></i>{{ __('roles.system_protected') }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-muted fs-7 mb-3 lh-sm">
                                {{ $description ?? 'دور وظيفي مخصص داخل النظام لتحديد مستوى الوصول والعمليات' }}
                            </p>
                        </div>

                        <div>
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top border-light-subtle fs-7 text-secondary mb-3">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-key-fill text-warning"></i>
                                    <strong>{{ $role->permissions_count }}</strong> صلاحية
                                </span>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-people-fill text-info"></i>
                                    <strong>{{ $role->users_count }}</strong> مستخدم
                                </span>
                            </div>

                            <div class="d-flex align-items-center justify-content-end gap-2">
                                @can('manage-roles')
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>تعديل الصلاحيات</span>
                                    </a>
                                    @if (!$isSystemRole)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('هل أنت تأكد من رغبتك في حذف هذا الدور الوظيفي؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف الدور">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-custom border-0 shadow-sm py-5 text-center">
                    <div class="card-body">
                        <i class="bi bi-shield-x fs-1 text-muted d-block mb-3"></i>
                        <h5 class="fw-bold text-dark">{{ __('roles.no_roles_found') }}</h5>
                        <p class="text-muted small">لم يتم العثور على أية أدوار وظيفية مسجلة تطابق مدخلات البحث.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if ($roles->hasPages())
        <div class="d-flex justify-content-center">
            {{ $roles->links() }}
        </div>
    @endif
</x-app-layout>
