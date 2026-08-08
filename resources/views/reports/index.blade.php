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
                            <h5 class="font-bold text-slate-900 mb-0">تقارير المبيعات والإيرادات</h5>
                            <small class="text-slate-500">تحليل الفواتير وعروض الأسعار وحساب الضرائب</small>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush border-0 fs-7">
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
                                <a href="{{ route('reports.inventory') }}" class="text-decoration-none text-slate-700 hover-primary font-medium d-flex justify-content-between">
                                    <span><i class="bi bi-box-seam text-secondary me-2"></i>حركة وأرصدة المخزون</span>
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
