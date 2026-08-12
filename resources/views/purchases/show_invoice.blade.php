<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['url' => route('purchases.index'), 'label' => __('المشتريات')],
                ['label' => __('تفاصيل فاتورة الشراء') . ': ' . $invoice->invoice_number]
            ];
        @endphp
    </x-slot>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2 no-print">
            <h2 class="h4 mb-0 font-bold text-dark">
                <i class="bi bi-receipt text-primary me-2"></i>تفاصيل فاتورة الشراء: <span class="font-mono text-primary">{{ $invoice->invoice_number }}</span>
            </h2>

            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-dark">
                    <i class="bi bi-printer me-1"></i>طباعة الفاتورة
                </button>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-right me-1"></i>الرجوع لقائمة المشتريات
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            .no-print, nav, header, sidebar, .navbar, .btn, .breadcrumb {
                display: none !important;
            }
            body {
                background-color: #fff !important;
                color: #000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                margin-bottom: 15px !important;
            }
            .table-light th {
                background-color: #f1f5f9 !important;
                color: #000 !important;
            }
            .print-header {
                display: block !important;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
        }
        .print-header {
            display: none;
        }
    </style>

    <!-- Header for Print -->
    <div class="print-header text-center">
        <h3 class="font-bold mb-1">{{ setting('facility_name', 'مؤسسة أثاث وديكور وورش CNC') }}</h3>
        <p class="mb-1 text-muted">فاتورة استلام مشتريات (Purchase Invoice)</p>
        <p class="mb-0 text-muted small">رقم الفاتورة: {{ $invoice->invoice_number }} | التاريخ: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</p>
    </div>

    <!-- Details Card -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Supplier Info -->
                <div class="col-12 col-md-4 border-end-md">
                    <span class="text-muted d-block mb-1 fs-7 font-semibold"><i class="bi bi-building me-1 text-primary"></i>بيانات المورد</span>
                    <h5 class="font-bold text-dark mb-1">{{ $invoice->supplier?->name ?? 'غير محدد' }}</h5>
                    @if($invoice->supplier?->company_name)
                        <div class="text-muted small mb-1"><i class="bi bi-briefcase me-1"></i>{{ $invoice->supplier->company_name }}</div>
                    @endif
                    @if($invoice->supplier?->phone)
                        <div class="text-muted small mb-1"><i class="bi bi-telephone me-1"></i>{{ $invoice->supplier->phone }}</div>
                    @endif
                    @if($invoice->supplier?->vat_number)
                        <div class="text-muted small"><i class="bi bi-card-text me-1"></i>الرقم الضريبي: {{ $invoice->supplier->vat_number }}</div>
                    @endif
                </div>

                <!-- Invoice Info -->
                <div class="col-12 col-md-4 border-end-md">
                    <span class="text-muted d-block mb-1 fs-7 font-semibold"><i class="bi bi-info-circle me-1 text-info"></i>معلومات الفاتورة والمخزن</span>
                    <div class="mb-2">
                        <span class="text-muted small me-2">رقم الفاتورة:</span>
                        <span class="badge bg-secondary-subtle text-secondary font-mono fs-6">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small me-2">تاريخ الفاتورة:</span>
                        <strong class="text-dark">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</strong>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small me-2">المخزن المستلم:</span>
                        <span class="badge bg-info-subtle text-info"><i class="bi bi-house me-1"></i>{{ $invoice->warehouse?->name ?? 'المخزن الرئيسي' }}</span>
                    </div>
                    <div>
                        <span class="text-muted small me-2">مُسجّل الفاتورة:</span>
                        <span class="text-dark fw-semibold">{{ $invoice->creator?->name ?? '-' }}</span>
                    </div>
                </div>

                <!-- Payment Status -->
                <div class="col-12 col-md-4">
                    <span class="text-muted d-block mb-1 fs-7 font-semibold"><i class="bi bi-wallet2 me-1 text-success"></i>حالة وطريقة الدفع</span>
                    <div class="mb-2">
                        <span class="text-muted small me-2">حالة الدفع:</span>
                        @if($invoice->status == 'paid')
                            <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>مدفوعة بالكامل</span>
                        @elseif($invoice->status == 'partially_paid')
                            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-exclamation-triangle me-1"></i>مدفوعة جزئياً</span>
                        @elseif($invoice->status == 'unpaid')
                            <span class="badge bg-danger fs-6"><i class="bi bi-x-circle me-1"></i>غير مدفوعة (آجل)</span>
                        @else
                            <span class="badge bg-secondary fs-6">{{ $invoice->status }}</span>
                        @endif
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small me-2">نوع السداد:</span>
                        @if($invoice->payment_type == 'cash')
                            <span class="badge bg-success-subtle text-success">نقدي بالكامل</span>
                        @elseif($invoice->payment_type == 'bank')
                            <span class="badge bg-primary-subtle text-primary">تحويل بنكي / شبكة</span>
                        @elseif($invoice->payment_type == 'split')
                            <span class="badge bg-info-subtle text-info">دفع متعدد (كاش + بنك + آجل)</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">آجل بالكامل للمورد</span>
                        @endif
                    </div>
                    @if($invoice->purchaseOrder)
                        <div>
                            <span class="text-muted small me-2">امر الشراء المرتبط:</span>
                            <span class="badge bg-dark-subtle text-dark font-mono">{{ $invoice->purchaseOrder->po_number }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 font-bold text-dark"><i class="bi bi-box-seam me-2 text-primary"></i>الأصناف المشتراة</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">الصنف</th>
                            <th scope="col" class="text-center">الوحدة</th>
                            <th scope="col" class="text-center">الكمية</th>
                            <th scope="col" class="text-end">سعر الوحدة</th>
                            <th scope="col" class="text-end">المجموع الفرعي</th>
                            <th scope="col" class="text-end">الضريبة</th>
                            <th scope="col" class="text-end pe-3">الإجمالي الشامل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->items as $index => $item)
                            <tr>
                                <th scope="row" class="ps-3">{{ $index + 1 }}</th>
                                <td>
                                    <strong class="text-dark d-block">{{ $item->item?->name ?? 'صنف غير محدد' }}</strong>
                                    @if($item->item?->code || $item->item?->sku)
                                        <small class="text-muted font-mono">كود: {{ $item->item?->code ?? $item->item?->sku }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $item->unit?->name ?? $item->item?->baseUnit?->name ?? '-' }}</span>
                                </td>
                                <td class="text-center font-mono fw-bold">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end font-mono">{{ number_format($item->unit_price, 2) }} {{ setting('currency', 'ر.س') }}</td>
                                <td class="text-end font-mono text-dark">{{ number_format($item->subtotal, 2) }} {{ setting('currency', 'ر.س') }}</td>
                                <td class="text-end font-mono text-muted">{{ number_format($item->tax_amount, 2) }} {{ setting('currency', 'ر.س') }}</td>
                                <td class="text-end pe-3 font-mono fw-bold text-success">{{ number_format($item->total, 2) }} {{ setting('currency', 'ر.س') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">لا توجد أصناف في هذه الفاتورة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Financial Summary & Payment Accounts -->
    <div class="row g-4 mb-4">
        <!-- Notes / Remarks -->
        <div class="col-12 col-md-7">
            <div class="card shadow-sm border-0 h-100 rounded-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-chat-left-text me-2 text-secondary"></i>الملاحظات والشروط</h5>
                </div>
                <div class="card-body p-4">
                    @if($invoice->notes)
                        <p class="text-dark mb-0 style-whitespace-pre-line">{{ $invoice->notes }}</p>
                    @else
                        <p class="text-muted mb-0 italic">لا توجد ملاحظات مسجلة لهذه الفاتورة.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Totals Card -->
        <div class="col-12 col-md-5">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-bold text-dark"><i class="bi bi-calculator me-2 text-success"></i>الملخص المالي والسداد</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">المجموع قبل الضريبة:</span>
                        <strong class="text-dark font-mono fs-6">{{ number_format($invoice->total_amount, 2) }} {{ setting('currency', 'ر.س') }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">مبلغ ضريبة القيمة المضافة:</span>
                        <strong class="text-dark font-mono fs-6">+ {{ number_format($invoice->tax_amount, 2) }} {{ setting('currency', 'ر.س') }}</strong>
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center py-2 mb-3 bg-light p-3 rounded-3">
                        <h5 class="mb-0 font-bold text-dark">صافي الفاتورة الإجمالي:</h5>
                        <h4 class="mb-0 font-bold text-success font-mono">{{ number_format($invoice->net_amount, 2) }} {{ setting('currency', 'ر.س') }}</h4>
                    </div>

                    <!-- Payment Details breakdown -->
                    <div class="p-3 bg-light-subtle rounded-3 border">
                        <h6 class="font-bold text-dark mb-3"><i class="bi bi-credit-card-2-front me-2 text-primary"></i>تفاصيل توزيع السداد:</h6>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">
                                <i class="bi bi-cash-stack text-success me-1"></i>المدفوع نقداً (كاش):
                                @if($invoice->cashAccount)
                                    <small class="d-block text-muted">({{ $invoice->cashAccount->name }})</small>
                                @endif
                            </span>
                            <strong class="text-success font-mono">{{ number_format($invoice->cash_amount, 2) }} {{ setting('currency', 'ر.س') }}</strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">
                                <i class="bi bi-bank text-primary me-1"></i>المدفوع بنك / شبكة:
                                @if($invoice->bankAccount)
                                    <small class="d-block text-muted">({{ $invoice->bankAccount->name }})</small>
                                @endif
                            </span>
                            <strong class="text-primary font-mono">{{ number_format($invoice->bank_amount, 2) }} {{ setting('currency', 'ر.س') }}</strong>
                        </div>

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center pt-1">
                            <span class="font-semibold text-danger small"><i class="bi bi-hourglass-split me-1"></i>المبلغ المتبقي (آجل للمورد):</span>
                            <strong class="text-danger font-mono fs-6">{{ number_format($invoice->due_amount, 2) }} {{ setting('currency', 'ر.s') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
