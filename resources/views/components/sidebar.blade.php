<aside id="sidebar" class="bg-dark text-white border-end flex-shrink-0 transition-all p-2 pt-0 d-flex flex-column align-self-stretch" style="min-height: 100%; height: auto; position: relative; z-index: 1020; padding-top: 0 !important;">
    <div class="sidebar-sticky-wrapper d-flex flex-column flex-grow-1 sticky-top overflow-y-auto custom-scrollbar" style="top: var(--header-height, 70px); height: calc(100vh - var(--header-height, 70px)); overscroll-behavior: contain; overscroll-behavior-y: contain;">
        <!-- Sidebar Header -->
        <div class="sidebar-header pt-3 pb-2 mb-1 border-bottom border-slate-800 text-center px-2 flex-shrink-0 position-relative">
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2.5 d-lg-none" id="closeMobileSidebar" aria-label="Close"></button>
            <div class="d-flex flex-column align-items-center justify-content-start gap-0.5 overflow-hidden w-100">
                @php $sysLogo = setting('logo'); @endphp
                @if($sysLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($sysLogo))
                    <img src="{{ asset('storage/' . $sysLogo) }}" alt="Logo" class="img-fluid object-contain sidebar-text transition-all w-100 mt-1" style="max-height: 90px; width: 100%; max-width: 100%; object-fit: contain;">
                @else
                    <div class="rounded-circle bg-primary-subtle p-2 mb-1 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-shop-window text-primary fs-2"></i>
                    </div>
                @endif

                <div class="sidebar-text text-center w-100 px-1 mt-0.5">
                    <h6 class="fs-6 font-bold text-white mb-0 text-truncate tracking-tight">
                        {{ setting('facility_name', app()->getLocale() == 'ar' ? 'إدارة المبيعات والمشتريات والمخازن' : 'Sales & Inventory ERP') }}
                    </h6>
                    <small class="text-slate-400 fs-8 font-medium d-block mt-0">
                        {{ app()->getLocale() == 'ar' ? 'منظومة إدارة المؤسسة' : 'Enterprise ERP System' }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu Search -->
        <div class="sidebar-text px-1 mb-1.5 flex-shrink-0">
            <div class="position-relative">
                <input type="text" 
                       id="sidebarMenuSearchInput" 
                       class="form-control form-control-sm bg-slate-900 border-slate-700 text-white placeholder-slate-400 ps-4 py-1.5 rounded-3 fs-7" 
                       placeholder="{{ app()->getLocale() == 'ar' ? 'البحث في القائمة' : 'Search menu' }}"
                       style="font-size: 0.85rem;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2.5 text-slate-400 fs-7"></i>
            </div>
        </div>

        <!-- Navigation Accordion -->
        <div class="sidebar-menu-wrapper flex-grow-1 pe-1" id="sidebarAccordion">
        <ul class="nav nav-pills flex-column gap-1 list-unstyled mb-0">

            <!-- 1. لوحة التحكم -->
            <li class="sidebar-nav-item">
                <a href="{{ route('dashboard') }}" 
                   class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}" 
                   title="{{ app()->getLocale() == 'ar' ? 'لوحة التحكم' : 'Dashboard' }}">
                    <i class="bi bi-speedometer2 fs-5 text-primary"></i>
                    <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'لوحة التحكم' : 'Dashboard' }}</span>
                </a>
            </li>



            <!-- 2. البيانات الأساسية -->
            @php
                $isLookupsActive = request()->routeIs('units.*') || request()->routeIs('categories.*') || request()->routeIs('warehouses.*');
            @endphp
            <li class="sidebar-nav-item" x-data="{ open: {{ $isLookupsActive ? 'true' : 'false' }} }">
                <button class="sidebar-group-btn {{ $isLookupsActive ? 'active' : '' }}" 
                        type="button" 
                        @click="open = !open" 
                        :aria-expanded="open ? 'true' : 'false'">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-sliders fs-5 text-primary"></i>
                        <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'البيانات الأساسية' : 'Master Data' }}</span>
                    </div>
                    <i class="bi bi-chevron-down chevron-icon sidebar-text"></i>
                </button>
                <div class="sidebar-group-menu mt-1" x-show="open">
                    <ul class="list-unstyled ps-0 mb-0 d-flex flex-column gap-1">
                        <li>
                            <a href="{{ route('units.index') }}" class="sidebar-sub-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                                <i class="bi bi-rulers fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'وحدات القياس' : 'Measurement Units' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('categories.index') }}" class="sidebar-sub-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                                <i class="bi bi-tags fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'تصنيفات الأصناف' : 'Item Categories' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('warehouses.index') }}" class="sidebar-sub-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                                <i class="bi bi-house-gear fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'إدارة المخازن' : 'Warehouses' }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- 3. المخازن والأصناف -->
            @php
                $isInventoryActive = request()->routeIs('inventory.*') || request()->routeIs('warehouses.*') || request()->routeIs('warehouse-transfers.*');
            @endphp
            <li class="sidebar-nav-item" x-data="{ open: {{ $isInventoryActive ? 'true' : 'false' }} }">
                <button class="sidebar-group-btn {{ $isInventoryActive ? 'active' : '' }}" 
                        type="button" 
                        @click="open = !open" 
                        :aria-expanded="open ? 'true' : 'false'">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-box-seam-fill fs-5 text-primary"></i>
                        <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'المخازن والأصناف' : 'Inventory & Items' }}</span>
                    </div>
                    <i class="bi bi-chevron-down chevron-icon sidebar-text"></i>
                </button>
                <div class="sidebar-group-menu mt-1" x-show="open">
                    <ul class="list-unstyled ps-0 mb-0 d-flex flex-column gap-1">
                        <li>
                            <a href="{{ route('inventory.index') }}" class="sidebar-sub-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                                <i class="bi bi-boxes fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'دليل الأصناف' : 'Inventory Items' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('inventory.item-card') }}" class="sidebar-sub-link {{ request()->routeIs('inventory.item-card') ? 'active' : '' }}">
                                <i class="bi bi-card-checklist fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'جرد الأصناف' : 'Item Inventory' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('warehouses.index') }}" class="sidebar-sub-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                                <i class="bi bi-house-gear fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'إدارة المخازن' : 'Warehouses Management' }}</span>
                            </a>
                        </li>
                        @canany(['manage-inventory', 'view-warehouse-transfers'])
                            <li>
                                <a href="{{ route('warehouse-transfers.index') }}" class="sidebar-sub-link {{ request()->routeIs('warehouse-transfers.*') ? 'active' : '' }}">
                                    <i class="bi bi-arrow-left-right fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'التحويل بين المخازن' : 'Warehouse Transfers' }}</span>
                                </a>
                            </li>
                        @endcanany
                    </ul>
                </div>
            </li>

            <!-- 4. المبيعات -->
            @php
                $isSalesActive = request()->routeIs('invoices.*') || request()->routeIs('pos.*') || request()->routeIs('quotations.*') || request()->routeIs('customers.*');
            @endphp
            <li class="sidebar-nav-item" x-data="{ open: {{ $isSalesActive ? 'true' : 'false' }} }">
                <button class="sidebar-group-btn {{ $isSalesActive ? 'active' : '' }}" 
                        type="button" 
                        @click="open = !open" 
                        :aria-expanded="open ? 'true' : 'false'">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-bag-check-fill fs-5 text-primary"></i>
                        <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'المبيعات' : 'Sales' }}</span>
                    </div>
                    <i class="bi bi-chevron-down chevron-icon sidebar-text"></i>
                </button>
                <div class="sidebar-group-menu mt-1" x-show="open">
                    <ul class="list-unstyled ps-0 mb-0 d-flex flex-column gap-1">
                        <li>
                            <a href="{{ route('pos.index') }}" class="sidebar-sub-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                                <i class="bi bi-display fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'شاشة الكاشير' : 'Cashier Screen' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('invoices.create') }}" class="sidebar-sub-link {{ request()->routeIs('invoices.create') ? 'active' : '' }}">
                                <i class="bi bi-plus-circle fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'فاتورة مبيعات جديدة' : 'New Sales Invoice' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('invoices.index') }}" class="sidebar-sub-link {{ request()->routeIs('invoices.index') || request()->routeIs('invoices.show') ? 'active' : '' }}">
                                <i class="bi bi-receipt fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'قائمة فواتير المبيعات' : 'Sales Invoices' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('quotations.index') }}" class="sidebar-sub-link {{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-text fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'عروض الأسعار' : 'Quotations' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customers.index') }}" class="sidebar-sub-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                                <i class="bi bi-person-vcard fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'إدارة العملاء' : 'Customers' }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- 5. المشتريات -->
            @php
                $isPurchasesActive = request()->routeIs('purchases.*') || request()->routeIs('suppliers.*');
            @endphp
            <li class="sidebar-nav-item" x-data="{ open: {{ $isPurchasesActive ? 'true' : 'false' }} }">
                <button class="sidebar-group-btn {{ $isPurchasesActive ? 'active' : '' }}" 
                        type="button" 
                        @click="open = !open" 
                        :aria-expanded="open ? 'true' : 'false'">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-cart-plus-fill fs-5 text-primary"></i>
                        <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'المشتريات' : 'Purchases' }}</span>
                    </div>
                    <i class="bi bi-chevron-down chevron-icon sidebar-text"></i>
                </button>
                <div class="sidebar-group-menu mt-1" x-show="open">
                    <ul class="list-unstyled ps-0 mb-0 d-flex flex-column gap-1">
                        <li>
                            <a href="{{ route('purchases.create_invoice') }}" class="sidebar-sub-link {{ request()->routeIs('purchases.create_invoice') ? 'active' : '' }}">
                                <i class="bi bi-plus-circle fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'فاتورة شراء جديدة' : 'New Purchase Invoice' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('purchases.index') }}" class="sidebar-sub-link {{ request()->routeIs('purchases.index') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-zip fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'فواتير المشتريات' : 'Purchase Invoices' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('purchases.payables') }}" class="sidebar-sub-link {{ request()->routeIs('purchases.payables') ? 'active' : '' }}">
                                <i class="bi bi-hourglass-split fs-6 text-danger me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'مستحقات الموردين' : 'Supplier Payables' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('suppliers.index') }}" class="sidebar-sub-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                                <i class="bi bi-truck fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'إدارة الموردين' : 'Suppliers' }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- 6. المالية والحسابات -->
            @php
                $isAccountingActive = request()->routeIs('accounting.*') || request()->routeIs('cashboxes.*') || request()->routeIs('cheques.*') || request()->routeIs('payments.*');
            @endphp
            <li class="sidebar-nav-item" x-data="{ open: {{ $isAccountingActive ? 'true' : 'false' }} }">
                <button class="sidebar-group-btn {{ $isAccountingActive ? 'active' : '' }}" 
                        type="button" 
                        @click="open = !open" 
                        :aria-expanded="open ? 'true' : 'false'">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-bank2 fs-5 text-primary"></i>
                        <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'المالية والحسابات' : 'Finance & Accounting' }}</span>
                    </div>
                    <i class="bi bi-chevron-down chevron-icon sidebar-text"></i>
                </button>
                <div class="sidebar-group-menu mt-1" x-show="open">
                    <ul class="list-unstyled ps-0 mb-0 d-flex flex-column gap-1">
                        <li>
                            <a href="{{ route('accounting.index') }}" class="sidebar-sub-link {{ request()->routeIs('accounting.*') ? 'active' : '' }}">
                                <i class="bi bi-diagram-3-fill fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'شجرة الحسابات المحاسبية' : 'Chart of Accounts' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cashboxes.index') }}" class="sidebar-sub-link {{ request()->routeIs('cashboxes.*') ? 'active' : '' }}">
                                <i class="bi bi-safe2 fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'الخزن والسيولة النقدية' : 'Cashboxes & Liquidity' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('payments.index') }}" class="sidebar-sub-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                                <i class="bi bi-receipt-cutoff fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'السندات المالية والمقبوضات' : 'Payment Vouchers' }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cheques.index') }}" class="sidebar-sub-link {{ request()->routeIs('cheques.*') ? 'active' : '' }}">
                                <i class="bi bi-card-checklist fs-6 text-primary me-1"></i>
                                <span>{{ app()->getLocale() == 'ar' ? 'إدارة الشيكات' : 'Cheques' }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- 7. المستخدمون -->
            @canany(['view-users', 'manage-roles'])
                @php
                    $isUsersActive = request()->routeIs('users.*') || request()->routeIs('roles.*');
                @endphp
                <li class="sidebar-nav-item" x-data="{ open: {{ $isUsersActive ? 'true' : 'false' }} }">
                    <button class="sidebar-group-btn {{ $isUsersActive ? 'active' : '' }}" 
                            type="button" 
                            @click="open = !open" 
                            :aria-expanded="open ? 'true' : 'false'">
                        <div class="d-flex align-items-center gap-2.5">
                            <i class="bi bi-people-fill fs-5 text-primary"></i>
                            <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'المستخدمون' : 'Users & Roles' }}</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon sidebar-text"></i>
                    </button>
                    <div class="sidebar-group-menu mt-1" x-show="open">
                        <ul class="list-unstyled ps-0 mb-0 d-flex flex-column gap-1">
                            <li>
                                <a href="{{ route('users.index') }}" class="sidebar-sub-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="bi bi-person-lines-fill fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'المستخدمين' : 'Users' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('roles.index') }}" class="sidebar-sub-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                    <i class="bi bi-shield-lock-fill fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'الأدوار والصلاحيات' : 'Roles & Permissions' }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcanany

            <!-- 8. التقارير العامة -->
            @canany(['reports.access', 'reports.sales.view', 'reports.customers.view', 'reports.suppliers.view', 'reports.inventory.view', 'reports.profitability.view', 'view-activity-logs'])
                @php
                    $isGeneralReportsActive = request()->routeIs('reports.index') || request()->routeIs('reports.sales') || request()->routeIs('reports.warehouse-inventory') || request()->routeIs('reports.inventory') || request()->routeIs('reports.financial-comparison') || request()->routeIs('reports.profitable-items') || request()->routeIs('reports.customer-statement') || request()->routeIs('reports.supplier-statement') || request()->routeIs('activity-logs.*');
                @endphp
                <li class="sidebar-nav-item" x-data="{ open: {{ $isGeneralReportsActive ? 'true' : 'false' }} }">
                    <button class="sidebar-group-btn {{ $isGeneralReportsActive ? 'active' : '' }}" 
                            type="button" 
                            @click="open = !open" 
                            :aria-expanded="open ? 'true' : 'false'">
                        <div class="d-flex align-items-center gap-2.5">
                            <i class="bi bi-bar-chart-line-fill fs-5 text-primary"></i>
                            <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'التقارير' : 'General Reports' }}</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon sidebar-text"></i>
                    </button>
                    <div class="sidebar-group-menu mt-1" x-show="open">
                        <ul class="list-unstyled ps-0 mb-0 d-flex flex-column gap-1">
                            <li>
                                <a href="{{ route('reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                                    <i class="bi bi-grid-fill fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'مركز التقارير' : 'Overview' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.sales') }}" class="sidebar-sub-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                                    <i class="bi bi-receipt fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'تقارير المبيعات' : 'Sales Reports' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.warehouse-inventory') }}" class="sidebar-sub-link {{ request()->routeIs('reports.warehouse-inventory') ? 'active' : '' }}">
                                    <i class="bi bi-clipboard-check fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'تقرير جرد المخزن' : 'Warehouse Inventory' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.inventory') }}" class="sidebar-sub-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
                                    <i class="bi bi-boxes fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'أرصدة المخزون العام' : 'General Inventory' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventory.item-card') }}" class="sidebar-sub-link {{ request()->routeIs('inventory.item-card') ? 'active' : '' }}">
                                    <i class="bi bi-card-checklist fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'جرد الأصناف' : 'Item Inventory' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.financial-comparison') }}" class="sidebar-sub-link {{ request()->routeIs('reports.financial-comparison') ? 'active' : '' }}">
                                    <i class="bi bi-arrows-collapse fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'مقارنة الفترات المالية' : 'Period Comparison' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.profitable-items') }}" class="sidebar-sub-link {{ request()->routeIs('reports.profitable-items') ? 'active' : '' }}">
                                    <i class="bi bi-graph-up-arrow fs-6 text-emerald-400 me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'الأصناف الأكثر ربحية' : 'Most Profitable Items' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.customer-statement') }}" class="sidebar-sub-link {{ request()->routeIs('reports.customer-statement') ? 'active' : '' }}">
                                    <i class="bi bi-person-lines-fill fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'كشف حساب عميل' : 'Customer Statement' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('reports.supplier-statement') }}" class="sidebar-sub-link {{ request()->routeIs('reports.supplier-statement') ? 'active' : '' }}">
                                    <i class="bi bi-truck fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'كشف حساب مورد' : 'Supplier Statement' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('activity-logs.index') }}" class="sidebar-sub-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                                    <i class="bi bi-shield-check fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'سجل تتبع الحركات والرقابة' : 'Audit Trail & Logs' }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcanany

            <!-- 8.5. التقارير المالية -->
            @canany(['reports.financial.view', 'reports.financial_statements.view', 'reports.balance_sheet.view', 'reports.income_statement.view', 'reports.trial_balance.view', 'reports.cash_flow.view', 'reports.equity_changes.view', 'reports.general_ledger.view', 'reports.account_balances.view'])
                @php
                    $isFinancialReportsActive = request()->routeIs('reports.balance-sheet') || request()->routeIs('reports.income-statement') || request()->routeIs('reports.trial-balance') || request()->routeIs('reports.cash-flow') || request()->routeIs('reports.equity-changes') || request()->routeIs('reports.general-ledger') || request()->routeIs('reports.account-balances');
                @endphp
                <li class="sidebar-nav-item" x-data="{ open: {{ $isFinancialReportsActive ? 'true' : 'false' }} }">
                    <button class="sidebar-group-btn {{ $isFinancialReportsActive ? 'active' : '' }}" 
                            type="button" 
                            @click="open = !open" 
                            :aria-expanded="open ? 'true' : 'false'">
                        <div class="d-flex align-items-center gap-2.5">
                            <i class="bi bi-file-earmark-spreadsheet-fill fs-5 text-emerald-400"></i>
                            <span class="sidebar-text font-bold text-white">{{ app()->getLocale() == 'ar' ? 'التقارير المالية' : 'Financial Statements' }}</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon sidebar-text"></i>
                    </button>
                    <div class="sidebar-group-menu mt-1" x-show="open">
                        <ul class="list-unstyled ps-0 mb-0 d-flex flex-column gap-1">
                            @canany(['reports.balance_sheet.view', 'reports.financial_statements.view', 'reports.financial.view'])
                                <li>
                                    <a href="{{ route('reports.balance-sheet') }}" class="sidebar-sub-link {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}">
                                        <i class="bi bi-bank fs-6 text-primary me-1"></i>
                                        <span>{{ app()->getLocale() == 'ar' ? 'قائمة المركز المالي (الميزانية)' : 'Balance Sheet' }}</span>
                                    </a>
                                </li>
                            @endcanany

                            @canany(['reports.income_statement.view', 'reports.financial_statements.view', 'reports.financial.view'])
                                <li>
                                    <a href="{{ route('reports.income-statement') }}" class="sidebar-sub-link {{ request()->routeIs('reports.income-statement') ? 'active' : '' }}">
                                        <i class="bi bi-graph-up-arrow fs-6 text-success me-1"></i>
                                        <span>{{ app()->getLocale() == 'ar' ? 'قائمة الدخل (الأرباح والخسائر)' : 'Income Statement' }}</span>
                                    </a>
                                </li>
                            @endcanany

                            @canany(['reports.trial_balance.view', 'reports.financial_statements.view', 'reports.financial.view'])
                                <li>
                                    <a href="{{ route('reports.trial-balance') }}" class="sidebar-sub-link {{ request()->routeIs('reports.trial-balance') ? 'active' : '' }}">
                                        <i class="bi bi-card-checklist fs-6 text-info me-1"></i>
                                        <span>{{ app()->getLocale() == 'ar' ? 'ميزان المراجعة' : 'Trial Balance' }}</span>
                                    </a>
                                </li>
                            @endcanany

                            @canany(['reports.cash_flow.view', 'reports.financial_statements.view', 'reports.financial.view'])
                                <li>
                                    <a href="{{ route('reports.cash-flow') }}" class="sidebar-sub-link {{ request()->routeIs('reports.cash-flow') ? 'active' : '' }}">
                                        <i class="bi bi-water fs-6 text-warning me-1"></i>
                                        <span>{{ app()->getLocale() == 'ar' ? 'قائمة التدفقات النقدية' : 'Cash Flow Statement' }}</span>
                                    </a>
                                </li>
                            @endcanany

                            @canany(['reports.equity_changes.view', 'reports.financial_statements.view', 'reports.financial.view'])
                                <li>
                                    <a href="{{ route('reports.equity-changes') }}" class="sidebar-sub-link {{ request()->routeIs('reports.equity-changes') ? 'active' : '' }}">
                                        <i class="bi bi-pie-chart fs-6 text-secondary me-1"></i>
                                        <span>{{ app()->getLocale() == 'ar' ? 'التغيرات في حقوق الملكية' : 'Changes in Equity' }}</span>
                                    </a>
                                </li>
                            @endcanany

                            @canany(['reports.general_ledger.view', 'reports.financial_statements.view', 'reports.financial.view'])
                                <li>
                                    <a href="{{ route('reports.general-ledger') }}" class="sidebar-sub-link {{ request()->routeIs('reports.general-ledger') ? 'active' : '' }}">
                                        <i class="bi bi-journal-bookmark fs-6 text-dark me-1"></i>
                                        <span>{{ app()->getLocale() == 'ar' ? 'تقرير دفتر الأستاذ العام' : 'General Ledger' }}</span>
                                    </a>
                                </li>
                            @endcanany

                            @canany(['reports.account_balances.view', 'reports.financial_statements.view', 'reports.financial.view'])
                                <li>
                                    <a href="{{ route('reports.account-balances') }}" class="sidebar-sub-link {{ request()->routeIs('reports.account-balances') ? 'active' : '' }}">
                                        <i class="bi bi-diagram-3 fs-6 text-emerald-400 me-1"></i>
                                        <span>{{ app()->getLocale() == 'ar' ? 'كشف أرصدة شجرة الحسابات' : 'Account Balances' }}</span>
                                    </a>
                                </li>
                            @endcanany
                        </ul>
                    </div>
                </li>
            @endcanany

            <!-- 9. الإعدادات -->
            @canany(['manage-settings', 'view-branches'])
                @php
                    $isSettingsActive = request()->routeIs('settings.*') || request()->routeIs('branches.*');
                @endphp
                <li class="sidebar-nav-item" x-data="{ open: {{ $isSettingsActive ? 'true' : 'false' }} }">
                    <button class="sidebar-group-btn {{ $isSettingsActive ? 'active' : '' }}" 
                            type="button" 
                            @click="open = !open" 
                            :aria-expanded="open ? 'true' : 'false'">
                        <div class="d-flex align-items-center gap-2.5">
                            <i class="bi bi-gear-fill fs-5 text-primary"></i>
                            <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'الإعدادات' : 'Settings' }}</span>
                        </div>
                        <i class="bi bi-chevron-down chevron-icon sidebar-text"></i>
                    </button>
                    <div class="sidebar-group-menu mt-1" x-show="open">
                        <ul class="list-unstyled ps-0 mb-0 d-flex flex-column gap-1">
                            <li>
                                <a href="{{ route('settings.index') }}" class="sidebar-sub-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                    <i class="bi bi-sliders fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'الإعدادات العامة' : 'General Settings' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('branches.index') }}" class="sidebar-sub-link {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                                    <i class="bi bi-diagram-2 fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'فروع المؤسسة' : 'Branches' }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('currencies.index') }}" class="sidebar-sub-link {{ request()->routeIs('currencies.*') ? 'active' : '' }}">
                                    <i class="bi bi-currency-exchange fs-6 text-primary me-1"></i>
                                    <span>{{ app()->getLocale() == 'ar' ? 'العملات وأسعار الصرف' : 'Currencies & Rates' }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcanany

            <!-- 10. دليل النظام -->
            <li class="sidebar-nav-item">
                <a href="{{ route('system-guide') }}" 
                   class="sidebar-nav-link {{ request()->routeIs('system-guide') ? 'active' : '' }}"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}" 
                   title="{{ app()->getLocale() == 'ar' ? 'دليل النظام' : 'System Guide' }}">
                    <i class="bi bi-compass-fill fs-5 text-emerald-400"></i>
                    <span class="sidebar-text font-bold text-white">{{ app()->getLocale() == 'ar' ? 'دليل النظام' : 'System Guide' }}</span>
                    <span class="badge bg-emerald-500-20 text-emerald-400 border border-emerald-500-30 ms-auto fs-8 rounded-pill px-2 sidebar-text">
                        {{ app()->getLocale() == 'ar' ? 'شرح' : 'Guide' }}
                    </span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Sidebar Footer with User Dropdown at End of List -->
    <div class="sidebar-footer border-top border-slate-800 pt-3 pb-4 mt-3 px-1">
        @auth
            <div class="dropup">
                <button class="btn btn-dark border-0 w-100 p-2 rounded-3 d-flex align-items-center justify-content-between gap-2 hover-bg-slate-800 transition-all text-start" 
                        type="button" id="sidebarUserDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255,255,255,0.06);">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle object-fit-cover flex-shrink-0" style="width: 34px; height: 34px;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-bold fs-7 flex-shrink-0" style="width: 34px; height: 34px;">
                                {{ mb_substr(Auth::user()->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="overflow-hidden sidebar-text">
                            <div class="font-bold text-white fs-7 text-truncate">{{ Auth::user()->name }}</div>
                            <div class="text-slate-400 fs-8 text-truncate">
                                {{ Auth::user()->roles->first()?->name ?? (app()->getLocale() == 'ar' ? 'مدير النظام' : 'Administrator') }}
                            </div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-up fs-8 text-slate-400 sidebar-text me-1"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow-lg border border-slate-700 rounded-3 p-2 w-100 mb-2" aria-labelledby="sidebarUserDropdown" style="min-width: 210px; z-index: 1050;">
                    <li class="px-3 py-2 bg-slate-800 rounded-2 mb-2">
                        <div class="font-bold text-white fs-7">{{ Auth::user()->name }}</div>
                        <div class="text-slate-400 fs-8 text-truncate">{{ Auth::user()->email }}</div>
                        <div class="mt-1">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-medium fs-8">
                                {{ Auth::user()->roles->first()?->name ?? (app()->getLocale() == 'ar' ? 'مدير النظام' : 'Admin') }}
                            </span>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 rounded-2 py-2 text-slate-200" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-gear text-primary fs-6"></i> 
                            <span>{{ __('general.profile') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 rounded-2 py-2 text-slate-200" href="{{ route('settings.index') }}">
                            <i class="bi bi-sliders text-amber-400 fs-6"></i> 
                            <span>{{ __('settings.title') }}</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1 border-slate-700"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 rounded-2 py-2 text-danger w-100 text-start">
                                <i class="bi bi-box-arrow-right fs-6"></i> 
                                <span>{{ __('general.logout') }}</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @endauth

        @guest
            <div class="px-1 text-center">
                <a href="{{ route('login') }}" class="btn btn-primary w-100 py-2 rounded-3 font-bold fs-7 d-flex align-items-center justify-content-center gap-2 shadow-sm">
                    <i class="bi bi-box-arrow-in-right fs-5"></i>
                    <span class="sidebar-text">{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول' : 'Sign In' }}</span>
                </a>
            </div>
        @endguest
    </div>
</div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('sidebarMenuSearchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        const navItems = document.querySelectorAll('#sidebarAccordion .sidebar-nav-item');

        navItems.forEach(item => {
            if (!query) {
                item.style.display = '';
                const subLinks = item.querySelectorAll('.sidebar-sub-link');
                subLinks.forEach(sub => sub.style.display = '');
                return;
            }

            const itemText = item.textContent.toLowerCase();
            if (itemText.includes(query)) {
                item.style.display = '';
                const menu = item.querySelector('.sidebar-group-menu');
                if (menu) {
                    menu.style.display = 'block';
                }
                const subLinks = item.querySelectorAll('.sidebar-sub-link');
                subLinks.forEach(sub => {
                    const subText = sub.textContent.toLowerCase();
                    if (subText.includes(query)) {
                        sub.style.display = '';
                    } else {
                        sub.style.display = 'none';
                    }
                });
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
