<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('purchases.index'), 'label' => __('المشتريات')],
                ['label' => 'سداد الآجل ومستحقات الموردين']
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-hourglass-split text-danger me-2"></i>مستحقات الموردين والفواتير الآجلة
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('payments.create', ['type' => 'payment']) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>سند صرف جديد
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Metrics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-danger bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-danger-subtle text-danger p-3 rounded-3 me-3">
                            <i class="bi bi-cash-stack fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block small font-semibold">إجمالي ديون الموردين (المتبقي):</span>
                            <h3 class="mb-0 font-bold text-danger font-mono">{{ number_format($totalOutstanding, 2) }} {{ setting('currency', 'ر.س') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-warning bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning-subtle text-warning p-3 rounded-3 me-3">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block small font-semibold">فواتير مسددة جزئياً:</span>
                            <h3 class="mb-0 font-bold text-warning font-mono">{{ $partiallyPaidCount }} فاتورة</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-secondary bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-light text-secondary p-3 rounded-3 me-3">
                            <i class="bi bi-x-circle fs-3"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block small font-semibold">فواتير غير مسددة بالكامل:</span>
                            <h3 class="mb-0 font-bold text-secondary font-mono">{{ $unpaidCount }} فاتورة</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('purchases.payables') }}" class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث برقم الفاتورة أو اسم المورد..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <select name="supplier_id" class="form-select">
                        <option value="">-- تصفية بحسب المورد --</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    @if (request()->hasAny(['search', 'supplier_id']))
                        <a href="{{ route('purchases.payables') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">الفاتورة</th>
                            <th scope="col">المورد</th>
                            <th scope="col">تاريخ الفاتورة</th>
                            <th scope="col" class="text-end">إجمالي الفاتورة</th>
                            <th scope="col" class="text-end">المدفوع سابقاً</th>
                            <th scope="col" class="text-end">المتبقي (الآجل)</th>
                            <th scope="col">الحالة</th>
                            <th scope="col" class="text-end pe-3">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payables as $inv)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('purchases.show_invoice', $inv) }}" class="fw-bold text-primary font-mono text-decoration-none">
                                        <i class="bi bi-receipt me-1"></i>{{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <strong class="text-dark d-block">{{ $inv->supplier?->name ?? '-' }}</strong>
                                    @if($inv->supplier?->phone)
                                        <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $inv->supplier->phone }}</small>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('Y-m-d') }}</td>
                                <td class="text-end font-mono fw-semibold">{{ number_format($inv->net_amount, 2) }} {{ setting('currency', 'ر.س') }}</td>
                                <td class="text-end font-mono text-success">{{ number_format($inv->total_paid, 2) }} {{ setting('currency', 'ر.س') }}</td>
                                <td class="text-end font-mono fw-bold fs-6 text-danger">
                                    {{ number_format($inv->due_amount, 2) }} {{ setting('currency', 'ر.س') }}
                                </td>
                                <td>
                                    @if($inv->status == 'partially_paid')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>مدفوعة جزئياً</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>غير مدفوعة (آجل)</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="{{ route('purchases.pay_invoice', $inv) }}" class="btn btn-sm btn-success fw-bold px-3">
                                        <i class="bi bi-wallet2 me-1"></i>سداد المستحق ({{ number_format($inv->due_amount, 2) }})
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle-fill fs-2 text-success d-block mb-2"></i>
                                    لا توجد مستحقات أو فواتير مشتريات قائمة غير مسددة حالياً. ممتاز!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($payables->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $payables->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
