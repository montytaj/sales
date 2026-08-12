<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public ?string $title;

    /**
     * Create a new component instance.
     */
    public function __construct(?string $title = null)
    {
        $this->title = $title ?: $this->resolvePageTitle();
    }

    /**
     * Resolve default page title based on current route if title is not explicitly provided.
     */
    protected function resolvePageTitle(): string
    {
        $route = request()->route();
        if (!$route) {
            return app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard';
        }

        $routeName = $route->getName();
        $isAr = app()->getLocale() === 'ar';

        $titles = [
            // Dashboard & Guide
            'dashboard' => $isAr ? 'لوحة التحكم' : 'Dashboard',
            'system-guide' => $isAr ? 'دليل النظام والخصائص' : 'System Guide',

            // Lookup / Master Data (البيانات الأساسية)
            'units.index' => $isAr ? 'وحدات القياس' : 'Measurement Units',
            'units.create' => $isAr ? 'إضافة وحدة قياس' : 'Add Measurement Unit',
            'units.edit' => $isAr ? 'تعديل وحدة قياس' : 'Edit Measurement Unit',
            'units.show' => $isAr ? 'تفاصيل وحدة القياس' : 'Measurement Unit Details',

            'categories.index' => $isAr ? 'تصنيفات الأصناف' : 'Item Categories',
            'categories.create' => $isAr ? 'إضافة تصنيف أصناف' : 'Add Category',
            'categories.edit' => $isAr ? 'تعديل تصنيف' : 'Edit Category',
            'categories.show' => $isAr ? 'تفاصيل التصنيف' : 'Category Details',

            'warehouses.index' => $isAr ? 'إدارة المخازن' : 'Warehouses',
            'warehouses.create' => $isAr ? 'إضافة مخزن جديد' : 'Add Warehouse',
            'warehouses.edit' => $isAr ? 'تعديل مخزن' : 'Edit Warehouse',
            'warehouses.show' => $isAr ? 'تفاصيل المخزن' : 'Warehouse Details',
            'warehouses.opening-balances' => $isAr ? 'الأرصدة الافتتاحية للمخزن' : 'Warehouse Opening Balances',

            // Inventory (المخازن والأصناف)
            'inventory.index' => $isAr ? 'دليل الأصناف والمخزون' : 'Inventory & Items',
            'inventory.create' => $isAr ? 'إضافة صنف جديد' : 'Add Inventory Item',
            'inventory.edit' => $isAr ? 'تعديل بيانات صنف' : 'Edit Inventory Item',
            'inventory.show' => $isAr ? 'تفاصيل الصنف' : 'Item Details',
            'inventory.item-card' => $isAr ? 'كارت حركة صنف' : 'Item Movement Card',

            'warehouse-transfers.index' => $isAr ? 'التحويلات بين المخازن' : 'Warehouse Transfers',
            'warehouse-transfers.create' => $isAr ? 'أمر تحويل مخزني جديد' : 'New Warehouse Transfer',
            'warehouse-transfers.edit' => $isAr ? 'تعديل أمر التحويل' : 'Edit Warehouse Transfer',
            'warehouse-transfers.show' => $isAr ? 'تفاصيل التحويل المخزني' : 'Warehouse Transfer Details',

            // Sales (المبيعات)
            'pos.index' => $isAr ? 'شاشة الكاشير (POS)' : 'Cashier Screen (POS)',
            'invoices.create' => $isAr ? 'فاتورة مبيعات جديدة' : 'New Sales Invoice',
            'invoices.index' => $isAr ? 'فواتير المبيعات' : 'Sales Invoices',
            'invoices.show' => $isAr ? 'تفاصيل فاتورة المبيعات' : 'Sales Invoice Details',

            'quotations.index' => $isAr ? 'عروض الأسعار' : 'Quotations',
            'quotations.create' => $isAr ? 'عرض سعر جديد' : 'New Quotation',
            'quotations.edit' => $isAr ? 'تعديل عرض السعر' : 'Edit Quotation',
            'quotations.show' => $isAr ? 'تفاصيل عرض السعر' : 'Quotation Details',

            'customers.index' => $isAr ? 'إدارة العملاء' : 'Customers',
            'customers.create' => $isAr ? 'إضافة عميل جديد' : 'Add Customer',
            'customers.edit' => $isAr ? 'تعديل بيانات عميل' : 'Edit Customer',
            'customers.show' => $isAr ? 'تفاصيل العميل' : 'Customer Details',

            // Services & Orders & Surveys (الخدمات واللوحات والمعاينات)
            'services.index' => $isAr ? 'دليل الخدمات والتركيبات' : 'Services Catalog',
            'services.create' => $isAr ? 'إضافة خدمة جديدة' : 'Add New Service',
            'services.edit' => $isAr ? 'تعديل خدمة' : 'Edit Service',
            'services.show' => $isAr ? 'تفاصيل الخدمة' : 'Service Details',

            'signage-orders.index' => $isAr ? 'أوامر اللوحات والكلادنج' : 'Signage Orders',
            'signage-orders.create' => $isAr ? 'أمر تصنيع جديد' : 'New Signage Order',
            'signage-orders.edit' => $isAr ? 'تعديل أمر التصنيع' : 'Edit Signage Order',
            'signage-orders.show' => $isAr ? 'تفاصيل أمر التصنيع' : 'Signage Order Details',

            'site-surveys.index' => $isAr ? 'المعاينات والرفع المساحي' : 'Site Surveys',
            'site-surveys.create' => $isAr ? 'طلب معاينة جديد' : 'New Site Survey',
            'site-surveys.edit' => $isAr ? 'تعديل المعاينة' : 'Edit Site Survey',
            'site-surveys.show' => $isAr ? 'تفاصيل المعاينة' : 'Site Survey Details',

            // Contracts, Projects & Work Orders (العقود والمشاريع والورشة)
            'contracts.index' => $isAr ? 'إدارة العقود والاتفاقيات' : 'Contracts & Agreements',
            'contracts.create' => $isAr ? 'إنشاء عقد جديد' : 'New Contract',
            'contracts.edit' => $isAr ? 'تعديل العقد' : 'Edit Contract',
            'contracts.show' => $isAr ? 'تفاصيل العقد' : 'Contract Details',

            'projects.index' => $isAr ? 'إدارة المشاريع' : 'Projects Management',
            'projects.create' => $isAr ? 'إضافة مشروع جديد' : 'Add New Project',
            'projects.edit' => $isAr ? 'تعديل بيانات مشروع' : 'Edit Project',
            'projects.show' => $isAr ? 'تفاصيل المشروع' : 'Project Details',

            'work-orders.index' => $isAr ? 'أوامر التشغيل والورشة' : 'Work Orders & Workshop',
            'work-orders.create' => $isAr ? 'أمر تشغيل جديد' : 'New Work Order',
            'work-orders.edit' => $isAr ? 'تعديل أمر التشغيل' : 'Edit Work Order',
            'work-orders.show' => $isAr ? 'تفاصيل أمر التشغيل' : 'Work Order Details',

            'workshop-kiosk.index' => $isAr ? 'شاشة الفنيين والورشة (Kiosk)' : 'Workshop Kiosk',

            // Purchases (المشتريات)
            'purchases.index' => $isAr ? 'قائمة فواتير المشتريات' : 'Purchase Invoices',
            'purchases.payables' => $isAr ? 'مستحقات الموردين' : 'Supplier Payables',
            'purchases.create_invoice' => $isAr ? 'فاتورة شراء جديدة' : 'New Purchase Invoice',
            'purchases.create_po' => $isAr ? 'أمر شراء جديد' : 'New Purchase Order',
            'purchases.show_invoice' => $isAr ? 'تفاصيل فاتورة الشراء' : 'Purchase Invoice Details',
            'purchases.pay_invoice' => $isAr ? 'سداد فاتورة شراء' : 'Pay Purchase Invoice',

            'suppliers.index' => $isAr ? 'إدارة الموردين' : 'Suppliers',
            'suppliers.create' => $isAr ? 'إضافة مورد جديد' : 'Add Supplier',
            'suppliers.edit' => $isAr ? 'تعديل مورد' : 'Edit Supplier',
            'suppliers.show' => $isAr ? 'تفاصيل المورد' : 'Supplier Details',

            // Finance & Accounting (المالية والحسابات)
            'accounting.index' => $isAr ? 'شجرة الحسابات المحاسبية' : 'Chart of Accounts',
            'accounting.create' => $isAr ? 'إضافة حساب جديد' : 'Add Account',
            'accounting.edit' => $isAr ? 'تعديل الحساب' : 'Edit Account',
            'accounting.show' => $isAr ? 'تفاصيل الحساب المحاسبي' : 'Account Details',

            'cashboxes.index' => $isAr ? 'الخزن والسيولة النقدية' : 'Cashboxes & Liquidity',
            'cashboxes.create' => $isAr ? 'إضافة خزنة جديدة' : 'Add Cashbox',
            'cashboxes.show' => $isAr ? 'تفاصيل الخزنة' : 'Cashbox Details',
            'cashboxes.edit' => $isAr ? 'تعديل خزنة' : 'Edit Cashbox',

            'payments.index' => $isAr ? 'السندات المالية والمقبوضات' : 'Payment Vouchers',
            'payments.create' => $isAr ? 'إنشاء سند مالي' : 'Create Payment Voucher',
            'payments.show' => $isAr ? 'تفاصيل السند المالي' : 'Payment Voucher Details',
            'payments.edit' => $isAr ? 'تعديل السند المالي' : 'Edit Payment Voucher',

            'cheques.index' => $isAr ? 'إدارة الشيكات' : 'Cheques',
            'cheques.show' => $isAr ? 'تفاصيل الشيك' : 'Cheque Details',
            'cheques.deposit-slip' => $isAr ? 'حافظة إيداع الشيكات' : 'Cheque Deposit Slip',

            // Users & Roles (المستخدمون والصلاحيات)
            'users.index' => $isAr ? 'المستخدمون' : 'Users',
            'users.create' => $isAr ? 'إضافة مستخدم جديد' : 'Add User',
            'users.edit' => $isAr ? 'تعديل مستخدم' : 'Edit User',
            'users.show' => $isAr ? 'تفاصيل المستخدم' : 'User Details',

            'roles.index' => $isAr ? 'الأدوار والصلاحيات' : 'Roles & Permissions',
            'roles.create' => $isAr ? 'إضافة دور صلاحيات' : 'Add Role',
            'roles.edit' => $isAr ? 'تعديل دور صلاحيات' : 'Edit Role',
            'roles.matrix' => $isAr ? 'مصفوفة الصلاحيات' : 'Permissions Matrix',
            'permissions.index' => $isAr ? 'صلاحيات النظام' : 'Permissions List',

            // Reports & Logs (التقارير وسجل الحركات)
            'reports.index' => $isAr ? 'مركز التقارير' : 'Overview Reports',
            'reports.sales' => $isAr ? 'تقارير المبيعات والضرائب' : 'Sales Reports',
            'reports.workshop' => $isAr ? 'تقارير تشغيل الورشة وCNC' : 'Workshop Reports',
            'reports.projects' => $isAr ? 'تقارير إنجاز ومصروفات المشاريع' : 'Projects Report',
            'reports.inventory' => $isAr ? 'تقارير المخزون والمشتريات' : 'Inventory Reports',
            'reports.warehouse-inventory' => $isAr ? 'تقرير جرد المخزن' : 'Warehouse Inventory Report',
            'reports.customer-statement' => $isAr ? 'كشف حساب عميل' : 'Customer Statement',
            'reports.supplier-statement' => $isAr ? 'كشف حساب مورد' : 'Supplier Statement',
            'reports.financial' => $isAr ? 'التقارير المالية وحركة النقدية' : 'Financial Reports',
            'reports.financial-comparison' => $isAr ? 'مقارنة الفترات المالية' : 'Financial Period Comparison',
            'reports.balance-sheet' => $isAr ? 'قائمة المركز المالي' : 'Balance Sheet',
            'reports.income-statement' => $isAr ? 'قائمة الدخل' : 'Income Statement',
            'reports.trial-balance' => $isAr ? 'تقرير ميزان المراجعة' : 'Trial Balance',
            'reports.cash-flow' => $isAr ? 'قائمة التدفقات النقدية' : 'Statement of Cash Flows',
            'reports.equity-changes' => $isAr ? 'قائمة التغيرات في حقوق الملكية' : 'Changes in Equity',
            'reports.general-ledger' => $isAr ? 'تقرير دفتر الأستاذ العام' : 'General Ledger Report',
            'reports.account-balances' => $isAr ? 'أرصدة شجرة الحسابات' : 'Account Balances Report',
            'reports.profitable-items' => $isAr ? 'تقرير الأصناف الأكثر ربحية' : 'Most Profitable Items',
            'activity-logs.index' => $isAr ? 'سجل تتبع الحركات والرقابة' : 'Audit Trail & Logs',

            // Settings & Branches & Currencies (الإعدادات والفروع والعملات)
            'settings.index' => $isAr ? 'الإعدادات العامة' : 'General Settings',
            'branches.index' => $isAr ? 'فروع المؤسسة' : 'Branches',
            'branches.create' => $isAr ? 'إضافة فرع جديد' : 'Add Branch',
            'branches.edit' => $isAr ? 'تعديل فرع' : 'Edit Branch',
            'branches.show' => $isAr ? 'تفاصيل الفرع' : 'Branch Details',
            'currencies.index' => $isAr ? 'العملات وأسعار الصرف' : 'Currencies & Rates',

            // Profile & Notifications (الملف الشخصي والإشعارات)
            'profile.edit' => $isAr ? 'الملف الشخصي' : 'Profile',
            'notifications.index' => $isAr ? 'مركز الإشعارات' : 'Notifications Hub',
        ];

        if (isset($titles[$routeName])) {
            return $titles[$routeName];
        }

        // Dynamic fallback for any unmapped route names
        if ($routeName) {
            $cleanName = str_replace(['.index', '.create', '.edit', '.show'], '', $routeName);
            $cleanName = str_replace(['-', '_', '.'], ' ', $cleanName);
            return ucwords($cleanName);
        }

        return $isAr ? 'لوحة التحكم' : 'Dashboard';
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
