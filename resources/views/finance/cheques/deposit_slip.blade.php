<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حافظة إيداع الشيكات - {{ date('Y-m-d') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }
        .deposit-slip-box {
            background: #fff;
            max-width: 900px;
            margin: 20px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header-line {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        @media print {
            body { background: #fff; }
            .deposit-slip-box { box-shadow: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container py-3">
        <!-- Print Controls -->
        <div class="no-print d-flex justify-content-between align-items-center max-w-900 mx-auto mb-3" style="max-width: 900px;">
            <a href="{{ route('cheques.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                &rarr; العودة لإدارة الشيكات
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4">
                طباعة الحافظة
            </button>
        </div>

        <div class="deposit-slip-box">
            <!-- Header -->
            <div class="header-line d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1 text-primary">{{ setting('facility_name', 'منظومة إدارة المؤسسة') }}</h4>
                    <span class="text-muted fs-7">قسم الحسابات والمالية - حافظة تسليم وإيداع شيكات</span>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold text-dark mb-0">حافظة إيداع شيكات</h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill mt-1">
                        تاريخ الإصدار: {{ date('Y-m-d H:i') }}
                    </span>
                </div>
            </div>

            <!-- Summary Bar -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                <div class="col-4">
                    <span class="text-muted fs-7 d-block">إجمالي عدد الشيكات</span>
                    <strong class="fs-5 text-dark">{{ $cheques->count() }} شيك</strong>
                </div>
                <div class="col-4">
                    <span class="text-muted fs-7 d-block">نوع الشيكات بالحافظة</span>
                    <strong class="fs-5 text-dark">شيكات برسم التحصيل/الإيداع</strong>
                </div>
                <div class="col-4 text-end">
                    <span class="text-muted fs-7 d-block">إجمالي قيمة الحافظة</span>
                    <strong class="fs-4 text-success">{{ number_format($totalAmount, 2) }} ر.س</strong>
                </div>
            </div>

            <!-- Cheques Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle fs-7">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th>رقم الشيك</th>
                            <th>البنك الساحب</th>
                            <th>الساحب / العميل / المورد</th>
                            <th class="text-center">تاريخ الاستحقاق</th>
                            <th class="text-center">النوع</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-end">المبلغ (ر.س)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cheques as $index => $c)
                            <tr>
                                <td class="text-center font-bold">{{ $index + 1 }}</td>
                                <td class="fw-bold font-mono">{{ $c->cheque_number }}</td>
                                <td>{{ $c->bank_name }}</td>
                                <td>{{ $c->drawer_name ?? $c->voucher?->customer?->name ?? $c->voucher?->supplier?->name ?? '-' }}</td>
                                <td class="text-center font-mono">{{ $c->due_date ? $c->due_date->format('Y-m-d') : '-' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $c->type === 'outgoing' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                        {{ $c->type === 'outgoing' ? 'صادر' : 'وارد' }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $c->status }}</td>
                                <td class="text-end fw-bold text-dark">{{ number_format($c->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">لا توجد شيكات بالحافظة</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold fs-6">
                        <tr>
                            <td colspan="7" class="text-end">إجمالي المبلغ المطلوب إيداعه بالحساب:</td>
                            <td class="text-end text-success fs-5">{{ number_format($totalAmount, 2) }} ر.س</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Signatures Section -->
            <div class="row pt-4 mt-5 border-top text-center fs-7">
                <div class="col-4">
                    <p class="fw-bold mb-4">معد الحافظة</p>
                    <p class="text-muted">...............................</p>
                </div>
                <div class="col-4">
                    <p class="fw-bold mb-4">المراجع / المدير المالي</p>
                    <p class="text-muted">...............................</p>
                </div>
                <div class="col-4">
                    <p class="fw-bold mb-4">ختم واستلام البنك / مندوب الإيداع</p>
                    <p class="text-muted">...............................</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
