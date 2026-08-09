<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('invoices.index'), 'label' => __('sales.invoices_list')],
                ['label' => $invoice->invoice_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-receipt text-primary me-2"></i>فاتورة مبيعات ضريبية: {{ $invoice->invoice_number }}
            </h2>

            <div class="d-flex gap-2">
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-dark">
                    <i class="bi bi-printer me-1"></i>{{ __('sales.print_document') }}
                </a>

                @can('edit-invoices')
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            تحديث حالة الفاتورة
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                            @foreach (__('sales.invoice_statuses') as $statusKey => $statusName)
                                <li>
                                    <form method="POST" action="{{ route('invoices.update-status', $invoice) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $statusKey }}">
                                        <button type="submit" class="dropdown-item">{{ $statusName }}</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- Details Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <span class="text-muted d-block mb-1">{{ __('customers.name') }}</span>
                    <h5 class="font-bold text-dark mb-0">{{ $invoice->customer->name }}</h5>
                    <small class="text-muted">{{ $invoice->customer->phone }} | {{ $invoice->customer->city }}</small>
                </div>

                <div class="col-12 col-md-3">
                    <span class="text-muted d-block mb-1">{{ __('sales.issue_date') }}</span>
                    <strong class="text-dark fs-6">{{ $invoice->issue_date->format('Y-m-d') }}</strong>
                </div>

                <div class="col-12 col-md-3">
                    <span class="text-muted d-block mb-1">{{ __('sales.due_date') }}</span>
                    <strong class="text-dark fs-6">{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</strong>
                </div>

                <div class="col-12 col-md-2">
                    <span class="text-muted d-block mb-1">حالة الفاتورة</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary fs-6">
                        {{ __('sales.invoice_statuses.' . $invoice->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 font-bold text-dark"><i class="bi bi-list-task me-2"></i>{{ __('sales.items') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">{{ __('sales.item_name') }}</th>
                            <th scope="col" class="text-center">{{ __('sales.quantity') }}</th>
                            <th scope="col" class="text-center">{{ __('sales.unit') }}</th>
                            <th scope="col" class="text-end">{{ __('sales.unit_price') }}</th>
                            <th scope="col" class="text-end">{{ __('sales.item_discount') }}</th>
                            <th scope="col" class="text-end">{{ __('sales.tax_amount') }}</th>
                            <th scope="col" class="text-end pe-3">{{ __('sales.item_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $index => $item)
                            <tr>
                                <th scope="row" class="ps-3">{{ $index + 1 }}</th>
                                <td>
                                    <strong class="text-dark d-block">{{ $item->item_name }}</strong>
                                    @if ($item->description)
                                        <small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-center"><span class="badge bg-light text-dark border">{{ __('services.units.' . $item->unit_of_measure) }}</span></td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }} {{ setting('currency', 'SDG') }}</td>
                                <td class="text-end text-danger">{{ number_format($item->discount_amount, 2) }} {{ setting('currency', 'SDG') }}</td>
                                <td class="text-end text-muted">{{ number_format($item->tax_amount, 2) }} {{ setting('currency', 'SDG') }}</td>
                                <td class="text-end pe-3 fw-bold text-success">{{ number_format($item->total, 2) }} {{ setting('currency', 'SDG') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Totals Summary -->
    <div class="row justify-content-end">
        <div class="col-12 col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('sales.subtotal') }}:</span>
                        <strong class="text-dark">{{ number_format($invoice->subtotal, 2) }} {{ setting('currency', 'SDG') }}</strong>
                    </div>

                    @if ($invoice->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>{{ __('sales.discount') }}:</span>
                            <strong>- {{ number_format($invoice->discount_amount, 2) }} {{ setting('currency', 'SDG') }}</strong>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>{{ __('sales.tax_amount') }}:</span>
                        <strong>+ {{ number_format($invoice->tax_amount, 2) }} {{ setting('currency', 'SDG') }}</strong>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center py-2">
                        <h5 class="mb-0 font-bold text-dark">{{ __('sales.total_amount') }}:</h5>
                        <h3 class="mb-0 font-bold text-success">{{ number_format($invoice->total_amount, 2) }} {{ setting('currency', 'SDG') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
