<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h4 mb-1 font-bold text-slate-900">
                    <i class="bi bi-speedometer2 text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'لوحة التحكم الإدارية والنظام الشامل' : 'ERP Executive Dashboard' }}
                </h2>
                <p class="text-muted fs-7 mb-0">
                    {{ app()->getLocale() == 'ar' ? 'متابعة حية للمبيعات، المشتريات، المخازن، والسيولة المالية برؤية مستقبلية' : 'Live overview of sales, purchases, warehouse stock, and finances' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pos.index') }}" class="btn btn-primary font-bold rounded-3 px-3.5 py-2 shadow-sm">
                    <i class="bi bi-display me-1.5"></i>{{ app()->getLocale() == 'ar' ? 'شاشة الكاشير (POS)' : 'POS Terminal' }}
                </a>
                <a href="{{ route('invoices.create') }}" class="btn btn-outline-primary font-semibold rounded-3 px-3.5 py-2">
                    <i class="bi bi-plus-circle me-1.5"></i>{{ app()->getLocale() == 'ar' ? 'فاتورة مبيعات جديدة' : 'New Sales Invoice' }}
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Executive KPI Cards Grid (Original ERP Metrics with Unified UX/UI) -->
    <div class="row g-3.5 mb-4">
        <!-- 1. Total Sales Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                :title="app()->getLocale() == 'ar' ? 'إجمالي المبيعات' : 'Total Sales'"
                :value="number_format($totalSales, 2)"
                :currency="setting('currency', 'SDG')"
                :subtitle="$invoicesCount . ' ' . (app()->getLocale() == 'ar' ? 'فاتورة صادرة' : 'Invoices')"
                icon="bi-graph-up-arrow"
                color="primary"
                :url="route('invoices.index')"
                :actionText="app()->getLocale() == 'ar' ? 'عرض المبيعات ←' : 'View Sales ←'"
                :infoTooltip="app()->getLocale() == 'ar' ? 'إجمالي قيمة فواتير المبيعات المحققة' : 'Total Sales Amount'" />
        </div>

        <!-- 2. Total Purchases Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                :title="app()->getLocale() == 'ar' ? 'إجمالي المشتريات' : 'Total Purchases'"
                :value="number_format($totalPurchases, 2)"
                :currency="setting('currency', 'SDG')"
                :subtitle="$purchasesCount . ' ' . (app()->getLocale() == 'ar' ? 'أمر توريد' : 'Orders')"
                icon="bi-cart-check-fill"
                color="emerald"
                :url="route('purchases.index')"
                :actionText="app()->getLocale() == 'ar' ? 'عرض المشتريات ←' : 'View Purchases ←'"
                :infoTooltip="app()->getLocale() == 'ar' ? 'إجمالي قيمة مشتريات وأوامر التوريد' : 'Total Purchases Amount'" />
        </div>

        <!-- 3. Warehouses & Stock Items Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                :title="app()->getLocale() == 'ar' ? 'المخازن والأصناف' : 'Warehouses & Stock'"
                :value="number_format($itemsCount)"
                :subtitle="$warehousesCount . ' ' . (app()->getLocale() == 'ar' ? 'مخازن نشطة' : 'Active Warehouses')"
                icon="bi-boxes"
                color="info"
                :url="route('inventory.index')"
                :actionText="app()->getLocale() == 'ar' ? 'عرض المخزون ←' : 'View Stock ←'"
                :infoTooltip="app()->getLocale() == 'ar' ? 'إجمالي الأصناف المتاحة بالمخازن النشطة' : 'Total Registered Stock Items'" />
        </div>

        <!-- 4. Customers & Suppliers Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-kpi-card 
                :title="app()->getLocale() == 'ar' ? 'العملاء والموردون' : 'Clients & Vendors'"
                :value="number_format($customersCount + $suppliersCount)"
                :subtitle="$customersCount . ' ' . (app()->getLocale() == 'ar' ? 'عميل' : 'Clients') . ' / ' . $suppliersCount . ' ' . (app()->getLocale() == 'ar' ? 'مورد' : 'Vendors')"
                icon="bi-people-fill"
                color="warning"
                :url="route('customers.index')"
                :actionText="app()->getLocale() == 'ar' ? 'عرض العملاء ←' : 'View Clients ←'"
                :infoTooltip="app()->getLocale() == 'ar' ? 'إجمالي عدد العملاء والموردين النشطين' : 'Total Active Clients & Vendors'" />
        </div>
    </div>




    @if(feature_enabled('quick_actions_enabled'))
    <!-- Quick Shortcuts Toolbar (Equal Grid & Height) -->
    <div class="card border shadow-sm rounded-4 p-3 mb-4 bg-white" style="border: 1.5px solid rgba(var(--bs-secondary-rgb, 15, 23, 42), 0.14) !important;">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="font-bold text-slate-900 mb-0">
                <i class="bi bi-lightning-charge-fill text-amber-500 me-1.5"></i>{{ app()->getLocale() == 'ar' ? 'اختصارات الإجراءات السريعة' : 'Quick Actions' }}
            </h6>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-3 align-items-stretch">
            <div class="col">
                <a href="{{ route('pos.index') }}" class="btn btn-light border w-100 h-100 py-3 px-2 rounded-3 hover-lift text-slate-800 font-bold fs-7 d-flex flex-column align-items-center justify-content-center gap-1.5 shadow-2xs">
                    <i class="bi bi-display text-primary fs-4"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'الكاشير POS' : 'POS Cashier' }}</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('invoices.create') }}" class="btn btn-light border w-100 h-100 py-3 px-2 rounded-3 hover-lift text-slate-800 font-bold fs-7 d-flex flex-column align-items-center justify-content-center gap-1.5 shadow-2xs">
                    <i class="bi bi-plus-square text-success fs-4"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'فاتورة مبيعات' : 'New Invoice' }}</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('purchases.create_invoice') }}" class="btn btn-light border w-100 h-100 py-3 px-2 rounded-3 hover-lift text-slate-800 font-bold fs-7 d-flex flex-column align-items-center justify-content-center gap-1.5 shadow-2xs">
                    <i class="bi bi-bag-plus text-info fs-4"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'طلب توريد' : 'New Purchase' }}</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('inventory.index') }}" class="btn btn-light border w-100 h-100 py-3 px-2 rounded-3 hover-lift text-slate-800 font-bold fs-7 d-flex flex-column align-items-center justify-content-center gap-1.5 shadow-2xs">
                    <i class="bi bi-box-seam text-warning fs-4"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'المخزن والأصناف' : 'Inventory' }}</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('accounting.index') }}" class="btn btn-light border w-100 h-100 py-3 px-2 rounded-3 hover-lift text-slate-800 font-bold fs-7 d-flex flex-column align-items-center justify-content-center gap-1.5 shadow-2xs">
                    <i class="bi bi-diagram-3 text-purple-600 fs-4"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'شجرة الحسابات' : 'Chart of Accts' }}</span>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('reports.index') }}" class="btn btn-light border w-100 h-100 py-3 px-2 rounded-3 hover-lift text-slate-800 font-bold fs-7 d-flex flex-column align-items-center justify-content-center gap-1.5 shadow-2xs">
                    <i class="bi bi-pie-chart text-rose-500 fs-4"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'التقارير الشاملة' : 'Reports Hub' }}</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Visual Analytics & Charts Section -->
    <div class="row g-4 mb-4">
        <!-- 6-Month Sales vs Purchases Bar Chart -->
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm rounded-4 p-4 bg-white h-100" style="border: 1.5px solid rgba(var(--bs-secondary-rgb, 15, 23, 42), 0.14) !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="font-bold text-slate-900 mb-0">
                            <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'مقارنة المبيعات والمشتريات خلال الـ 6 أشهر الماضية' : 'Sales vs Purchases (6 Months)' }}
                        </h6>
                        <small class="text-muted fs-7">{{ app()->getLocale() == 'ar' ? 'تحليل حجم التدفقات التجارية والتوريد' : 'Monthly volume comparison' }}</small>
                    </div>
                </div>
                <div class="position-relative" style="height: 260px;">
                    <canvas id="dashboardSalesPurchasesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Doughnut Distribution Chart -->
        <div class="col-12 col-lg-4">
            <div class="card border shadow-sm rounded-4 p-4 bg-white h-100" style="border: 1.5px solid rgba(var(--bs-secondary-rgb, 15, 23, 42), 0.14) !important;">
                <div class="mb-3">
                    <h6 class="font-bold text-slate-900 mb-0">
                        <i class="bi bi-pie-chart-fill text-success me-2"></i>{{ app()->getLocale() == 'ar' ? 'نسبة المبيعات إلى المشتريات' : 'Sales vs Purchases Ratio' }}
                    </h6>
                    <small class="text-muted fs-7">{{ app()->getLocale() == 'ar' ? 'التوزيع الإجمالي' : 'Overall Distribution' }}</small>
                </div>
                <div class="position-relative d-flex align-items-center justify-content-center" style="height: 240px;">
                    <canvas id="dashboardRatioPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Recent Sales Invoices Row (Full Width Row) -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border shadow-sm rounded-4 bg-white overflow-hidden" style="border: 1.5px solid rgba(var(--bs-secondary-rgb, 15, 23, 42), 0.14) !important;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="font-bold text-slate-900 mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i>{{ app()->getLocale() == 'ar' ? 'أحدث فواتير المبيعات الصادرة' : 'Recent Sales Invoices' }}
                    </h6>
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fs-7 font-bold">
                        {{ app()->getLocale() == 'ar' ? 'عرض كافة الفواتير' : 'View All Invoices' }}
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light fs-7">
                            <tr>
                                <th class="ps-4" style="width: 18%;">{{ app()->getLocale() == 'ar' ? 'رقم الفاتورة' : 'Invoice #' }}</th>
                                <th style="width: 32%;">{{ app()->getLocale() == 'ar' ? 'اسم العميل' : 'Customer Name' }}</th>
                                <th style="width: 20%;">{{ app()->getLocale() == 'ar' ? 'تاريخ الفاتورة' : 'Invoice Date' }}</th>
                                <th class="text-end" style="width: 18%;">{{ app()->getLocale() == 'ar' ? 'الإجمالي' : 'Total Amount' }}</th>
                                <th class="pe-4 text-center" style="width: 12%;">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($recentInvoices as $inv)
                                @php
                                    $invObj = (is_object($inv) && get_class($inv) !== '__PHP_Incomplete_Class') ? $inv : null;
                                    $invArr = is_array($inv) ? $inv : [];
                                    $invNumber = $invObj?->invoice_number ?? ($invArr['invoice_number'] ?? (is_string($inv) ? $inv : '---'));
                                    $customerName = $invObj?->customer?->name ?? ($invArr['customer']['name'] ?? 'عميل نقدي');
                                    $totalAmount = $invObj?->total_amount ?? ($invArr['total_amount'] ?? 0);
                                    $statusVal = $invObj?->status ?? ($invArr['status'] ?? 'pending');
                                    $invId = $invObj?->id ?? ($invArr['id'] ?? (is_scalar($inv) ? $inv : 1));
                                    $invDate = $invArr['date'] ?? ($invObj?->invoice_date ? $invObj->invoice_date->format('Y-m-d') : '-');
                                @endphp
                                <tr>
                                    <td class="ps-4 font-mono font-bold">
                                        <a href="{{ route('invoices.show', $invId) }}" class="text-decoration-none text-primary">{{ $invNumber }}</a>
                                    </td>
                                    <td class="font-medium text-slate-800">{{ $customerName }}</td>
                                    <td class="font-mono text-muted">{{ $invDate }}</td>
                                    <td class="text-end font-mono font-bold text-slate-900">{{ number_format((float)$totalAmount, 2) }} {{ setting('currency', 'SDG') }}</td>
                                    <td class="pe-4 text-center">
                                        <x-status-badge :status="$statusVal" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">{{ app()->getLocale() == 'ar' ? 'لا توجد فواتير مبيعات مسجلة مؤخراً' : 'No recent invoices.' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Inventory Stock Levels Row (Full Width Row) -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="font-bold text-slate-900 mb-0">
                        <i class="bi bi-box-seam text-warning me-2"></i>{{ app()->getLocale() == 'ar' ? 'حالة المخزون والأصناف المتاحة' : 'Inventory Stock Overview' }}
                    </h6>
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fs-7 font-bold">
                        {{ app()->getLocale() == 'ar' ? 'إدارة المخزن' : 'Manage Inventory' }}
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light fs-7">
                            <tr>
                                <th class="ps-4" style="width: 15%;">{{ app()->getLocale() == 'ar' ? 'كود الصنف' : 'Code' }}</th>
                                <th style="width: 20%;">{{ app()->getLocale() == 'ar' ? 'التصنيف' : 'Category' }}</th>
                                <th style="width: 45%;">{{ app()->getLocale() == 'ar' ? 'اسم الصنف والمنتج' : 'Item & Product Name' }}</th>
                                <th class="pe-4 text-end" style="width: 20%;">{{ app()->getLocale() == 'ar' ? 'الرصيد الكلي المتاح' : 'Available Stock' }}</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($lowStockItems as $item)
                                @php
                                    $itemObj = is_object($item) ? $item : null;
                                    $itemArr = is_array($item) ? $item : [];
                                    
                                    if ($itemObj) {
                                        $stk = $itemObj->warehouseItems ? $itemObj->warehouseItems->sum('qty_in_base_units') : 0;
                                        $itemCode = $itemObj->item_code ?? '';
                                        $categoryName = $itemObj->category?->name ?? 'عام';
                                        $itemName = $itemObj->name ?? '';
                                        $unitName = $itemObj->baseUnit?->name ?? 'قطعة';
                                    } elseif ($itemArr) {
                                        $stk = isset($itemArr['warehouse_items']) ? collect($itemArr['warehouse_items'])->sum('qty_in_base_units') : 0;
                                        $itemCode = $itemArr['item_code'] ?? '';
                                        $categoryName = $itemArr['category']['name'] ?? 'عام';
                                        $itemName = $itemArr['name'] ?? '';
                                        $unitName = $itemArr['base_unit']['name'] ?? 'قطعة';
                                    } else {
                                        $stk = 0;
                                        $itemCode = is_string($item) ? $item : '';
                                        $categoryName = 'عام';
                                        $itemName = '';
                                        $unitName = 'قطعة';
                                    }
                                @endphp
                                <tr>
                                    <td class="ps-4 font-mono font-bold text-primary">{{ $itemCode }}</td>
                                    <td><span class="badge bg-slate-100 text-slate-700 font-medium px-2.5 py-1">{{ $categoryName }}</span></td>
                                    <td class="font-bold text-slate-900 fs-6">{{ $itemName }}</td>
                                    <td class="pe-4 text-end font-mono">
                                        <span class="badge {{ $stk > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} px-3 py-1.5 rounded-pill fs-7 font-bold">
                                            {{ number_format($stk, 0) }} {{ $unitName }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">{{ app()->getLocale() == 'ar' ? 'لا توجد أصناف مخزنية' : 'No items found.' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Bar Chart: 6-Month Sales vs Purchases
            const ctx1 = document.getElementById('dashboardSalesPurchasesChart');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: @json($months),
                        datasets: [
                            {
                                label: '{{ app()->getLocale() == "ar" ? "المبيعات (ر.س)" : "Sales (SAR)" }}',
                                data: @json($salesMonthly),
                                backgroundColor: '#2563eb',
                                borderRadius: 6
                            },
                            {
                                label: '{{ app()->getLocale() == "ar" ? "المشتريات (ر.س)" : "Purchases (SAR)" }}',
                                data: @json($purchasesMonthly),
                                backgroundColor: '#10b981',
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            // Doughnut Chart: Ratio
            const ctx2 = document.getElementById('dashboardRatioPieChart');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['{{ app()->getLocale() == "ar" ? "إجمالي المبيعات" : "Total Sales" }}', '{{ app()->getLocale() == "ar" ? "إجمالي المشتريات" : "Total Purchases" }}'],
                        datasets: [{
                            data: [{{ max(1, $totalSales) }}, {{ max(1, $totalPurchases) }}],
                            backgroundColor: ['#2563eb', '#10b981'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        cutout: '70%'
                    }
                });
            }
        });
    </script>
</x-app-layout>
