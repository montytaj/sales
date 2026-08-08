<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('branches.index'), 'label' => __('branches.branches_list')],
                ['label' => __('branches.show_branch')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-building font-bold text-primary me-2"></i>{{ __('branches.show_branch') }}: {{ $branch->name }}
            </h2>
            @can('edit-branches')
                <a href="{{ route('branches.edit', $branch) }}" class="btn btn-outline-primary d-flex align-items-center gap-1">
                    <i class="bi bi-pencil"></i> {{ __('branches.edit_branch') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Branch Details Card -->
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 bg-primary-subtle text-primary rounded-circle mb-3">
                        <i class="bi bi-building fs-1"></i>
                    </div>
                    <h4 class="font-bold text-dark mb-1">{{ $branch->name }}</h4>
                    <p class="text-muted mb-2"><code>{{ $branch->code }}</code></p>

                    <div class="mb-3">
                        @if ($branch->is_main)
                            <span class="badge bg-warning text-dark border border-warning px-3 py-2 fs-6">
                                <i class="bi bi-star-fill me-1"></i>{{ __('branches.main_branch') }}
                            </span>
                        @else
                            <span class="badge bg-light text-secondary border px-3 py-2 fs-6">{{ __('branches.sub_branch') }}</span>
                        @endif

                        @if ($branch->is_active)
                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6 ms-1">{{ __('branches.active') }}</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6 ms-1">{{ __('branches.inactive') }}</span>
                        @endif
                    </div>

                    <hr class="my-3 text-secondary">

                    <div class="text-start small">
                        <p class="mb-2"><strong><i class="bi bi-geo-alt me-1"></i>{{ __('branches.address') }}:</strong> {{ $branch->address ?? '-' }}</p>
                        <p class="mb-2"><strong><i class="bi bi-telephone me-1"></i>{{ __('branches.phone') }}:</strong> {{ $branch->phone ?? '-' }}</p>
                        <p class="mb-0"><strong><i class="bi bi-envelope me-1"></i>{{ __('branches.email') }}:</strong> {{ $branch->email ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Users Card -->
        <div class="col-12 col-md-7 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-people text-primary"></i>{{ __('branches.assigned_users') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-3">{{ __('users.name') }}</th>
                                    <th scope="col">{{ __('users.email') }}</th>
                                    <th scope="col">{{ __('users.roles') }}</th>
                                    <th scope="col" class="pe-3">{{ __('users.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branch->mainUsers->merge($branch->users)->unique('id') as $user)
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @foreach ($user->roles as $role)
                                                <span class="badge bg-info text-dark me-1">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td class="pe-3">
                                            @if ($user->is_active)
                                                <span class="badge bg-success-subtle text-success">{{ __('users.active') }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">{{ __('users.inactive') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            لا يوجد موظفون مخصصون لهذا الفرع حالياً.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
