<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('projects.contracts')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>{{ __('projects.contracts') }}
            </h2>
            @can('create-contracts')
                <a href="{{ route('contracts.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('projects.create_contract') }}</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contracts.index') }}" class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم العقد، نطاق العمل، أو اسم العميل..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- كافة حالات العقود --</option>
                        @foreach (__('projects.contract_statuses') as $sKey => $sName)
                            <option value="{{ $sKey }}" {{ request('status') === $sKey ? 'selected' : '' }}>{{ $sName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('projects.contract_number') }}</th>
                            <th scope="col">{{ __('customers.name') }}</th>
                            <th scope="col">{{ __('projects.net_amount') }}</th>
                            <th scope="col">تاريخ البدء والانتهاء</th>
                            <th scope="col">الاعتماد</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contracts as $contract)
                            <tr>
                                <td class="ps-3"><code>{{ $contract->contract_number }}</code></td>
                                <td class="fw-semibold">{{ $contract->customer->name }}</td>
                                <td class="fw-bold text-success">{{ number_format($contract->net_amount, 2) }} {{ setting('currency', 'SDG') }}</td>
                                <td>
                                    <small class="d-block">{{ $contract->start_date->format('Y-m-d') }}</small>
                                    <small class="text-muted">{{ $contract->end_date ? $contract->end_date->format('Y-m-d') : 'مفتوح' }}</small>
                                </td>
                                <td>
                                    @if ($contract->is_approved)
                                        <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle me-1"></i>معتمد</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-hourglass-split me-1"></i>بانتظار الاعتماد</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary">
                                        {{ __('projects.contract_statuses.' . $contract->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('contracts.show', $contract) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>عرض التفاصيل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد عقود مسجلة في النظام.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($contracts->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
