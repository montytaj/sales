<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('payments.cheques_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <h2 class="h4 mb-0 font-bold text-dark">
            <i class="bi bi-bank2 text-primary me-2"></i>{{ __('payments.cheques_list') }}
        </h2>
    </x-slot>

    <!-- Search & Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('cheques.index') }}" class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم الشيك، اسم البنك، أو اسم الساحب..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- كافة حالات الشيكات --</option>
                        @foreach (__('payments.cheque_statuses') as $statusKey => $statusName)
                            <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>{{ $statusName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('cheques.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Cheques Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('payments.cheque_number') }}</th>
                            <th scope="col">{{ __('payments.bank_name') }}</th>
                            <th scope="col">{{ __('payments.drawer_name') }}</th>
                            <th scope="col">{{ __('payments.due_date') }}</th>
                            <th scope="col">{{ __('payments.amount') }}</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cheques as $cheque)
                            <tr>
                                <td class="ps-3"><strong class="text-primary"><code>{{ $cheque->cheque_number }}</code></strong></td>
                                <td class="fw-semibold">{{ $cheque->bank_name }}</td>
                                <td>{{ $cheque->drawer_name }}</td>
                                <td>{{ $cheque->due_date->format('Y-m-d') }}</td>
                                <td><strong class="text-success">{{ number_format($cheque->amount, 2) }} {{ setting('currency', 'SAR') }}</strong></td>
                                <td>
                                    <span class="badge {{ $cheque->status === 'collected' ? 'bg-success' : ($cheque->status === 'returned' ? 'bg-danger' : 'bg-primary') }}">
                                        {{ __('payments.cheque_statuses.' . $cheque->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('cheques.show', $cheque) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>عرض وتغيير الحالة
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-bank fs-3 d-block mb-2"></i>
                                    لا توجد شيكات مسجلة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($cheques->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $cheques->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
