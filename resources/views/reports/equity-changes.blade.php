<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => 'قائمة التغيرات في حقوق الملكية']
            ];
        @endphp
    </x-slot>

    <x-page-header title="{{ app()->getLocale() == 'ar' ? 'قائمة التغيرات في حقوق الملكية' : 'Statement of Changes in Equity' }}" description="تتبع التغيرات في رأس المال والأرباح المحتجزة وأرباح الفترة والمسحوبات">
    </x-page-header>

    <!-- Filters Form Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.equity-changes') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ $from_date }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label fs-7 font-bold text-slate-700 mb-1">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ $to_date }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">
                        <i class="bi bi-filter me-1"></i> عرض
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Equity Table Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-white p-3 border-bottom border-slate-200">
            <h5 class="mb-0 font-bold text-slate-800 fs-6"><i class="bi bi-pie-chart me-2 text-emerald-600"></i> حركات التغير في حقوق الملكية</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-7">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">البيان / الحركة</th>
                        <th class="text-end">رأس المال</th>
                        <th class="text-end">أرباح محتجزة / الفترة</th>
                        <th class="text-end pe-4">إجمالي حقوق الملكية</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-4 font-semibold text-slate-800">الرصيد في بداية الفترة ({{ $from_date }})</td>
                        <td class="text-end font-mono">{{ number_format($opening_capital, 2) }}</td>
                        <td class="text-end font-mono">0.00</td>
                        <td class="text-end pe-4 font-mono font-bold">{{ number_format($opening_capital, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-slate-600">(+) إضافات على رأس المال خلال الفترة</td>
                        <td class="text-end font-mono text-success">{{ number_format($capital_additions, 2) }}</td>
                        <td class="text-end font-mono">0.00</td>
                        <td class="text-end pe-4 font-mono text-success">{{ number_format($capital_additions, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-slate-600">(-) المسحوبات الشخصية للشركاء / المالك</td>
                        <td class="text-end font-mono text-danger">({{ number_format($owner_drawings, 2) }})</td>
                        <td class="text-end font-mono">0.00</td>
                        <td class="text-end pe-4 font-mono text-danger">({{ number_format($owner_drawings, 2) }})</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-slate-600">(+) صافي ربح الفترة المالية الحالية</td>
                        <td class="text-end font-mono">0.00</td>
                        <td class="text-end font-mono text-emerald-600 font-bold">{{ number_format($net_profit_period, 2) }}</td>
                        <td class="text-end pe-4 font-mono text-emerald-600 font-bold">{{ number_format($net_profit_period, 2) }}</td>
                    </tr>
                    <tr class="table-success font-bold fs-6">
                        <td class="ps-4">الرصيد النهائي لحقوق الملكية في ({{ $to_date }})</td>
                        <td class="text-end font-mono text-slate-900">{{ number_format($ending_capital, 2) }}</td>
                        <td class="text-end font-mono text-emerald-700">{{ number_format($net_profit_period, 2) }}</td>
                        <td class="text-end pe-4 font-mono text-success">{{ number_format($ending_equity, 2) }} {{ currency() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
