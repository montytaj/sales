<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('sales.quotation_number') }}: {{ $quotation->quotation_number }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #111;
            padding: 20px;
        }
        .print-container {
            max-width: 850px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            border-radius: 8px;
        }
        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
        }
        .table-items th {
            background-color: #f8f9fa !important;
            color: #000;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .print-container {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-4 me-2">
            <i class="bi bi-printer"></i> طباعة المستند
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg px-4">إغلاق</button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="row align-items-center mb-4 pb-3 border-bottom">
            <div class="col-6">
                <h2 class="header-title mb-1">{{ setting('facility_name', 'مؤسسة أثاث وديكور وورش CNC') }}</h2>
                <p class="text-muted mb-0 small">الرياض، المملكة العربية السعودية | هاتف: {{ $quotation->branch?->phone ?? '0112345678' }}</p>
            </div>
            <div class="col-6 text-end">
                <h3 class="font-bold text-dark mb-1">{{ __('sales.quotation_number') }}</h3>
                <h4 class="text-primary font-bold mb-1"><code>{{ $quotation->quotation_number }}</code></h4>
                <small class="text-muted">{{ __('sales.issue_date') }}: {{ $quotation->issue_date->format('Y-m-d') }}</small>
            </div>
        </div>

        <!-- Customer & Info -->
        <div class="row mb-4">
            <div class="col-6">
                <div class="p-3 bg-light rounded">
                    <h6 class="font-bold mb-2 text-primary">بيانات العميل:</h6>
                    <p class="mb-1 fw-bold">{{ $quotation->customer->name }}</p>
                    @if ($quotation->customer->company_name)
                        <p class="mb-1 small text-muted">{{ $quotation->customer->company_name }}</p>
                    @endif
                    <p class="mb-1 small">هاتف: {{ $quotation->customer->phone }}</p>
                    @if ($quotation->customer->vat_number)
                        <p class="mb-0 small">الرقم الضريبي: {{ $quotation->customer->vat_number }}</p>
                    @endif
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 bg-light rounded">
                    <h6 class="font-bold mb-2 text-primary">تفاصيل عرض السعر:</h6>
                    <p class="mb-1 small">تاريخ التحرير: {{ $quotation->issue_date->format('Y-m-d') }}</p>
                    <p class="mb-1 small">تاريخ الانتهاء: {{ $quotation->expiry_date?->format('Y-m-d') ?? '14 يوماً' }}</p>
                    <p class="mb-0 small">حالة الاعتماد: {{ $quotation->is_approved ? 'معتمد رسمياً' : 'مبدئي' }}</p>
                </div>
            </div>
        </div>

        <!-- Table -->
        <table class="table table-bordered table-items align-middle mb-4">
            <thead>
                <tr>
                    <th scope="col" style="width: 5%;">#</th>
                    <th scope="col" style="width: 45%;">البيان / الخدمة</th>
                    <th scope="col" class="text-center" style="width: 10%;">الكمية</th>
                    <th scope="col" class="text-center" style="width: 10%;">الوحدة</th>
                    <th scope="col" class="text-end" style="width: 15%;">سعر الوحدة</th>
                    <th scope="col" class="text-end" style="width: 15%;">الإجمالي ({{ setting('currency', 'SDG') }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->item_name }}</strong>
                            @if ($item->description)
                                <small class="text-muted d-block">{{ $item->description }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-center">{{ __('services.units.' . $item->unit_of_measure) }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="row mb-4">
            <div class="col-7">
                @if ($quotation->terms_conditions)
                    <div class="p-3 border rounded">
                        <h6 class="font-bold mb-2">الشروط والأحكام:</h6>
                        <p class="mb-0 small text-muted" style="white-space: pre-line;">{{ $quotation->terms_conditions }}</p>
                    </div>
                @endif
            </div>
            <div class="col-5">
                <div class="p-3 bg-light rounded">
                    <div class="d-flex justify-content-between mb-1">
                        <span>المجموع الفرعي:</span>
                        <span>{{ number_format($quotation->subtotal, 2) }} {{ setting('currency', 'SDG') }}</span>
                    </div>
                    @if ($quotation->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-1 text-danger">
                            <span>الخصم:</span>
                            <span>- {{ number_format($quotation->discount_amount, 2) }} {{ setting('currency', 'SDG') }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span>ضريبة القيمة المضافة ({{ setting('tax_percentage', 15.00) }}%):</span>
                        <span>+ {{ number_format($quotation->tax_amount, 2) }} {{ setting('currency', 'SDG') }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between font-bold fs-5 text-success">
                        <span>الإجمالي النهائي:</span>
                        <span>{{ number_format($quotation->total_amount, 2) }} {{ setting('currency', 'SDG') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Signatures -->
        <div class="row pt-5 text-center mt-4">
            <div class="col-6">
                <p class="mb-4 font-bold">توقيع مسئول المبيعات</p>
                <p class="text-muted mb-0">---------------------------</p>
            </div>
            <div class="col-6">
                <p class="mb-4 font-bold">موافقة وتوقيع العميل</p>
                <p class="text-muted mb-0">---------------------------</p>
            </div>
        </div>
    </div>
</body>
</html>
