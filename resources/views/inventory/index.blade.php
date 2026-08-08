<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-box-seam-fill text-primary me-2"></i>{{ __('inventory.title') }}
                </h2>
                <p class="text-muted fs-7 mb-0">{{ __('inventory.inventory_management') }}</p>
            </div>
            <a href="{{ route('inventory.create') }}" class="btn btn-primary font-bold rounded-3 px-3 py-2 fs-7 shadow-sm">
                <i class="bi bi-plus-circle me-1.5"></i>{{ __('inventory.add_new_item') }}
            </a>
        </div>
    </x-slot>

    <!-- Main Card Container (Constrained width to avoid page horizontal scroll) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="max-width: 100%;">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive w-100 overflow-x-auto" style="max-width: 100%; -webkit-overflow-scrolling: touch;">
                <table class="table table-hover align-middle datatable w-100 mb-0">
                    <thead class="table-light fs-7">
                        <tr>
                            <th scope="col" style="width: 50px;">#</th>
                            <th scope="col">{{ __('inventory.item_code') }}</th>
                            <th scope="col">{{ __('inventory.item_name') }}</th>
                            <th scope="col">{{ __('inventory.category') }}</th>
                            <th scope="col">{{ __('inventory.units') }}</th>
                            <th scope="col">{{ __('inventory.cost_price') }}</th>
                            <th scope="col">{{ __('inventory.prices') }}</th>
                            <th scope="col">{{ __('inventory.total_stock') }}</th>
                            <th scope="col">{{ __('general.status') }}</th>
                            <th scope="col" class="text-end pe-3">{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-slate-100 text-slate-700 font-mono border px-2 py-1">{{ $item->code ?? $item->item_code }}</span>
                                    @if($item->barcode)
                                        <div class="fs-8 font-mono text-muted mt-1"><i class="bi bi-barcode me-1"></i>{{ $item->barcode }}</div>
                                    @endif
                                </td>
                                <td class="fw-bold text-slate-800">{{ $item->name }}</td>
                                <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ $item->category?->name ?? __('inventory.uncategorized') }}</span></td>
                                <td>
                                    <div class="fs-8">
                                        <span class="text-primary font-bold">{{ __('inventory.wholesale_unit') }}:</span> {{ $item->wholesaleUnit?->name ?? __('inventory.carton') }} <br>
                                        <span class="text-secondary font-bold">{{ __('inventory.base_unit') }}:</span> {{ $item->baseUnit?->name ?? __('inventory.piece') }} <br>
                                        <small class="text-muted">(1 = {{ $item->conversion_factor }})</small>
                                    </div>
                                </td>
                                <td class="fw-bold font-mono text-slate-800">{{ number_format($item->cost_price, 2) }} {{ setting('currency', 'SAR') }}</td>
                                <td>
                                    <div class="fs-8 font-mono">
                                        <span class="text-success font-bold">{{ __('inventory.wholesale_price') }}:</span> {{ number_format($item->wholesale_price, 2) }} <br>
                                        <span class="text-primary font-bold">{{ __('inventory.retail_price') }}:</span> {{ number_format($item->retail_price, 2) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 font-mono">
                                        {{ $item->formatted_stock }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">{{ __('inventory.available') }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">{{ __('inventory.disabled') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('inventory.edit', $item) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2.5 py-1" title="{{ __('general.edit') }}">
                                            <i class="bi bi-pencil me-1"></i>{{ __('general.edit') }}
                                        </a>
                                        <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('general.confirm_delete') ?? 'هل أنت تأكد من الحذف؟' }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger rounded-3 px-2.5 py-1" title="{{ __('general.delete') }}">
                                                <i class="bi bi-trash me-1"></i>{{ __('general.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">{{ __('general.no_records_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>


