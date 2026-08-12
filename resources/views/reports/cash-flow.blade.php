<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير', 'url' => route('reports.index')],
                ['label' => app()->getLocale() == 'ar' ? 'قائمة التدفقات النقدية' : 'Cash Flow Statement']
            ];
        @endphp
    </x-slot>

    <x-page-header title="{{ app()->getLocale() == 'ar' ? 'قائمة التدفقات النقدية' : 'Statement of Cash Flows' }}" description="تقرير التدفقات النقدية المقسم حسب الأنشطة التشغيلية والاستثمارية والتمويلية">
    </x-page-header>

    <!-- Filters Form Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.cash-flow') }}" class="row g-3 align-items-end">
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
                        <i class="bi bi-filter me-1"></i> تصفية
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-custom border-0 shadow-sm p-3 border-start border-4 border-primary">
                <span class="fs-8 text-slate-500 uppercase font-bold">صافي التدفقات التشغيلية</span>
                <h4 class="font-bold text-primary mb-0 mt-1">{{ number_format($net_operating_cash, 2) }} <small class="fs-8 text-slate-500">{{ currency() }}</small></h4>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-custom border-0 shadow-sm p-3 border-start border-4 border-info">
                <span class="fs-8 text-slate-500 uppercase font-bold">النقدية بداية الفترة</span>
                <h4 class="font-bold text-info mb-0 mt-1">{{ number_format($opening_cash, 2) }} <small class="fs-8 text-slate-500">{{ currency() }}</small></h4>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-custom border-0 shadow-sm p-3 border-start border-4 border-success">
                <span class="fs-8 text-slate-500 uppercase font-bold">النقدية نهاية الفترة</span>
                <h4 class="font-bold text-success mb-0 mt-1">{{ number_format($ending_cash, 2) }} <small class="fs-8 text-slate-500">{{ currency() }}</small></h4>
            </div>
        </div>
    </div>

    <!-- Cash Flow Table Card -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-white p-3 border-bottom border-slate-200">
            <h5 class="mb-0 font-bold text-slate-800 fs-6"><i class="bi bi-water me-2 text-primary"></i> تفاصيل قائمة التدفقات النقدية</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-7">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">النشاط / البيان</th>
                        <th class="text-end pe-4">{{ app()->getLocale() == 'ar' ? 'المبلغ (' . currency() . ')' : 'Amount (' . currency() . ')' }}</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Operating -->
                    <tr class="table-light font-bold">
                        <td colspan="2" class="ps-4"><i class="bi bi-gear me-1 text-primary"></i> {{ app()->getLocale() == 'ar' ? 'أولاً: التدفقات النقدية من الأنشطة التشغيلية' : 'Operating Activities' }}</td>
                    </tr>
                    <tr>
                        <td class="ps-5">المتحصلات النقدية من العملاء (المبيعات)</td>
                        <td class="text-end pe-4 text-success font-semibold">{{ number_format($customer_collections, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-5">(-) المدفوعات النقدية للموردين (المشتريات)</td>
                        <td class="text-end pe-4 text-danger font-semibold">({{ number_format($supplier_payments, 2) }})</td>
                    </tr>
                    <tr>
                        <td class="ps-5">(-) المدفوعات النقدية للمصروفات التشغيلية والرواتب</td>
                        <td class="text-end pe-4 text-danger font-semibold">({{ number_format($operating_expenses_paid, 2) }})</td>
                    </tr>
                    <tr class="table-primary font-bold">
                        <td class="ps-4">صافي التدفقات النقدية من الأنشطة التشغيلية</td>
                        <td class="text-end pe-4 text-primary fs-6">{{ number_format($net_operating_cash, 2) }}</td>
                    </tr>

                    <!-- Investing -->
                    <tr class="table-light font-bold">
                        <td colspan="2" class="ps-4"><i class="bi bi-building me-1 text-info"></i> {{ app()->getLocale() == 'ar' ? 'ثانياً: التدفقات النقدية من الأنشطة الاستثمارية' : 'Investing Activities' }}</td>
                    </tr>
                    <tr>
                        <td class="ps-5">مشتريات/استثمارات في أصول ثابتة ومعدات</td>
                        <td class="text-end pe-4 font-semibold text-slate-700">{{ number_format($net_investing_cash, 2) }}</td>
                    </tr>
                    <tr class="table-info font-bold">
                        <td class="ps-4">صافي التدفقات النقدية من الأنشطة الاستثمارية</td>
                        <td class="text-end pe-4 text-info fs-6">{{ number_format($net_investing_cash, 2) }}</td>
                    </tr>

                    <!-- Financing -->
                    <tr class="table-light font-bold">
                        <td colspan="2" class="ps-4"><i class="bi bi-piggy-bank me-1 text-warning"></i> {{ app()->getLocale() == 'ar' ? 'ثالثاً: التدفقات النقدية من الأنشطة التمويلية' : 'Financing Activities' }}</td>
                    </tr>
                    <tr>
                        <td class="ps-5">سداد/تحصيل قروض أو إضافات رأس المال</td>
                        <td class="text-end pe-4 font-semibold text-slate-700">{{ number_format($net_financing_cash, 2) }}</td>
                    </tr>
                    <tr class="table-warning font-bold">
                        <td class="ps-4">صافي التدفقات النقدية من الأنشطة التمويلية</td>
                        <td class="text-end pe-4 text-dark fs-6">{{ number_format($net_financing_cash, 2) }}</td>
                    </tr>

                    <!-- Summary Net Increase -->
                    <tr class="table-dark font-bold">
                        <td class="ps-4">صافي التغير في النقدية خلال الفترة</td>
                        <td class="text-end pe-4 fs-6">{{ number_format($net_cash_change, 2) }} {{ currency() }}</td>
                    </tr>
                    <tr class="table-success font-bold">
                        <td class="ps-4">النقدية وما في حكمها نهاية الفترة</td>
                        <td class="text-end pe-4 fs-5 text-success">{{ number_format($ending_cash, 2) }} {{ currency() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
