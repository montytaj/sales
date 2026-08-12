<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>كارت جرد صنف: {{ $selectedItem->name }} - {{ setting('facility_name', 'ERP System') }}</title>
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
            font-size: 12px;
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
            padding: 7px 10px;
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
                margin: 12mm;
            }
        }
    </style>
</head>
<body>
    <!-- Top Action Bar -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4 bg-light p-3 rounded border">
        <button onclick="window.close()" class="btn btn-secondary fw-bold">
            <i class="bi bi-x-circle me-1"></i> إغلاق النافذة
        </button>
        <button onclick="window.print()" class="btn btn-primary fw-bold px-4">
            <i class="bi bi-printer-fill me-1"></i> طباعة كارت الجرد
        </button>
    </div>

    <!-- Official Document Header -->
    <div class="print-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1 text-primary">{{ setting('facility_name', 'نظام إدارة المؤسسة والمخازن') }}</h3>
            <div class="text-muted small">تقرير كارت جرد ومتابعة حركة الصنف التفصيلي</div>
            <div class="small mt-1">طبع بواسطة: <strong>{{ Auth::user()?->name ?? 'مستخدم النظام' }}</strong></div>
        </div>
        <div class="text-end">
            <h4 class="fw-bold mb-1">{{ $selectedItem->name }}</h4>
            <div class="text-muted small">كود الصنف: <strong class="font-mono text-dark">{{ $selectedItem->code ?? $selectedItem->item_code }}</strong> {{ $selectedItem->barcode ? ' | باركود: ' . $selectedItem->barcode : '' }}</div>
            <div class="small text-muted dir-ltr text-end mt-1">تاريخ التصدير والطباعة: {{ date('Y-m-d H:i') }}</div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-2 mb-4">
        <div class="col-3">
            <div class="summary-card">
                <div class="small text-muted fw-semibold">إجمالي رصيد الصنف</div>
                <div class="fs-6 fw-bold text-primary">{{ number_format($totalBaseStock, 2) }} {{ $selectedItem->baseUnit?->name ?? 'وحدة' }}</div>
                <div class="small text-muted">{{ $selectedItem->formatted_stock }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="summary-card">
                <div class="small text-muted fw-semibold">إجمالي التقييم (تكلفة)</div>
                <div class="fs-6 fw-bold text-success">{{ number_format($totalValuation, 2) }} ر.س</div>
                <div class="small text-muted">سعر الوحدة: {{ number_format((float)$selectedItem->cost_price, 2) }} ر.س</div>
            </div>
        </div>
        <div class="col-3">
            <div class="summary-card">
                <div class="small text-muted fw-semibold">عدد المخازن المتوفر بها</div>
                <div class="fs-6 fw-bold text-dark">{{ $warehousesWithStockCount }} مخزن</div>
                <div class="small text-muted">من إجمالي المخازن المسجلة</div>
            </div>
        </div>
        <div class="col-3">
            <div class="summary-card">
                <div class="small text-muted fw-semibold">أسعار البيع</div>
                <div class="small fw-bold">جملة: {{ number_format((float)$selectedItem->wholesale_price, 2) }} ر.س</div>
                <div class="small fw-bold text-success">قطاعي: {{ number_format((float)$selectedItem->retail_price, 2) }} ر.س</div>
            </div>
        </div>
    </div>

    <!-- Section 1: Warehouses Stock Table -->
    <h6 class="fw-bold mb-2 text-dark border-bottom pb-1">
        <i class="bi bi-building me-1"></i> أولاً: أرصدة وكميات الصنف المتوفرة بالمخازن
    </h6>
    <table class="print-table mb-4">
        <thead>
            <tr>
                <th style="width: 35px;">#</th>
                <th>اسم المخزن</th>
                <th>الفرع</th>
                <th class="text-center">الكمية بالوحدة الأساسية</th>
                <th>تفاصيل الكمية (جملة + تجزئة)</th>
                <th class="text-end">التقييم بسعر التكلفة</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($warehouseStock as $index => $ws)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $ws['warehouse']->name }}</td>
                    <td>{{ $ws['warehouse']->branch?->name ?? 'المركز الرئيسي' }}</td>
                    <td class="text-center fw-bold">{{ number_format($ws['qty_in_base'], 2) }} {{ $selectedItem->baseUnit?->name }}</td>
                    <td>{{ $ws['formatted_stock'] }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($ws['valuation'], 2) }} ر.س</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-3 text-muted">لا تتوفر أرصدة مخزنية.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold bg-light">
                <td colspan="3">الإجمالي الإجمالي بكافة المخازن:</td>
                <td class="text-center text-primary">{{ number_format($totalBaseStock, 2) }} {{ $selectedItem->baseUnit?->name }}</td>
                <td>{{ $selectedItem->formatted_stock }}</td>
                <td class="text-end text-success">{{ number_format($totalValuation, 2) }} ر.س</td>
            </tr>
        </tfoot>
    </table>

    <!-- Section 2: Movements History Table -->
    <h6 class="fw-bold mb-2 text-dark border-bottom pb-1">
        <i class="bi bi-clock-history me-1"></i> ثانياً: سجل حركات الصنف التفصيلية (الوارد والمنصرف)
    </h6>
    <table class="print-table mb-4">
        <thead>
            <tr>
                <th style="width: 35px;">#</th>
                <th>التاريخ والوقت</th>
                <th>المخزن</th>
                <th class="text-center">نوع الحركة</th>
                <th class="text-center">الكمية بالحركة</th>
                <th>المرجع / المستند</th>
                <th>البيان والملاحظات</th>
                <th>المستخدم المسؤول</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $index => $m)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td>{{ $m->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $m->warehouse?->name ?? '-' }}</td>
                    <td class="text-center fw-bold">
                        @if($m->movement_type === 'in')
                            <span class="text-success">وارد (+)</span>
                        @elseif($m->movement_type === 'out')
                            <span class="text-danger">منصرف (-)</span>
                        @else
                            <span>{{ $m->movement_type }}</span>
                        @endif
                    </td>
                    <td class="text-center fw-bold">
                        {{ $m->movement_type === 'in' ? '+' : ($m->movement_type === 'out' ? '-' : '') }}{{ number_format($m->quantity, 2) }}
                    </td>
                    <td>{{ $m->reference_type }}</td>
                    <td>{{ $m->notes ?: '-' }}</td>
                    <td>{{ $m->creator?->name ?? 'النظام' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-3 text-muted">لا توجد حركات مخزنية مسجلة على هذا الصنف.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer Signatures -->
    <div class="row mt-5 pt-4 border-top">
        <div class="col-4 text-center">
            <div class="fw-bold mb-4">أمين / أمناء المخازن</div>
            <div class="text-muted">................................</div>
        </div>
        <div class="col-4 text-center">
            <div class="fw-bold mb-4">توقيع لجنة الجرد والمعاينة</div>
            <div class="text-muted">................................</div>
        </div>
        <div class="col-4 text-center">
            <div class="fw-bold mb-4">اعتماد المدير المسؤول</div>
            <div class="text-muted">................................</div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
