<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>طباعة مجموعة فواتير مبيعات ({{ count($invoices) }})</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #111;
            padding: 20px;
        }
        .print-container {
            max-width: 850px;
            margin: 0 auto 30px auto;
            border: 1px solid #e2e8f0;
            padding: 30px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header-title {
            font-size: 22px;
            font-weight: bold;
            color: #0d6efd;
        }
        .table-items th {
            background-color: #f1f5f9 !important;
            color: #0f172a;
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #fff;
                padding: 0;
            }
            .print-container {
                border: none;
                padding: 0;
                box-shadow: none;
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4 sticky-top bg-white p-3 border-bottom shadow-sm">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-4 me-2">
            <i class="bi bi-printer-fill me-1"></i> طباعة كافّة الفواتير المحددة ({{ count($invoices) }})
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-lg px-4">إغلاق</button>
    </div>

    @foreach ($invoices as $index => $invoice)
        <div class="print-container {{ !$loop->last ? 'page-break' : '' }}">
            <!-- Header -->
            <div class="row align-items-center mb-4 pb-3 border-bottom">
                <div class="col-6">
                    <h2 class="header-title mb-1">{{ setting('facility_name', 'مؤسسة التكاليف والمبيعات') }}</h2>
                    <p class="text-muted mb-0 small">الرياض، المملكة العربية السعودية | هاتف: {{ $invoice->branch?->phone ?? '0112345678' }}</p>
                    <p class="text-muted mb-0 small">الرقم الضريبي للمنشأة: 300012345600003</p>
                </div>
                <div class="col-6 text-end">
                    <h3 class="fw-bold text-dark mb-1">فاتورة مبيعات ضريبية</h3>
                    <h4 class="text-primary fw-bold mb-1"><code>{{ $invoice->invoice_number }}</code></h4>
                    <small class="text-muted">تاريخ الإصدار: {{ $invoice->issue_date->format('Y-m-d') }}</small>
                </div>
            </div>

            <!-- Customer & Details -->
            <div class="row mb-4">
                <div class="col-6">
                    <div class="p-3 bg-light rounded">
                        <h6 class="fw-bold text-dark mb-2">بيانات العميل:</h6>
                        <p class="mb-1 fw-bold">{{ $invoice->customer->name }}</p>
                        <p class="mb-1 text-muted small">هاتف: {{ $invoice->customer->phone ?? '-' }}</p>
                        <p class="mb-0 text-muted small">الرقم الضريبي: {{ $invoice->customer->vat_number ?? '-' }}</p>
                    </div>
                </div>
                <div class="col-6 text-end">
                    <div class="p-3 bg-light rounded text-end">
                        <h6 class="fw-bold text-dark mb-2">تفاصيل الفاتورة:</h6>
                        <p class="mb-1 small">حالة الدفع: <span class="badge bg-primary">{{ __('sales.invoice_statuses.' . $invoice->status) }}</span></p>
                        <p class="mb-1 small">طريقة السداد: <strong>{{ strtoupper($invoice->payment_type) }}</strong></p>
                        <p class="mb-0 small">تاريخ الاستحقاق: {{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle table-items">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 50px;">#</th>
                            <th scope="col">الصنف / الخدمة</th>
                            <th scope="col" class="text-center" style="width: 100px;">الكمية</th>
                            <th scope="col" class="text-center" style="width: 100px;">الوحدة</th>
                            <th scope="col" class="text-end" style="width: 120px;">سعر الوحدة</th>
                            <th scope="col" class="text-end" style="width: 130px;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-bold">{{ $item->inventoryItem?->name ?? 'صنف غير معروف' }}</div>
                                    @if ($item->inventoryItem?->code)
                                        <small class="text-muted">كود: {{ $item->inventoryItem->code }}</small>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-center">{{ $item->unit?->name ?? 'قطعة' }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Totals -->
            <div class="row justify-content-end">
                <div class="col-5">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">المجموع الفرعي:</td>
                            <td class="text-end fw-bold">{{ number_format($invoice->subtotal, 2) }} {{ setting('currency', 'SDG') }}</td>
                        </tr>
                        @if ($invoice->tax_amount > 0)
                            <tr>
                                <td class="text-muted">ضريبة القيمة المضافة (15%):</td>
                                <td class="text-end text-danger fw-bold">+ {{ number_format($invoice->tax_amount, 2) }} {{ setting('currency', 'SDG') }}</td>
                            </tr>
                        @endif
                        <tr class="border-top">
                            <td class="fw-bold fs-6">الإجمالي النهائي:</td>
                            <td class="text-end fw-bold fs-5 text-primary">{{ number_format($invoice->total_amount, 2) }} {{ setting('currency', 'SDG') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Footer Signatures -->
            <div class="row mt-4 pt-4 border-top text-center text-muted small">
                <div class="col-4">
                    <p class="mb-4">توقيع المستلم</p>
                    <p>....................................</p>
                </div>
                <div class="col-4">
                    <p class="mb-4">ختم المؤسسة</p>
                    <p>....................................</p>
                </div>
                <div class="col-4">
                    <p class="mb-4">توقيع المحاسب / الكاشير</p>
                    <p>....................................</p>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
