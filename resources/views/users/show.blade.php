<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('users.index'), 'label' => __('users.users_list')],
                ['label' => __('users.show_user')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-person-badge-fill text-primary me-2"></i>{{ __('users.show_user') }}: {{ $user->name }}
            </h2>
            @can('edit-users')
                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary d-flex align-items-center gap-1">
                    <i class="bi bi-pencil"></i> {{ __('users.edit_user') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- User Information Card -->
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 bg-primary-subtle text-primary rounded-circle mb-3">
                        <i class="bi bi-person-fill fs-1"></i>
                    </div>
                    <h4 class="font-bold text-dark mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    <div class="mb-3">
                        @if ($user->is_active)
                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">
                                <i class="bi bi-check-circle-fill me-1"></i>{{ __('users.active') }}
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6">
                                <i class="bi bi-x-circle-fill me-1"></i>{{ __('users.inactive') }}
                            </span>
                        @endif
                    </div>

                    <hr class="my-3 text-secondary">

                    <div class="text-start">
                        <h6 class="font-semibold text-secondary mb-2">{{ __('users.roles') }}:</h6>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse ($user->roles as $role)
                                @php
                                    $roleLabel = Lang::has('roles.' . $role->name) ? __('roles.' . $role->name) : $role->name;
                                @endphp
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle p-2 font-medium">{{ $roleLabel }}</span>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log Card -->
        <div class="col-12 col-md-7 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-journal-text text-primary"></i>{{ __('users.activity_log') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-3">{{ __('users.action') }}</th>
                                    <th scope="col">{{ __('users.details') }}</th>
                                    <th scope="col">{{ __('users.ip_address') }}</th>
                                    <th scope="col" class="pe-3">{{ __('users.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($user->activityLogs as $log)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-secondary-subtle text-dark border">{{ $log->action }}</span>
                                        </td>
                                        <td>{{ $log->description ?? '-' }}</td>
                                        <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                                        <td class="pe-3 text-muted small">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="bi bi-clock-history fs-4 d-block mb-1"></i>
                                            لا يوجد سجل نشاطات مسجل لهذا المستخدم بعد.
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
