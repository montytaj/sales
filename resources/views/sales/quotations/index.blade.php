<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('sales.quotations_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-file-earmark-spreadsheet-fill text-primary me-2"></i>{{ __('sales.quotations_list') }}
            </h2>
            @can('create-quotations')
                <a href="{{ route('quotations.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('sales.create_quotation') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Search & Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('quotations.index') }}" class="row g-3">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم عرض السعر أو اسم العميل..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- كافة الحالات --</option>
                        @foreach (__('sales.quotation_statuses') as $statusKey => $statusName)
                            <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>{{ $statusName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select name="approved" class="form-select">
                        <option value="">-- حالة الاعتماد --</option>
                        <option value="1" {{ request('approved') === '1' ? 'selected' : '' }}>{{ __('sales.approved') }}</option>
                        <option value="0" {{ request('approved') === '0' ? 'selected' : '' }}>{{ __('sales.not_approved') }}</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'status', 'approved']))
                        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Quotations Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('sales.quotation_number') }}</th>
                            <th scope="col">{{ __('customers.name') }}</th>
                            <th scope="col">{{ __('sales.issue_date') }}</th>
                            <th scope="col">{{ __('sales.total_amount') }}</th>
                            <th scope="col">الحالة</th>
                            <th scope="col">{{ __('sales.approval_status') }}</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quotations as $quotation)
                            <tr>
                                <td class="ps-3"><code>{{ $quotation->quotation_number }}</code></td>
                                <td class="fw-semibold">
                                    <a href="{{ route('quotations.show', $quotation) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $quotation->customer->name }}
                                    </a>
                                </td>
                                <td>{{ $quotation->issue_date->format('Y-m-d') }}</td>
                                <td><strong class="text-success">{{ number_format($quotation->total_amount, 2) }} {{ setting('currency', 'SDG') }}</strong></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary">
                                        {{ __('sales.quotation_statuses.' . $quotation->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($quotation->is_approved)
                                        <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle me-1"></i>{{ __('sales.approved') }}</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-clock me-1"></i>{{ __('sales.not_approved') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-outline-secondary" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="btn btn-outline-dark" title="طباعة">
                                            <i class="bi bi-printer"></i>
                                        </a>

                                        @can('edit-quotations')
                                            <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-outline-primary" title="تعديل">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-file-earmark-x fs-3 d-block mb-2"></i>
                                    لا توجد عروض أسعار مسجلة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($quotations->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $quotations->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
