<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'تقرير طباعة' }} - Workshop ERP</title>
    <!-- Cairo Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #ffffff;
            color: #0f172a;
            padding: 20px;
        }

        .print-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .print-table th {
            background-color: #f1f5f9 !important;
            color: #0f172a;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: right;
        }

        .print-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
        }

        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
    <!-- Top Action Bar for screen view -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4 bg-light p-3 rounded border">
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="bi bi-x-circle me-1"></i> إغلاق النافذة
        </button>
        <button onclick="window.print()" class="btn btn-primary fw-bold px-4">
            <i class="bi bi-printer-fill me-1"></i> طباعة المستند الآن
        </button>
    </div>

    <!-- Official Document Header -->
    <div class="print-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1 text-primary">Workshop ERP</h3>
            <div class="text-muted small">نظام إدارة الورش والإنتاج والمقاولات</div>
            <div class="small mt-1">المستخدم: <strong>{{ Auth::user()?->name ?? 'نظام' }}</strong></div>
        </div>
        <div class="text-end">
            <h4 class="fw-bold mb-1">{{ $title }}</h4>
            <div class="text-muted small">{{ $subtitle ?? '' }}</div>
            <div class="small text-muted dir-ltr text-end mt-1">تاريخ الطباعة: {{ date('Y-m-d H:i:s') }}</div>
        </div>
    </div>

    @if (!empty($summary))
        <!-- Summary Cards Grid -->
        <div class="row g-2 mb-4">
            @foreach ($summary as $label => $val)
                <div class="col-3">
                    <div class="summary-card">
                        <div class="small text-muted fw-semibold">{{ $label }}</div>
                        <div class="fs-5 fw-bold text-dark font-mono dir-ltr text-start">{{ $val }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Main Data Table -->
    <table class="print-table mb-4">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                @foreach ($columns as $key => $colLabel)
                    <th>{{ $colLabel }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $row)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    @foreach ($columns as $key => $colLabel)
                        @php
                            $parts = explode('.', $key);
                            $val = $row;
                            foreach ($parts as $part) {
                                $val = is_array($val) ? ($val[$part] ?? null) : ($val?->{$part} ?? '');
                            }
                            $isNumeric = is_numeric($val);
                        @endphp
                        <td class="{{ $isNumeric ? 'font-mono text-end dir-ltr' : '' }}">
                            {{ $isNumeric ? number_format((float)$val, 2) : $val }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" class="text-center py-4 text-muted">
                        لا توجد بيانات متاحة في هذا التقرير.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer Signatures -->
    <div class="row mt-5 pt-4 border-top">
        <div class="col-4 text-center">
            <div class="fw-bold mb-4">توقيع أخصائي التقارير</div>
            <div class="text-muted">................................</div>
        </div>
        <div class="col-4 text-center">
            <div class="fw-bold mb-4">توقيع المحاسب / المدير</div>
            <div class="text-muted">................................</div>
        </div>
        <div class="col-4 text-center">
            <div class="fw-bold mb-4">اعتماد الإدارة العامة</div>
            <div class="text-muted">................................</div>
        </div>
    </div>

    <script>
        // Auto print popup when opened
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
