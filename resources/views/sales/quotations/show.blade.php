<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('quotations.index'), 'label' => __('sales.quotations_list')],
                ['label' => $quotation->quotation_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <h2 class="h4 mb-0 font-bold text-dark">
                    <i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i>عرض سعر: {{ $quotation->quotation_number }}
                </h2>
                @if ($quotation->is_approved)
                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6"><i class="bi bi-check-circle me-1"></i>معتمد</span>
                @else
                    <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 fs-6"><i class="bi bi-clock me-1"></i>غير معتمد</span>
                @endif
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="btn btn-outline-dark">
                    <i class="bi bi-printer me-1"></i>{{ __('sales.print_document') }}
                </a>

                @can('approve-quotations')
                    @if (!$quotation->is_approved)
                        <form method="POST" action="{{ route('quotations.approve', $quotation) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>{{ __('sales.approve_action') }}
                            </button>
                        </form>
                    @endif
                @endcan

                @can('convert-quotation-to-invoice')
                    @if (!$quotation->invoice)
                        <form method="POST" action="{{ route('quotations.convert-to-invoice', $quotation) }}" class="d-inline" onsubmit="return confirm('هل أنت تأكد من تحويل عرض السعر إلى فاتورة مبيعات؟');">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-receipt me-1"></i>{{ __('sales.convert_to_invoice') }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('invoices.show', $quotation->invoice) }}" class="btn btn-outline-info">
                            <i class="bi bi-receipt me-1"></i>عرض الفاتورة الصادرة
                        </a>
                    @endif
                @endcan

                @can('edit-quotations')
                    @if (!$quotation->is_approved || auth()->user()->can('approve-quotations'))
                        <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>تعديل
                        </a>
                    @endif
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
                    <h5 class="font-bold text-dark mb-0">{{ $quotation->customer->name }}</h5>
                    <small class="text-muted">{{ $quotation->customer->phone }} | {{ $quotation->customer->city }}</small>
                </div>

                <div class="col-12 col-md-3">
                    <span class="text-muted d-block mb-1">{{ __('sales.issue_date') }}</span>
                    <strong class="text-dark fs-6">{{ $quotation->issue_date->format('Y-m-d') }}</strong>
                </div>

                <div class="col-12 col-md-3">
                    <span class="text-muted d-block mb-1">{{ __('sales.expiry_date') }}</span>
                    <strong class="text-dark fs-6">{{ $quotation->expiry_date?->format('Y-m-d') ?? '-' }}</strong>
                </div>

                <div class="col-12 col-md-2">
                    <span class="text-muted d-block mb-1">المحاضر / المنشئ</span>
                    <strong class="text-dark fs-6">{{ $quotation->creator?->name ?? 'النظام' }}</strong>
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
                        @foreach ($quotation->items as $index => $item)
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

    <!-- Totals Summary & Terms -->
    <div class="row g-4">
        <div class="col-12 col-md-7">
            @if ($quotation->terms_conditions)
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 font-bold text-dark"><i class="bi bi-file-earmark-text text-primary me-2"></i>{{ __('sales.terms_and_conditions') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        <p class="mb-0 text-muted small" style="white-space: pre-line;">{{ $quotation->terms_conditions }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-12 col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('sales.subtotal') }}:</span>
                        <strong class="text-dark">{{ number_format($quotation->subtotal, 2) }} {{ setting('currency', 'SDG') }}</strong>
                    </div>

                    @if ($quotation->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>{{ __('sales.discount') }}:</span>
                            <strong>- {{ number_format($quotation->discount_amount, 2) }} {{ setting('currency', 'SDG') }}</strong>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>{{ __('sales.tax_amount') }}:</span>
                        <strong>+ {{ number_format($quotation->tax_amount, 2) }} {{ setting('currency', 'SDG') }}</strong>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center py-2">
                        <h5 class="mb-0 font-bold text-dark">{{ __('sales.total_amount') }}:</h5>
                        <h3 class="mb-0 font-bold text-success">{{ number_format($quotation->total_amount, 2) }} {{ setting('currency', 'SDG') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
