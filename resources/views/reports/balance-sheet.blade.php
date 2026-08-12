<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => app()->getLocale() == 'ar' ? 'قائمة المركز المالي' : 'Balance Sheet']
            ];
        @endphp
    </x-slot>

    <x-page-header title="{{ app()->getLocale() == 'ar' ? 'قائمة المركز المالي' : 'Balance Sheet' }}" description="تقرير الميزانية العمومية الشامل للأصول والخصوم وحقوق الملكية وفقاً للمعايير المحاسبية">
        <x-slot name="actions">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('reports.balance-sheet', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير CSV
                </a>
                <a href="{{ route('reports.balance-sheet', array_merge(request()->all(), ['export' => 'print'])) }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i> طباعة التقرير
                </a>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Filters Form Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.balance-sheet') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">
                        <i class="bi bi-calendar-date text-primary me-1"></i> تاريخ الميزانية العمومية (كما في تاريخ)
                    </label>
                    <input type="date" name="as_of_date" value="{{ $as_of_date }}" class="form-control form-control-sm rounded-3">
                </div>

                <div class="col-12 col-md-5">
                    @if(count($branches) > 0)
                        <label class="form-label fs-7 font-bold text-slate-700 mb-1">
                            <i class="bi bi-building me-1 text-info"></i> الفرع
                        </label>
                        <select name="branch_id" class="form-select form-select-sm rounded-3">
                            <option value="">كافة الفروع والمركز الرئيسي</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)$branch_id === (string)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">
                        <i class="bi bi-filter me-1"></i> عرض
                    </button>
                    <a href="{{ route('reports.balance-sheet') }}" class="btn btn-light btn-sm text-slate-600 rounded-3" title="إعادة ضبط">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Balance Sheet Main Grid (Side A: Assets, Side B: Liabilities & Equity) -->
    <div class="row g-4 mb-4">
        <!-- 1. Right Side: Assets (الأصول) -->
        <div class="col-12 col-lg-6">
            <div class="card card-custom h-100 border-0 shadow-sm border-top border-4 border-primary">
                <div class="card-header bg-primary text-white p-3 d-flex justify-content-between align-items-center rounded-top">
                    <h5 class="mb-0 font-bold fs-6"><i class="bi bi-wallet2 me-2"></i> {{ app()->getLocale() == 'ar' ? 'الأصول' : 'Assets' }}</h5>
                    <span class="badge bg-white text-primary rounded-pill px-3 py-1 fs-7 font-bold">
                        {{ number_format($total_assets, 2) }} {{ currency() }}
                    </span>
                </div>

                <div class="card-body p-0">
                    <!-- Section 1: Current Assets -->
                    <div class="bg-slate-50 p-3 border-bottom border-slate-200">
                        <h6 class="font-bold text-slate-800 mb-0 fs-7 text-uppercase">
                            <i class="bi bi-folder-symlink me-1 text-primary"></i> {{ app()->getLocale() == 'ar' ? 'أولاً: الأصول المتداولة' : 'Current Assets' }}
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-7">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">البيان / الحساب</th>
                                    <th class="text-end pe-4">{{ app()->getLocale() == 'ar' ? 'المبلغ (' . currency() . ')' : 'Amount (' . currency() . ')' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($current_assets as $item)
                                    <tr>
                                        <td class="ps-4 font-medium text-slate-700">{{ $item['name'] }}</td>
                                        <td class="text-end pe-4 font-semibold text-slate-900">{{ number_format($item['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-info font-bold">
                                <tr>
                                    <td class="ps-4">إجمالي الأصول المتداولة</td>
                                    <td class="text-end pe-4 text-primary">{{ number_format($total_current_assets, 2) }} {{ currency() }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Section 2: Fixed / Non-Current Assets -->
                    <div class="bg-slate-50 p-3 border-bottom border-top border-slate-200">
                        <h6 class="font-bold text-slate-800 mb-0 fs-7 text-uppercase">
                            <i class="bi bi-building-add me-1 text-info"></i> {{ app()->getLocale() == 'ar' ? 'ثانياً: الأصول غير المتداولة' : 'Fixed Assets' }}
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-7">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">البيان / الحساب</th>
                                    <th class="text-end pe-4">{{ app()->getLocale() == 'ar' ? 'المبلغ (' . currency() . ')' : 'Amount (' . currency() . ')' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fixed_assets as $item)
                                    <tr>
                                        <td class="ps-4 font-medium text-slate-700">{{ $item['name'] }}</td>
                                        <td class="text-end pe-4 font-semibold {{ $item['amount'] < 0 ? 'text-danger' : 'text-slate-900' }}">
                                            {{ $item['amount'] < 0 ? '(' . number_format(abs($item['amount']), 2) . ')' : number_format($item['amount'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-info font-bold">
                                <tr>
                                    <td class="ps-4">صافي الأصول الثابتة</td>
                                    <td class="text-end pe-4 text-info">{{ number_format($net_fixed_assets, 2) }} {{ currency() }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Assets Footer Total -->
                <div class="card-footer bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                    <span class="font-bold fs-6">{{ app()->getLocale() == 'ar' ? 'إجمالي الأصول:' : 'Total Assets:' }}</span>
                    <span class="fs-5 font-bold">{{ number_format($total_assets, 2) }} {{ currency() }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Left Side: Liabilities & Equity (الخصوم وحقوق الملكية) -->
        <div class="col-12 col-lg-6">
            <div class="card card-custom h-100 border-0 shadow-sm border-top border-4 border-success">
                <div class="card-header bg-success text-white p-3 d-flex justify-content-between align-items-center rounded-top">
                    <h5 class="mb-0 font-bold fs-6"><i class="bi bi-shield-lock me-2"></i> {{ app()->getLocale() == 'ar' ? 'الخصوم وحقوق الملكية' : 'Liabilities & Equity' }}</h5>
                    <span class="badge bg-white text-success rounded-pill px-3 py-1 fs-7 font-bold">
                        {{ number_format($total_liabilities_and_equity, 2) }} {{ currency() }}
                    </span>
                </div>

                <div class="card-body p-0">
                    <!-- Section 1: Current Liabilities -->
                    <div class="bg-slate-50 p-3 border-bottom border-slate-200">
                        <h6 class="font-bold text-slate-800 mb-0 fs-7 text-uppercase">
                            <i class="bi bi-box-arrow-right me-1 text-warning"></i> {{ app()->getLocale() == 'ar' ? 'أولاً: الخصوم المتداولة' : 'Current Liabilities' }}
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-7">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">البيان / الحساب</th>
                                    <th class="text-end pe-4">{{ app()->getLocale() == 'ar' ? 'المبلغ (' . currency() . ')' : 'Amount (' . currency() . ')' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($current_liabilities as $item)
                                    <tr>
                                        <td class="ps-4 font-medium text-slate-700">{{ $item['name'] }}</td>
                                        <td class="text-end pe-4 font-semibold text-slate-900">{{ number_format($item['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-warning font-bold">
                                <tr>
                                    <td class="ps-4">إجمالي الخصوم المتداولة</td>
                                    <td class="text-end pe-4 text-dark">{{ number_format($total_current_liabilities, 2) }} {{ currency() }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Section 2: Long-Term Liabilities -->
                    <div class="bg-slate-50 p-3 border-bottom border-top border-slate-200">
                        <h6 class="font-bold text-slate-800 mb-0 fs-7 text-uppercase">
                            <i class="bi bi-bank2 me-1 text-danger"></i> {{ app()->getLocale() == 'ar' ? 'ثانياً: الخصوم طويلة الأجل' : 'Long-term Liabilities' }}
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-7">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">البيان / الحساب</th>
                                    <th class="text-end pe-4">{{ app()->getLocale() == 'ar' ? 'المبلغ (' . currency() . ')' : 'Amount (' . currency() . ')' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($long_term_liabilities as $item)
                                    <tr>
                                        <td class="ps-4 font-medium text-slate-700">{{ $item['name'] }}</td>
                                        <td class="text-end pe-4 font-semibold text-slate-900">{{ number_format($item['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-warning font-bold">
                                <tr>
                                    <td class="ps-4">إجمالي الخصوم بالكامل</td>
                                    <td class="text-end pe-4 text-danger">{{ number_format($total_liabilities, 2) }} {{ currency() }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Section 3: Equity -->
                    <div class="bg-slate-50 p-3 border-bottom border-top border-slate-200">
                        <h6 class="font-bold text-slate-800 mb-0 fs-7 text-uppercase">
                            <i class="bi bi-pie-chart me-1 text-emerald-600"></i> {{ app()->getLocale() == 'ar' ? 'ثالثاً: حقوق الملكية' : 'Equity' }}
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-7">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">البيان / الحساب</th>
                                    <th class="text-end pe-4">{{ app()->getLocale() == 'ar' ? 'المبلغ (' . currency() . ')' : 'Amount (' . currency() . ')' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equity_items as $item)
                                    <tr>
                                        <td class="ps-4 font-medium text-slate-700">{{ $item['name'] }}</td>
                                        <td class="text-end pe-4 font-semibold text-slate-900">{{ number_format($item['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-success font-bold">
                                <tr>
                                    <td class="ps-4">إجمالي حقوق الملكية</td>
                                    <td class="text-end pe-4 text-success">{{ number_format($total_equity, 2) }} {{ currency() }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Liabilities & Equity Footer Total -->
                <div class="card-footer bg-success text-white p-3 d-flex justify-content-between align-items-center">
                    <span class="font-bold fs-6">إجمالي الخصوم وحقوق الملكية:</span>
                    <span class="fs-5 font-bold">{{ number_format($total_liabilities_and_equity, 2) }} {{ currency() }}</span>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>

    <!-- Verification / Balancing Equation Note Card -->
    <div class="card border-0 shadow-sm rounded-4 text-white p-4 mb-4 {{ $is_balanced ? 'bg-success' : 'bg-danger' }}">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 text-center text-md-start">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-white {{ $is_balanced ? 'text-success' : 'text-danger' }} p-3 fs-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi {{ $is_balanced ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                </div>
                <div>
                    <h5 class="font-bold mb-1">
                        {{ $is_balanced ? '✅ ملاحظة محاسبية هامة: الميزانية العمومية متوازنة بالكامل' : '⚠️ القائمة غير متوازنة!' }}
                    </h5>
                    <p class="mb-0 opacity-90 fs-7">
                        معادلة الميزانية: <strong>إجمالي الأصول = إجمالي الخصوم + حقوق الملكية</strong>
                    </p>
                </div>
            </div>
            <div class="bg-white text-dark px-4 py-2 rounded-pill font-mono font-bold fs-6 shadow-sm">
                {{ number_format($total_assets, 2) }} = {{ number_format($total_liabilities, 2) }} + {{ number_format($total_equity, 2) }}
            </div>
        </div>
    </div>
</x-app-layout>
