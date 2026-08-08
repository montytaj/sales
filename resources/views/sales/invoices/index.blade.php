<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('sales.invoices_list')]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-receipt-cutoff text-primary me-2"></i>{{ __('sales.invoices_list') ?? 'سجل فواتير المبيعات' }}
                </h2>
                <p class="text-muted fs-7 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'استعراض ومتابعة فواتير المبيعات، المدفوعات، وحالات التحصيل' : 'View and track sales invoices and payment statuses' }}
                </p>
            </div>
            <div>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary font-bold rounded-3 px-3.5 py-2 shadow-sm">
                    <i class="bi bi-plus-circle me-1.5"></i>{{ app()->getLocale() == 'ar' ? 'إنشاء فاتورة مبيعات' : 'New Sales Invoice' }}
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Search & Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('invoices.index') }}" class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم الفاتورة أو اسم العميل..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- كافة حالات الفواتير --</option>
                        @foreach (__('sales.invoice_statuses') as $statusKey => $statusName)
                            <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>{{ $statusName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">{{ __('sales.invoice_number') }}</th>
                            <th scope="col">{{ __('customers.name') }}</th>
                            <th scope="col">{{ __('sales.issue_date') }}</th>
                            <th scope="col">{{ __('sales.due_date') }}</th>
                            <th scope="col">{{ __('sales.total_amount') }}</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="ps-3"><code>{{ $invoice->invoice_number }}</code></td>
                                <td class="fw-semibold">{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->issue_date->format('Y-m-d') }}</td>
                                <td>{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</td>
                                <td><strong class="text-success">{{ number_format($invoice->total_amount, 2) }} {{ setting('currency', 'SDG') }}</strong></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary">
                                        {{ __('sales.invoice_statuses.' . $invoice->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-dark" title="طباعة">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-receipt fs-3 d-block mb-2"></i>
                                    لا توجد فواتير مبيعات مسجلة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($invoices->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
