<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('payments.voucher_number') }}: {{ $voucher->voucher_number }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #111;
            padding: 20px;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px dashed #bbb;
            padding: 30px;
            border-radius: 8px;
        }
        .header-title {
            font-size: 22px;
            font-weight: bold;
            color: #0d6efd;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .print-container {
                border: 1px solid #000;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-4 me-2">
            <i class="bi bi-printer"></i> طباعة الإيصال المالي
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg px-4">إغلاق</button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="row align-items-center mb-4 pb-3 border-bottom">
            <div class="col-6">
                <h2 class="header-title mb-1">{{ setting('facility_name', 'مؤسسة أثاث وديكور وورش CNC') }}</h2>
                <p class="text-muted mb-0 small">إيصال مالي معتمد</p>
            </div>
            <div class="col-6 text-end">
                <h3 class="font-bold text-dark mb-1">{{ __('payments.types.' . $voucher->type) }}</h3>
                <h4 class="text-primary font-bold mb-1"><code>{{ $voucher->voucher_number }}</code></h4>
                <small class="text-muted">التاريخ: {{ $voucher->payment_date->format('Y-m-d') }}</small>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <p class="mb-1"><strong>استلمنا من السيد / المنشأة:</strong> {{ $voucher->customer?->name ?? ($voucher->supplier?->name ?? 'تحويل داخلي') }}</p>
                <p class="mb-1"><strong>مبلغ وقدره:</strong> <span class="fs-5 font-bold text-success">{{ number_format($voucher->amount, 2) }} {{ setting('currency', 'SDG') }}</span></p>
                <p class="mb-0"><strong>وذلك عن:</strong> {{ $voucher->notes ?? 'سداد مستحقات مالية' }}</p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-1"><strong>الخزنة / الحساب:</strong> {{ $voucher->cashbox?->name_ar ?? '-' }}</p>
                <p class="mb-0"><strong>الفاتورة المرتبطة:</strong> {{ $voucher->invoice?->invoice_number ?? 'غير مرتبطة' }}</p>
            </div>
        </div>

        <!-- Payment Breakdown -->
        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th>طريقة السداد</th>
                    <th class="text-end">المبلغ المسدد</th>
                    <th>رقم المرجع / الحوالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($voucher->lines as $line)
                    <tr>
                        <td>{{ __('payments.methods.' . $line->payment_method) }}</td>
                        <td class="text-end fw-bold">{{ number_format($line->amount, 2) }} {{ setting('currency', 'SDG') }}</td>
                        <td><code>{{ $line->reference_number ?? '-' }}</code></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="row pt-5 text-center mt-4">
            <div class="col-6">
                <p class="mb-4 font-bold">توقيع المستلم / المحاسب</p>
                <p class="text-muted mb-0">---------------------------</p>
            </div>
            <div class="col-6">
                <p class="mb-4 font-bold">توقيع المودع / العميل</p>
                <p class="text-muted mb-0">---------------------------</p>
            </div>
        </div>
    </div>
</body>
</html>
