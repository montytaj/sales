<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => app()->getLocale() == 'ar' ? 'قائمة الدخل' : 'Income Statement']
            ];
        @endphp
    </x-slot>

    <x-page-header title="{{ app()->getLocale() == 'ar' ? 'قائمة الدخل' : 'Income Statement' }}" description="تقرير قائمة الدخل الشامل لتحليل المبيعات وتكلفة المشتريات والمصروفات وصافي الأرباح">
        <x-slot name="actions">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('reports.income-statement', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير CSV
                </a>
                <a href="{{ route('reports.income-statement', array_merge(request()->all(), ['export' => 'print'])) }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i> طباعة التقرير
                </a>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Filters Form Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.income-statement') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ $from_date }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ $to_date }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-12 col-md-3">
                    @if(count($branches) > 0)
                        <label class="form-label fs-7 font-bold text-slate-700 mb-1">الفرع</label>
                        <select name="branch_id" class="form-select form-select-sm rounded-3">
                            <option value="">كافة الفروع</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)$branch_id === (string)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">
                        <i class="bi bi-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card card-custom border-0 shadow-sm p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-8 text-slate-500 uppercase font-bold">{{ app()->getLocale() == 'ar' ? 'صافي المبيعات' : 'Net Sales' }}</span>
                        <h4 class="font-bold text-slate-900 mb-0 mt-1">{{ number_format($net_sales, 2) }} <small class="fs-8 text-slate-500">{{ currency() }}</small></h4>
                    </div>
                    <div class="rounded-circle bg-primary-subtle p-3 text-primary fs-4">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="card card-custom border-0 shadow-sm p-3 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-8 text-slate-500 uppercase font-bold">{{ app()->getLocale() == 'ar' ? 'مجمل الربح' : 'Gross Profit' }}</span>
                        <h4 class="font-bold text-info mb-0 mt-1">{{ number_format($gross_profit, 2) }} <small class="fs-8 text-slate-500">{{ currency() }}</small></h4>
                    </div>
                    <div class="rounded-circle bg-info-subtle p-3 text-info fs-4">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="card card-custom border-0 shadow-sm p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-8 text-slate-500 uppercase font-bold">{{ app()->getLocale() == 'ar' ? 'إجمالي المصروفات التشغيلية' : 'Total Operating Expenses' }}</span>
                        <h4 class="font-bold text-warning mb-0 mt-1">{{ number_format($total_operating_expenses, 2) }} <small class="fs-8 text-slate-500">{{ currency() }}</small></h4>
                    </div>
                    <div class="rounded-circle bg-warning-subtle p-3 text-warning fs-4">
                        <i class="bi bi-journal-minus"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="card card-custom border-0 shadow-sm p-3 border-start border-4 {{ $net_profit_after_tax >= 0 ? 'border-success' : 'border-danger' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-8 text-slate-500 uppercase font-bold">{{ app()->getLocale() == 'ar' ? 'صافي الربح النهائي' : 'Net Profit' }}</span>
                        <h4 class="font-bold {{ $net_profit_after_tax >= 0 ? 'text-success' : 'text-danger' }} mb-0 mt-1">
                            {{ number_format($net_profit_after_tax, 2) }} <small class="fs-8 text-slate-500">{{ currency() }}</small>
                        </h4>
                    </div>
                    <div class="rounded-circle {{ $net_profit_after_tax >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} p-3 fs-4">
                        <i class="bi {{ $net_profit_after_tax >= 0 ? 'bi-trophy' : 'bi-arrow-down-circle' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Statement Table Card (Matching Image 2 Structure) -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-white p-3 border-bottom border-slate-200 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-bold text-slate-800 fs-6"><i class="bi bi-file-earmark-text text-primary me-2"></i> {{ app()->getLocale() == 'ar' ? 'قائمة الدخل التفصيلية' : 'Income Statement' }}</h5>
            <span class="badge bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1 fs-7">
                الفترة من {{ $from_date }} إلى {{ $to_date }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-7">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4" style="width: 70%;">البيان المحاسبي</th>
                        <th class="text-end pe-4" style="width: 30%;">{{ app()->getLocale() == 'ar' ? 'المبلغ (' . currency() . ')' : 'Amount (' . currency() . ')' }}</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sales -->
                    <tr>
                        <td class="ps-4 font-semibold text-slate-900">المبيعات الإجمالية</td>
                        <td class="text-end pe-4 font-semibold text-slate-900">{{ number_format($gross_sales, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4 font-medium text-slate-600">(-) مردودات ومسموحات المبيعات والخصومات</td>
                        <td class="text-end pe-4 text-danger font-semibold">({{ number_format($sales_returns, 2) }})</td>
                    </tr>
                    <tr class="table-primary font-bold">
                        <td class="ps-4">{{ app()->getLocale() == 'ar' ? 'صافي المبيعات' : 'Net Sales' }}</td>
                        <td class="text-end pe-4 text-primary fs-6">{{ number_format($net_sales, 2) }}</td>
                    </tr>

                    <!-- COGS -->
                    <tr>
                        <td class="ps-4 font-medium text-slate-600">{{ app()->getLocale() == 'ar' ? '(-) تكلفة البضاعة المباعة' : '(-) Cost of Goods Sold' }}</td>
                        <td class="text-end pe-4 text-danger font-semibold">({{ number_format($cogs, 2) }})</td>
                    </tr>

                    <!-- Gross Profit -->
                    <tr class="table-info font-bold">
                        <td class="ps-4">{{ app()->getLocale() == 'ar' ? 'مجمل الربح' : 'Gross Profit' }}</td>
                        <td class="text-end pe-4 text-info fs-6">{{ number_format($gross_profit, 2) }}</td>
                    </tr>

                    <!-- Operating Expenses Header -->
                    <tr class="table-light">
                        <td colspan="2" class="ps-4 font-bold text-slate-700 text-uppercase border-top border-bottom">
                            <i class="bi bi-list-ul me-1 text-warning"></i> {{ app()->getLocale() == 'ar' ? 'المصروفات التشغيلية:' : 'Operating Expenses:' }}
                        </td>
                    </tr>

                    @foreach($operating_expenses as $exp)
                        <tr>
                            <td class="ps-5 text-slate-700">{{ $exp['name'] }}</td>
                            <td class="text-end pe-4 font-medium text-slate-800">{{ number_format($exp['amount'], 2) }}</td>
                        </tr>
                    @endforeach

                    <tr class="table-warning font-bold">
                        <td class="ps-4">إجمالي المصروفات التشغيلية</td>
                        <td class="text-end pe-4 text-dark fs-7">({{ number_format($total_operating_expenses, 2) }})</td>
                    </tr>

                    <!-- Operating Profit -->
                    <tr class="table-secondary font-bold">
                        <td class="ps-4">{{ app()->getLocale() == 'ar' ? 'الربح التشغيلي' : 'Operating Profit' }}</td>
                        <td class="text-end pe-4 fs-6">{{ number_format($operating_profit, 2) }}</td>
                    </tr>

                    <!-- Other Revenues & Interest -->
                    <tr>
                        <td class="ps-4 font-medium text-slate-700">(+) إيرادات أخرى متنوعة</td>
                        <td class="text-end pe-4 font-semibold text-success">{{ number_format($other_income, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4 font-medium text-slate-700">(-) مصروف فوائد بنكية وتمويلية</td>
                        <td class="text-end pe-4 font-semibold text-danger">({{ number_format($interest_expense, 2) }})</td>
                    </tr>

                    <!-- Profit Before Tax -->
                    <tr class="table-light font-bold">
                        <td class="ps-4">{{ app()->getLocale() == 'ar' ? 'صافي الربح قبل الضريبة' : 'Profit Before Tax' }}</td>
                        <td class="text-end pe-4 fs-6">{{ number_format($profit_before_tax, 2) }}</td>
                    </tr>

                    <!-- Tax -->
                    <tr>
                        <td class="ps-4 font-medium text-slate-700">(-) ضريبة الدخل / ضريبة القيمة المضافة</td>
                        <td class="text-end pe-4 font-semibold text-danger">({{ number_format($tax_amount, 2) }})</td>
                    </tr>

                    <!-- Final Net Profit -->
                    <tr class="{{ $net_profit_after_tax >= 0 ? 'table-success' : 'table-danger' }} font-bold">
                        <td class="ps-4 fs-6">{{ app()->getLocale() == 'ar' ? 'صافي الربح بعد الضريبة' : 'Net Profit After Tax' }}</td>
                        <td class="text-end pe-4 fs-5 {{ $net_profit_after_tax >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($net_profit_after_tax, 2) }} {{ currency() }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
