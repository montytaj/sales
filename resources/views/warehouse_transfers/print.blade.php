<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('transfers.voucher_title') }} - {{ $transfer->transfer_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #333;
        }
        .header-box {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f8fafc !important;
            color: #1e293b;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">
    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-primary shadow-sm"><i class="bi bi-printer me-1"></i>طباعة المستند</button>
        <button onclick="window.close()" class="btn btn-secondary shadow-sm">إغلاق</button>
    </div>

    <div class="container-fluid">
        <!-- Document Header -->
        <div class="header-box d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-primary mb-1">{{ setting('facility_name', 'منظومة إدارة المؤسسة') }}</h2>
                <h5 class="text-secondary mb-0">{{ __('transfers.voucher_title') }}</h5>
            </div>
            <div class="text-end">
                <h4 class="fw-bold text-dark mb-1 font-mono">#{{ $transfer->transfer_number }}</h4>
                <div class="text-muted fs-7">التاريخ: {{ $transfer->transfer_date?->format('Y-m-d') }}</div>
                <div class="mt-1">{!! $transfer->status_badge !!}</div>
            </div>
        </div>

        <!-- Warehouses Information -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="p-3 border rounded">
                    <small class="text-muted fw-bold d-block mb-1">المخزن المصدر (من):</small>
                    <h5 class="fw-bold mb-1">{{ $transfer->fromWarehouse?->name }}</h5>
                    <div class="text-muted fs-7">الكود: {{ $transfer->fromWarehouse?->code }} | أمين المخزن: {{ $transfer->fromWarehouse?->keeper_name ?? '-' }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 border rounded">
                    <small class="text-muted fw-bold d-block mb-1">المخزن الهدف (إلى):</small>
                    <h5 class="fw-bold mb-1">{{ $transfer->toWarehouse?->name }}</h5>
                    <div class="text-muted fs-7">الكود: {{ $transfer->toWarehouse?->code }} | أمين المخزن: {{ $transfer->toWarehouse?->keeper_name ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered align-middle mb-4">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>الصنف</th>
                    <th>الوحدة</th>
                    <th>الكمية</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfer->items as $itemRow)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $itemRow->item?->name }}</td>
                        <td>{{ $itemRow->item?->unit }}</td>
                        <td class="fw-bold fs-6 font-mono">{{ number_format($itemRow->quantity, 2) }}</td>
                        <td>{{ $itemRow->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($transfer->notes)
            <div class="p-3 border rounded mb-4">
                <small class="text-muted fw-bold d-block mb-1">ملاحظات التحويل:</small>
                <p class="mb-0 fs-7">{{ $transfer->notes }}</p>
            </div>
        @endif

        <!-- Signatures Footer -->
        <div class="row text-center mt-5 pt-4">
            <div class="col-4">
                <div class="fw-bold mb-4">منشئ الطلب</div>
                <div class="text-muted">{{ $transfer->creator?->name ?? '........................' }}</div>
            </div>
            <div class="col-4">
                <div class="fw-bold mb-4">أمين المخزن المصدر</div>
                <div class="text-muted">........................</div>
            </div>
            <div class="col-4">
                <div class="fw-bold mb-4">أمين المخزن المحول إليه</div>
                <div class="text-muted">........................</div>
            </div>
        </div>
    </div>
</body>
</html>
