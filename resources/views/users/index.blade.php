<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('users.users_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-people-fill text-primary"></i>
                <span>{{ __('users.users_list') }}</span>
            </h2>
            @can('create-users')
                <a href="{{ route('users.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>{{ __('users.create_user') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Filters & Search Form -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-3 bg-white">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                <!-- Search Input -->
                <div class="col-12 col-md-5">
                    <div class="input-group search-input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ __('users.search') }}" value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Role Filter -->
                <div class="col-12 col-md-3">
                    <select name="role" class="form-select filter-select">
                        <option value="">{{ __('users.all_roles') }}</option>
                        @foreach ($roles as $role)
                            @php
                                $roleLabel = Lang::has('roles.' . $role->name) ? __('roles.' . $role->name) : $role->name;
                            @endphp
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                {{ $roleLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-12 col-md-2">
                    <select name="status" class="form-select filter-select">
                        <option value="">{{ __('users.all_statuses') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('users.active') }}</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('users.inactive') }}</option>
                    </select>
                </div>

                <!-- Submit & Clear Buttons -->
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-funnel-fill"></i>
                        <span>تصفية</span>
                    </button>
                    @if (request()->hasAny(['search', 'role', 'status']))
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="إلغاء التصفية">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card card-custom shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable-init">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">#</th>
                            <th scope="col">{{ __('users.name') }}</th>
                            <th scope="col">{{ __('users.email') }}</th>
                            <th scope="col">{{ __('users.roles') }}</th>
                            <th scope="col">{{ __('users.status') }}</th>
                            <th scope="col" class="text-end pe-4">{{ __('users.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <th scope="row" class="ps-4 text-muted fs-7">{{ $user->id }}</th>
                                <td class="fw-semibold">
                                    <a href="{{ route('users.show', $user) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $user->name }}
                                    </a>
                                </td>
                                <td class="text-secondary">{{ $user->email }}</td>
                                <td>
                                    @forelse ($user->roles as $role)
                                        @php
                                            $roleLabel = Lang::has('roles.' . $role->name) ? __('roles.' . $role->name) : $role->name;
                                        @endphp
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1 px-2.5 py-1 font-medium">
                                            {{ $roleLabel }}
                                        </span>
                                    @empty
                                        <span class="badge bg-secondary-subtle text-muted">-</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">{{ __('users.active') }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">{{ __('users.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary" title="{{ __('users.show_user') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @can('edit-users')
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary" title="{{ __('users.edit_user') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('toggle-user-status')
                                            @if ($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" title="{{ $user->is_active ? __('users.deactivate') : __('users.activate') }}">
                                                        <i class="bi bi-power"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                    {{ __('users.no_users_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($users->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
