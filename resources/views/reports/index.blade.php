<x-app-layout>
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['label' => __('reports.reports_hub') ?? 'مركز التقارير']
            ];
        @endphp
    </x-slot>

    <x-page-header :title="__('reports.reports_hub') ?? 'مركز التقارير الشامل'" :description="__('reports.hub_desc') ?? 'استعراض واستخراج التقارير المالية والإدارية والتشغيلية لكافة قطاعات المنشأة'">
        <x-slot name="actions">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6 rounded-pill">
                <i class="bi bi-shield-check me-1"></i> خاضع للصلاحيات ونطاق الفروع
            </span>
        </x-slot>
    </x-page-header>

    <!-- Report Category Cards Grid -->
    <div class="row g-4 mb-5">
        <!-- 0. Core Financial Statements (القوائم المالية والختامية الرئيسية) -->
        <div class="col-12">
            <div class="card card-custom border-0 border-top border-4 border-emerald-600 shadow-sm bg-white p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle bg-emerald-100 p-3 text-emerald-700 fs-3">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-0">القوائم المالية والتقارير المحاسبية الختامية (Financial Statements)</h4>
                        <p class="text-slate-500 mb-0 fs-7">القوائم والتقارير المحاسبية المعتمدة وفق المعايير الدولية والتقارير العامة</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6 col-lg-3">
                        <a href="{{ route('reports.balance-sheet') }}" class="card h-100 text-decoration-none border border-slate-200 hover-shadow p-3 rounded-3 transition-all">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary-subtle text-primary font-bold px-2 py-1 fs-8">الصورة 1</span>
                                <i class="bi bi-bank fs-4 text-primary"></i>
                            </div>
                            <h6 class="font-bold text-slate-900 mb-1">قائمة المركز المالي (الميزانية العمومية)</h6>
                            <p class="fs-8 text-slate-500 mb-0">الأصول، الخصوم، حقوق الملكية معادلة الميزانية `أصول = خصوم + ملكية`</p>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <a href="{{ route('reports.income-statement') }}" class="card h-100 text-decoration-none border border-slate-200 hover-shadow p-3 rounded-3 transition-all">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-success-subtle text-success font-bold px-2 py-1 fs-8">الصورة 2</span>
                                <i class="bi bi-graph-up-arrow fs-4 text-success"></i>
                            </div>
                            <h6 class="font-bold text-slate-900 mb-1">قائمة الدخل (الأرباح والخسائر)</h6>
                            <p class="fs-8 text-slate-500 mb-0">المبيعات، صافي المبيعات، COGS، الربح التشغيلي والصافي بعد الضريبة</p>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <a href="{{ route('reports.trial-balance') }}" class="card h-100 text-decoration-none border border-slate-200 hover-shadow p-3 rounded-3 transition-all">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-info-subtle text-info font-bold px-2 py-1 fs-8">ميزان المراجعة</span>
                                <i class="bi bi-card-checklist fs-4 text-info"></i>
                            </div>
                            <h6 class="font-bold text-slate-900 mb-1">{{ app()->getLocale() == 'ar' ? 'ميزان المراجعة' : 'Trial Balance' }}</h6>
                            <p class="fs-8 text-slate-500 mb-0">أرصدة وحركات الحسابات (مدين ودائن) والتحقق من التوازن المحاسبي</p>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <a href="{{ route('reports.cash-flow') }}" class="card h-100 text-decoration-none border border-slate-200 hover-shadow p-3 rounded-3 transition-all">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-warning-subtle text-warning font-bold px-2 py-1 fs-8">التدفقات النقدية</span>
                                <i class="bi bi-water fs-4 text-warning"></i>
                            </div>
                            <h6 class="font-bold text-slate-900 mb-1">قائمة التدفقات النقدية (Cash Flows)</h6>
                            <p class="fs-8 text-slate-500 mb-0">الأنشطة التشغيلية، الاستثمارية، والتمويلية وصافي حركة النقدية</p>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('reports.equity-changes') }}" class="card h-100 text-decoration-none border border-slate-200 hover-shadow p-3 rounded-3 transition-all">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-secondary-subtle text-secondary font-bold px-2 py-1 fs-8">حقوق الملكية</span>
                                <i class="bi bi-pie-chart fs-4 text-secondary"></i>
                            </div>
                            <h6 class="font-bold text-slate-900 mb-1">قائمة التغيرات في حقوق الملكية</h6>
                            <p class="fs-8 text-slate-500 mb-0">رأس المال، المسحوبات، الإضافات، الأرباح المحتجزة وأرباح الفترة</p>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('reports.general-ledger') }}" class="card h-100 text-decoration-none border border-slate-200 hover-shadow p-3 rounded-3 transition-all">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-dark text-white font-bold px-2 py-1 fs-8">الأستاذ العام</span>
                                <i class="bi bi-journal-bookmark fs-4 text-dark"></i>
                            </div>
                            <h6 class="font-bold text-slate-900 mb-1">تقرير دفتر الأستاذ العام (General Ledger)</h6>
                            <p class="fs-8 text-slate-500 mb-0">كشف حساب تفصيلي لأي حساب من الشجرة مع القيود والرصيد الجاري</p>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('reports.account-balances') }}" class="card h-100 text-decoration-none border border-slate-200 hover-shadow p-3 rounded-3 transition-all">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-emerald-100 text-emerald-800 font-bold px-2 py-1 fs-8">شجرة الحسابات</span>
                                <i class="bi bi-diagram-3 fs-4 text-emerald-600"></i>
                            </div>
                            <h6 class="font-bold text-slate-900 mb-1">كشف أرصدة شجرة الحسابات</h6>
                            <p class="fs-8 text-slate-500 mb-0">ملخص أرصدة كافة الحسابات الرئيسية والفرعية بحسب الشجرة</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. Financial & Ledgers -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-custom h-100 border-0 border-top border-4 border-success shadow-sm card-clickable">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-success-subtle p-3 text-success fs-4">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-slate-900 mb-0">التقارير المالية وكشوف الحسابات</h5>
                            <small class="text-slate-500">كشوف حسابات العملاء والموردين وحركة الخزن</small>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush border-0 fs-7">
                        <li class="list-group-item bg-transparent px-0 py-2 border-slate-100">
                            <a href="{{ route('reports.financial-comparison') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                <span><i class="bi bi-arrows-collapse text-primary me-2"></i>شاشة مقارنة الفترات المالية (% Variance)</span>
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                        <li class="list-group-item bg-transparent px-0 py-2 border-slate-100">
                            <a href="{{ route('reports.customer-statement') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                <span><i class="bi bi-person-vcard text-info me-2"></i>كشف حساب عميل تفصيلي</span>
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                        <li class="list-group-item bg-transparent px-0 py-2 border-slate-100">
                            <a href="{{ route('reports.supplier-statement') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                <span><i class="bi bi-truck-front text-warning me-2"></i>كشف حساب مورد تفصيلي</span>
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                        <li class="list-group-item bg-transparent px-0 py-2 border-0">
                            <a href="{{ route('reports.financial') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                <span><i class="bi bi-wallet2 text-success me-2"></i>حركة الخزن والسندات النقدية</span>
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 2. Sales Reports -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-custom h-100 border-0 border-top border-4 border-primary shadow-sm card-clickable">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary-subtle p-3 text-primary fs-4">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-slate-900 mb-0">تقارير المبيعات والربحية</h5>
                            <small class="text-slate-500">تحليل الفواتير، الأصناف الأربح، والضرائب</small>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush border-0 fs-7">
                        <li class="list-group-item bg-transparent px-0 py-2 border-slate-100">
                            <a href="{{ route('reports.profitable-items') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                <span><i class="bi bi-trophy text-emerald-600 me-2"></i>تقرير الأصناف الأكثر ربحية (Margin %)</span>
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                        <li class="list-group-item bg-transparent px-0 py-2 border-slate-100">
                            <a href="{{ route('reports.sales') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                <span><i class="bi bi-receipt text-primary me-2"></i>تقرير المبيعات والفواتير</span>
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                        <li class="list-group-item bg-transparent px-0 py-2 border-0">
                            <a href="{{ route('reports.sales', ['status' => 'paid']) }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                <span><i class="bi bi-check-circle text-success me-2"></i>المبيعات المحصلة والضرائب</span>
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 5. Inventory & Purchases -->
        @if (setting('inventory_enabled', true))
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-custom h-100 border-0 border-top border-4 border-secondary shadow-sm card-clickable">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-secondary-subtle p-3 text-secondary fs-4">
                                <i class="bi bi-boxes"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-900 mb-0">تقارير المخزون والمشتريات</h5>
                                <small class="text-slate-500">أرصدة المواد وحركات المخزن والتوريدات</small>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush border-0 fs-7">
                            <li class="list-group-item bg-transparent px-0 py-2 border-slate-100">
                                <a href="{{ route('inventory.item-card') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                    <span><i class="bi bi-card-checklist text-info me-2"></i>جرد الأصناف وكارت الحركة</span>
                                    <i class="bi bi-arrow-left"></i>
                                </a>
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2 border-slate-100">
                                <a href="{{ route('reports.warehouse-inventory') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                    <span><i class="bi bi-clipboard-check text-primary me-2"></i>تقرير جرد المخزن (تفصيلي بالكميات والقيم)</span>
                                    <i class="bi bi-arrow-left"></i>
                                </a>
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2 border-0">
                                <a href="{{ route('reports.inventory') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                    <span><i class="bi bi-box-seam text-secondary me-2"></i>حركة وأرصدة المخزون العام</span>
                                    <i class="bi bi-arrow-left"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
