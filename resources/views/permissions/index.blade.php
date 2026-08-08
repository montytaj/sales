<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('users.index'), 'label' => __('users.title')],
                ['label' => __('permissions.title')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 w-100">
            <div>
                <h2 class="h4 mb-1 font-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-key-fill text-warning"></i>
                    <span>{{ __('permissions.title') }}</span>
                </h2>
                <p class="text-muted small mb-0">استعراض وحصر كافة صلاحيات النظام المقسمة بحسب الأقسام الوظيفية</p>
            </div>
            @can('manage-permissions')
                <a href="{{ route('roles.matrix') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <span>مصفوفة التخصيص</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Filter & Search Card -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-3 bg-white">
            <form method="GET" action="{{ route('permissions.index') }}" class="row g-3">
                <!-- Search Input -->
                <div class="col-12 col-md-6">
                    <div class="input-group search-input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ __('permissions.search_permission') }}" value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Module Filter Dropdown -->
                <div class="col-12 col-md-4">
                    <select name="module" class="form-select filter-select">
                        <option value="">{{ __('permissions.all_modules') }}</option>
                        @foreach ($groupMapping as $modKey => $names)
                            <option value="{{ $modKey }}" {{ request('module') == $modKey ? 'selected' : '' }}>
                                {{ Lang::has('permissions.modules.' . $modKey) ? __('permissions.modules.' . $modKey) : $modKey }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-funnel-fill"></i>
                        <span>تصفية</span>
                    </button>
                    @if (request()->hasAny(['search', 'module']))
                        <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary" title="إلغاء التصفية">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Grouped Permissions View -->
    @forelse ($groupedPermissions as $moduleKey => $permissions)
        @php
            $moduleTitle = Lang::has('permissions.modules.' . $moduleKey) ? __('permissions.modules.' . $moduleKey) : $moduleKey;
        @endphp
        <div class="card card-custom border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-folder-fill text-primary"></i>
                    <span>{{ $moduleTitle }}</span>
                </h5>
                <span class="badge bg-light text-dark border font-semibold px-2.5 py-1">
                    {{ count($permissions) }} صلاحية
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">#</th>
                                <th scope="col">{{ __('permissions.permission_name') }}</th>
                                <th scope="col">{{ __('permissions.permission_key') }}</th>
                                <th scope="col">{{ __('permissions.assigned_roles') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $index => $permission)
                                @php
                                    $permLabel = Lang::has('permissions.' . $permission->name) ? __('permissions.' . $permission->name) : $permission->name;
                                @endphp
                                <tr>
                                    <td class="ps-4 text-muted fs-7">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold text-dark">{{ $permLabel }}</td>
                                    <td>
                                        <code class="text-primary bg-light px-2 py-1 rounded font-monospace fs-7">{{ $permission->name }}</code>
                                    </td>
                                    <td>
                                        @forelse ($permission->roles as $role)
                                            @php
                                                $roleLabel = Lang::has('roles.' . $role->name) ? __('roles.' . $role->name) : $role->name;
                                            @endphp
                                            <span class="badge bg-secondary-subtle text-secondary me-1 border mb-1">{{ $roleLabel }}</span>
                                        @empty
                                            <span class="text-muted fs-7">-</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-custom border-0 shadow-sm py-5 text-center">
            <div class="card-body">
                <i class="bi bi-key fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold text-dark">{{ __('permissions.no_permissions_found') }}</h5>
                <p class="text-muted small">لم يتم العثور على أي صلاحيات تطابق شروط البحث المختارة.</p>
            </div>
        </div>
    @endforelse
</x-app-layout>
