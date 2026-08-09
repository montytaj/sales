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
            'dashboard' => $isAr ? 'لوحة التحكم الرئيسية' : 'Dashboard',
            'system-guide' => $isAr ? 'دليل المنظومة والخصائص' : 'System Guide',

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

            // Inventory (المخازن والأصناف)
            'inventory.index' => $isAr ? 'المخازن والأصناف' : 'Inventory & Items',
            'inventory.create' => $isAr ? 'إضافة صنف جديد' : 'Add Inventory Item',
            'inventory.edit' => $isAr ? 'تعديل بيانات صنف' : 'Edit Inventory Item',
            'inventory.show' => $isAr ? 'تفاصيل الصنف' : 'Item Details',

            // Sales (المبيعات)
            'pos.index' => $isAr ? 'شاشة الكاشير (POS)' : 'Cashier Screen (POS)',
            'invoices.create' => $isAr ? 'فاتورة مبيعات جديدة' : 'New Sales Invoice',
            'invoices.index' => $isAr ? 'قائمة فواتير المبيعات' : 'Sales Invoices',
            'invoices.show' => $isAr ? 'تفاصيل فاتورة المبيعات' : 'Sales Invoice Details',

            'quotations.index' => $isAr ? 'عروض الأسعار' : 'Quotations',
            'quotations.create' => $isAr ? 'عرض سعر جديد' : 'New Quotation',
            'quotations.edit' => $isAr ? 'تعديل عرض السعر' : 'Edit Quotation',
            'quotations.show' => $isAr ? 'تفاصيل عرض السعر' : 'Quotation Details',

            'customers.index' => $isAr ? 'إدارة العملاء' : 'Customers',
            'customers.create' => $isAr ? 'إضافة عميل جديد' : 'Add Customer',
            'customers.edit' => $isAr ? 'تعديل بيانات عميل' : 'Edit Customer',
            'customers.show' => $isAr ? 'تفاصيل العميل' : 'Customer Details',

            // Purchases (المشتريات)
            'purchases.create_invoice' => $isAr ? 'فاتورة شراء جديدة' : 'New Purchase Invoice',
            'purchases.create_po' => $isAr ? 'أمر شراء جديد' : 'New Purchase Order',
            'purchases.index' => $isAr ? 'قائمة فواتير المشتريات' : 'Purchase Invoices',
            'purchases.show_invoice' => $isAr ? 'تفاصيل فاتورة الشراء' : 'Purchase Invoice Details',

            'suppliers.index' => $isAr ? 'إدارة الموردين' : 'Suppliers',
            'suppliers.create' => $isAr ? 'إضافة مورد جديد' : 'Add Supplier',
            'suppliers.edit' => $isAr ? 'تعديل مورد' : 'Edit Supplier',
            'suppliers.show' => $isAr ? 'تفاصيل المورد' : 'Supplier Details',

            // Finance & Accounting (المالية والحسابات)
            'accounting.index' => $isAr ? 'شجرة الحسابات المحاسبية' : 'Chart of Accounts',
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
            'reports.sales' => $isAr ? 'تقارير المبيعات' : 'Sales Reports',
            'reports.inventory' => $isAr ? 'تقارير المخزون' : 'Inventory Reports',
            'reports.customer-statement' => $isAr ? 'كشف حساب عميل' : 'Customer Statement',
            'reports.supplier-statement' => $isAr ? 'كشف حساب مورد' : 'Supplier Statement',
            'reports.financial' => $isAr ? 'التقارير المالية' : 'Financial Reports',
            'activity-logs.index' => $isAr ? 'سجل تتبع الحركات والرقابة' : 'Audit Trail & Logs',

            // Settings & Branches (الإعدادات والفروع)
            'settings.index' => $isAr ? 'الإعدادات العامة' : 'General Settings',
            'branches.index' => $isAr ? 'فروع المؤسسة' : 'Branches',
            'branches.create' => $isAr ? 'إضافة فرع جديد' : 'Add Branch',
            'branches.edit' => $isAr ? 'تعديل فرع' : 'Edit Branch',
            'branches.show' => $isAr ? 'تفاصيل الفرع' : 'Branch Details',
            'currencies.index' => $isAr ? 'العملات وأسعار الصرف' : 'Currencies & Rates',

            // Profile & Notifications (الملف الشخصي والإشعارات)
            'profile.edit' => $isAr ? 'الملف الشخصي' : 'Profile',
            'notifications.index' => $isAr ? 'الإشعارات' : 'Notifications',
        ];

        return $titles[$routeName] ?? ($isAr ? 'لوحة التحكم' : 'Dashboard');
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
