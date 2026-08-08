<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('users.index'), 'label' => __('users.users_list')],
                ['label' => __('users.edit_user')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-pencil-square text-primary me-2"></i>{{ __('users.edit_user') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label font-semibold">{{ __('users.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label font-semibold">{{ __('users.email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password (Optional) -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label font-semibold">{{ __('users.password') }} <small class="text-muted">(اتركه فارغاً للإبقاء عليها)</small></label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="password_confirmation" class="form-label font-semibold">{{ __('users.password_confirmation') }}</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                            </div>
                        </div>

                        <!-- Roles Checkboxes -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label font-semibold mb-0">{{ __('users.roles') }} <span class="text-danger">*</span></label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary py-0.5 px-2 text-xs" id="btnSelectAllRoles">
                                        <i class="bi bi-check-all me-1"></i>تحديد جميع الأدوار
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary py-0.5 px-2 text-xs" id="btnDeselectAllRoles">
                                        <i class="bi bi-x-lg me-1"></i>إلغاء الكل
                                    </button>
                                </div>
                            </div>
                            <div class="row g-2 p-3 bg-light rounded border">
                                @foreach ($roles as $role)
                                    @if ($role->name === 'system-admin' && !auth()->user()->hasRole('system-admin'))
                                        @continue
                                    @endif
                                    @php
                                        $roleLabel = Lang::has('roles.' . $role->name) ? __('roles.' . $role->name) : $role->name;
                                    @endphp
                                    <div class="col-12 col-md-4">
                                        <div class="form-check p-2 bg-white rounded border">
                                            <input class="form-check-input user-role-cb" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}" {{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold text-dark cursor-pointer w-100" for="role_{{ $role->id }}">
                                                {{ $roleLabel }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Branch Assignment -->
                        <div class="mb-4">
                            <label for="main_branch_id" class="form-label font-semibold">{{ __('branches.main_branch_for_user') }}</label>
                            <select name="main_branch_id" id="main_branch_id" class="form-select mb-3">
                                <option value="">-- اختر الفرع الرئيسي --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('main_branch_id', $user->main_branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} ({{ $branch->code }})
                                    </option>
                                @endforeach
                            </select>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label font-semibold mb-0">{{ __('branches.additional_branches') }}</label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary py-0.5 px-2 text-xs" id="btnSelectAllBranches">
                                        <i class="bi bi-check-all me-1"></i>تحديد جميع الفروع
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary py-0.5 px-2 text-xs" id="btnDeselectAllBranches">
                                        <i class="bi bi-x-lg me-1"></i>إلغاء الكل
                                    </button>
                                </div>
                            </div>
                            <div class="row g-2 p-3 bg-light rounded border">
                                @foreach ($branches as $branch)
                                    <div class="col-12 col-md-6">
                                        <div class="form-check p-2 bg-white rounded border">
                                            <input class="form-check-input user-branch-cb" type="checkbox" name="branches[]" value="{{ $branch->id }}" id="branch_{{ $branch->id }}" {{ in_array($branch->id, old('branches', $user->branches->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label cursor-pointer w-100 text-dark" for="branch_{{ $branch->id }}">
                                                {{ $branch->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Active Status Checkbox -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $user->is_active) ? 'checked' : '' }} {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                                <label class="form-check-label font-semibold" for="is_active">{{ __('users.active') }}</label>
                                @if (auth()->id() === $user->id)
                                    <input type="hidden" name="is_active" value="1">
                                    <small class="text-muted d-block ms-1">({{ __('users.cannot_deactivate_self') }})</small>
                                @endif
                            </div>
                        </div>

                        <!-- Submit and Cancel Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('users.index') }}" class="btn btn-light border">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>تحديث البيانات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('btnSelectAllRoles')?.addEventListener('click', function() {
                document.querySelectorAll('.user-role-cb').forEach(cb => cb.checked = true);
            });
            document.getElementById('btnDeselectAllRoles')?.addEventListener('click', function() {
                document.querySelectorAll('.user-role-cb').forEach(cb => cb.checked = false);
            });

            document.getElementById('btnSelectAllBranches')?.addEventListener('click', function() {
                document.querySelectorAll('.user-branch-cb').forEach(cb => cb.checked = true);
            });
            document.getElementById('btnDeselectAllBranches')?.addEventListener('click', function() {
                document.querySelectorAll('.user-branch-cb').forEach(cb => cb.checked = false);
            });
        });
    </script>
</x-app-layout>
