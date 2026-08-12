<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 mb-0 font-bold text-gray-800">
                <i class="bi bi-arrow-left-right text-primary me-2"></i>{{ __('transfers.title') }}
            </h2>
            @can('create-warehouse-transfers')
                <a href="{{ route('warehouse-transfers.create') }}" class="btn btn-primary shadow-sm rounded-3">
                    <i class="bi bi-plus-circle me-1"></i>{{ __('transfers.new_transfer') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <!-- Filter Bar -->
            <form method="GET" action="{{ route('warehouse-transfers.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label text-muted fs-7 fw-semibold mb-1">{{ __('general.search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 bg-light" placeholder="{{ __('transfers.transfer_number') }} أو اسم المخزن...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted fs-7 fw-semibold mb-1">{{ __('transfers.from_warehouse') }}</label>
                    <select name="from_warehouse_id" class="form-select bg-light">
                        <option value="">{{ __('transfers.all_warehouses') }}</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('from_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted fs-7 fw-semibold mb-1">{{ __('transfers.to_warehouse') }}</label>
                    <select name="to_warehouse_id" class="form-select bg-light">
                        <option value="">{{ __('transfers.all_warehouses') }}</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted fs-7 fw-semibold mb-1">{{ __('transfers.status') }}</label>
                    <select name="status" class="form-select bg-light">
                        <option value="">{{ __('transfers.all_statuses') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('transfers.pending') }}</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('transfers.completed') }}</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('transfers.cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i></button>
                </div>
            </form>

            <!-- Transfers Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th>#</th>
                            <th>{{ __('transfers.transfer_number') }}</th>
                            <th>{{ __('transfers.from_warehouse') }}</th>
                            <th></th>
                            <th>{{ __('transfers.to_warehouse') }}</th>
                            <th>{{ __('transfers.items_count') }}</th>
                            <th>{{ __('transfers.transfer_date') }}</th>
                            <th>{{ __('transfers.created_by') }}</th>
                            <th>{{ __('transfers.status') }}</th>
                            <th class="text-center">{{ __('transfers.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $t)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('warehouse-transfers.show', $t) }}" class="fw-bold text-primary text-decoration-none">
                                        <i class="bi bi-file-earmark-arrow-right me-1"></i>{{ $t->transfer_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>{{ $t->fromWarehouse?->name }}
                                    </span>
                                </td>
                                <td class="text-muted"><i class="bi bi-arrow-right fs-6"></i></td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1">
                                        <i class="bi bi-box-arrow-in-down-left me-1"></i>{{ $t->toWarehouse?->name }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill">
                                        {{ $t->items_count }} {{ app()->getLocale() == 'ar' ? 'صنف' : 'items' }}
                                    </span>
                                </td>
                                <td><span class="font-mono text-muted fs-7">{{ $t->transfer_date?->format('Y-m-d') }}</span></td>
                                <td>{{ $t->creator?->name ?? '-' }}</td>
                                <td>{!! $t->status_badge !!}</td>
                                <td class="text-center text-nowrap">
                                    <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('warehouse-transfers.show', $t) }}" class="btn btn-action-icon btn-action-show" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('warehouse-transfers.print', $t) }}" target="_blank" class="btn btn-action-icon btn-action-print" title="طباعة">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                         @if($t->status === 'pending')
                                             @can('approve-warehouse-transfers')
                                                 <form action="{{ route('warehouse-transfers.complete', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('transfers.approve_confirm') }}');">
                                                     @csrf
                                                     <button type="submit" class="btn btn-action-icon btn-action-success" title="اعتماد التحويل">
                                                         <i class="bi bi-check-lg"></i>
                                                     </button>
                                                 </form>
                                             @endcan
                                         @endif
                                         @if(in_array($t->status, ['pending', 'completed']))
                                             @can('delete-warehouse-transfers')
                                                 <form action="{{ route('warehouse-transfers.cancel', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $t->status === 'completed' ? 'هل أنت تأكد من رغبتك في عكس وإلغاء هذا التحويل المخزني المكتمل وإعادة الكميات للمخزن المصدر؟' : __('transfers.cancel_confirm') }}');">
                                                     @csrf
                                                     <button type="submit" class="btn btn-action-icon btn-action-delete" title="{{ $t->status === 'completed' ? 'عكس وإلغاء التحويل' : 'إلغاء التحويل' }}">
                                                         <i class="bi {{ $t->status === 'completed' ? 'bi-arrow-counterclockwise' : 'bi-x-lg' }}"></i>
                                                     </button>
                                                 </form>
                                             @endcan
                                         @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-arrow-left-right fs-1 d-block mb-2 text-slate-300"></i>
                                    {{ __('transfers.no_transfers') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
