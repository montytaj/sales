<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('branches.branches_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-building-fill text-primary me-2"></i>{{ __('branches.branches_list') }}
            </h2>
            @can('create-branches')
                <a href="{{ route('branches.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('branches.create_branch') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Search Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('branches.index') }}" class="row g-3">
                <div class="col-12 col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث باسم الفرع أو الكود أو الهاتف..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->filled('search'))
                        <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Branches Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">{{ __('branches.code') }}</th>
                            <th scope="col">{{ __('branches.name') }}</th>
                            <th scope="col">{{ __('branches.phone') }}</th>
                            <th scope="col">{{ __('branches.is_main') }}</th>
                            <th scope="col">{{ __('branches.status') }}</th>
                            <th scope="col" class="text-end pe-3">{{ __('branches.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branches as $branch)
                            <tr>
                                <th scope="row" class="ps-3">{{ $branch->id }}</th>
                                <td><code>{{ $branch->code }}</code></td>
                                <td class="fw-semibold">
                                    <a href="{{ route('branches.show', $branch) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $branch->name }}
                                    </a>
                                </td>
                                <td>{{ $branch->phone ?? '-' }}</td>
                                <td>
                                    @if ($branch->is_main)
                                        <span class="badge bg-warning text-dark border border-warning">
                                            <i class="bi bi-star-fill me-1"></i>{{ __('branches.main_branch') }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border">{{ __('branches.sub_branch') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($branch->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success">{{ __('branches.active') }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger">{{ __('branches.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('branches.show', $branch) }}" class="btn btn-outline-secondary" title="{{ __('branches.show_branch') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @can('edit-branches')
                                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-outline-primary" title="{{ __('branches.edit_branch') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('toggle-branch-status')
                                            @if (!$branch->is_main)
                                                <form method="POST" action="{{ route('branches.toggle-status', $branch) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-{{ $branch->is_active ? 'warning' : 'success' }}" title="{{ $branch->is_active ? __('branches.inactive') : __('branches.active') }}">
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
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-building-exclamation fs-3 d-block mb-2"></i>
                                    {{ __('branches.no_branches_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($branches->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $branches->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
