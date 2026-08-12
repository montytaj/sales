<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('users.index'), 'label' => __('users.title')],
                ['url' => route('roles.index'), 'label' => __('roles.roles_list')],
                ['label' => __('roles.matrix_title')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 w-100">
            <div>
                <h2 class="h4 mb-1 font-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
                    <span>{{ __('roles.matrix_title') }}</span>
                </h2>
                <p class="text-muted small mb-0">{{ __('roles.matrix_desc') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-right"></i>
                    <span>عودة للأدوار</span>
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .matrix-table-wrapper {
            max-height: 70vh;
            overflow-y: auto;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
        }
        .matrix-sticky-col {
            position: sticky;
            right: 0;
            background-color: #ffffff !important;
            z-index: 5;
            box-shadow: -2px 0 5px rgba(0,0,0,0.05);
        }
        html[dir="ltr"] .matrix-sticky-col {
            right: auto;
            left: 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        .matrix-col-head {
            min-width: 260px;
        }
        @media (max-width: 575.98px) {
            .matrix-col-head {
                min-width: 170px !important;
                max-width: 180px !important;
            }
            .matrix-sticky-col {
                max-width: 180px !important;
                font-size: 0.8rem;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            .matrix-cb {
                width: 1.25rem;
                height: 1.25rem;
                cursor: pointer;
            }
        }
    </style>

    <form method="POST" action="{{ route('roles.matrix.update') }}" id="matrixForm">
        @csrf

        <!-- Matrix Header Sticky Bar -->
        <div class="card border-0 shadow-sm mb-4 rounded-3 bg-white sticky-top shadow-md" style="top: 70px; z-index: 1020;">
            <div class="card-body p-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle fs-6">
                        <i class="bi bi-sliders"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">لوحة التحكم السريعة بالمصفوفة</h6>
                        <span class="text-muted fs-7 d-none d-sm-inline">قم بزيادة أو إزالة الصلاحيات لكل دور بالضغط المباشر على مربع الاختيار ثم اضغط حفظ.</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-end flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary py-2 px-3 fw-semibold d-inline-flex align-items-center gap-1" id="btnSelectAllMatrix">
                        <i class="bi bi-check-all fs-5"></i>
                        <span>تحديد كافة المصفوفة</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-2 px-3 fw-semibold d-inline-flex align-items-center gap-1" id="btnDeselectAllMatrix">
                        <i class="bi bi-x-lg fs-6"></i>
                        <span>إلغاء التحديد</span>
                    </button>
                    <button type="submit" class="btn btn-primary px-4 py-2 font-bold d-inline-flex align-items-center gap-2 shadow-sm ms-auto">
                        <i class="bi bi-floppy-fill fs-5"></i>
                        <span>{{ __('roles.save_matrix') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Role Permissions Matrix Table -->
        <div class="card card-custom border-0 shadow-sm mb-5">
            <div class="card-body p-0">
                <div class="bg-light p-2 text-center text-muted fs-8 d-block d-md-none border-bottom">
                    <i class="bi bi-arrows-left-right me-1"></i>يمكنك السحب أفقياً لعرض باقي الأدوار والصلاحيات
                </div>
                <div class="table-responsive matrix-table-wrapper">
                    <table class="table table-bordered table-hover align-middle mb-0 matrix-table">
                        <thead class="bg-dark text-white sticky-top" style="top: 0; z-index: 10;">
                            <tr>
                                <th scope="col" class="bg-dark text-white p-3 align-middle matrix-sticky-col matrix-col-head" style="z-index: 11;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>الصلاحية / الموديول</span>
                                        <i class="bi bi-key-fill text-warning fs-5"></i>
                                    </div>
                                </th>
                                @foreach ($roles as $role)
                                    @php
                                        $roleDisplayName = Lang::has('roles.' . $role->name) ? __('roles.' . $role->name) : $role->name;
                                    @endphp
                                    <th scope="col" class="bg-dark text-white text-center p-3 align-middle" style="min-width: 130px;">
                                        <div class="fw-bold fs-7 mb-2">{{ $roleDisplayName }}</div>
                                        @if ($role->name !== 'system-admin')
                                            <button type="button" class="btn btn-xs btn-outline-light py-0 px-1.5 toggle-column-btn fs-8" data-role-id="{{ $role->id }}" title="تحديد/إلغاء الكل لهذا الدور">
                                                <i class="bi bi-check2-square me-1"></i>الكل
                                            </button>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">محمي</span>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groupedPermissions as $moduleKey => $permissions)
                                @php
                                    $moduleTitle = Lang::has('permissions.modules.' . $moduleKey) ? __('permissions.modules.' . $moduleKey) : $moduleKey;
                                @endphp
                                <!-- Module Header Row -->
                                <tr class="table-secondary fw-bold">
                                    <td colspan="{{ count($roles) + 1 }}" class="py-2.5 px-3 bg-light text-primary border-bottom border-top border-primary-subtle">
                                        <i class="bi bi-folder-fill me-2"></i>{{ $moduleTitle }}
                                    </td>
                                </tr>

                                @foreach ($permissions as $permission)
                                    @php
                                        $permLabel = Lang::has('permissions.' . $permission->name) ? __('permissions.' . $permission->name) : $permission->name;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 pe-3 py-2.5 matrix-sticky-col">
                                            <span class="fw-semibold text-dark fs-7">{{ $permLabel }}</span>
                                        </td>
                                        @foreach ($roles as $role)
                                            @php
                                                $hasPermission = $role->hasPermissionTo($permission->name);
                                            @endphp
                                            <td class="text-center py-2 bg-white">
                                                <div class="form-check d-flex justify-content-center m-0">
                                                    <input class="form-check-input matrix-cb role-col-{{ $role->id }}" type="checkbox" name="matrix[{{ $role->id }}][{{ $permission->id }}]" value="1" {{ $hasPermission || $role->name === 'system-admin' ? 'checked' : '' }} {{ $role->name === 'system-admin' ? 'disabled' : '' }}>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select All Matrix
            document.getElementById('btnSelectAllMatrix')?.addEventListener('click', function() {
                document.querySelectorAll('.matrix-cb:not(:disabled)').forEach(cb => cb.checked = true);
            });

            // Deselect All Matrix
            document.getElementById('btnDeselectAllMatrix')?.addEventListener('click', function() {
                document.querySelectorAll('.matrix-cb:not(:disabled)').forEach(cb => cb.checked = false);
            });

            // Toggle Column button handler
            document.querySelectorAll('.toggle-column-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const roleId = this.getAttribute('data-role-id');
                    const checkboxes = document.querySelectorAll('.role-col-' + roleId + ':not(:disabled)');
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

                    checkboxes.forEach(cb => cb.checked = !allChecked);
                });
            });
        });
    </script>
</x-app-layout>
