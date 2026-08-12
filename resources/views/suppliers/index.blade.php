<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('suppliers.suppliers_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-truck-front-fill text-primary me-2"></i>{{ __('suppliers.suppliers_list') }}
            </h2>
            @can('create-suppliers')
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('suppliers.create_supplier') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Search & Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('suppliers.index') }}" class="row g-3">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث بالاسم، الكود، مسئول الاتصال، أو الخدمات الموردة..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <select name="rating" class="form-select">
                        <option value="">-- تصفية التقييم --</option>
                        <option value="5" {{ request('rating') == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 نجوم)</option>
                        <option value="4" {{ request('rating') == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ (4 نجوم)</option>
                        <option value="3" {{ request('rating') == 3 ? 'selected' : '' }}>⭐⭐⭐ (3 نجوم)</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- جميع الحالات --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('suppliers.active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('suppliers.inactive') }}</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'rating', 'status']))
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('suppliers.name') }}</th>
                            <th scope="col">{{ __('suppliers.contact_person') }}</th>
                            <th scope="col">{{ __('suppliers.phone') }}</th>
                            <th scope="col">{{ __('suppliers.rating') }}</th>
                            <th scope="col">{{ __('suppliers.status') }}</th>
                            <th scope="col" class="text-end pe-3">{{ __('suppliers.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="ps-3 fw-semibold">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $supplier->name }}
                                        @if ($supplier->company_name)
                                            <small class="text-muted d-block">{{ $supplier->company_name }}</small>
                                        @endif
                                    </a>
                                </td>
                                <td>{{ $supplier->contact_person ?? '-' }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>
                                    <span class="text-warning">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $supplier->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </span>
                                </td>
                                <td>
                                    @if ($supplier->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success">{{ __('suppliers.active') }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger">{{ __('suppliers.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-action-icon btn-action-show" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @can('edit-suppliers')
                                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-action-icon btn-action-edit" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('delete-suppliers')
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="d-inline" onsubmit="return confirm('هل أنت تأكد من رغبتك في حذف هذا المورد؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-action-icon btn-action-delete" title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-truck fs-3 d-block mb-2"></i>
                                    {{ __('suppliers.no_suppliers_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($suppliers->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
