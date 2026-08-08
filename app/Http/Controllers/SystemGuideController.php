<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;

class SystemGuideController extends Controller
{
    /**
     * Display the System Guide & Feature Showcase page.
     * Accessible publicly for guests and authenticated users.
     */
    public function index()
    {
        $facilityName = setting('facility_name', app()->getLocale() == 'ar' ? 'إدارة المبيعات والمشتريات والمخازن' : 'Sales & Inventory ERP');
        $primaryColor = setting('primary_color', '#2563eb');
        
        // System Modules Data
        $modules = [
            [
                'id' => 'pos-sales',
                'category' => 'sales',
                'icon' => 'bi-display',
                'badge_color' => 'primary',
                'title_ar' => 'شاشة المبيعات والكاشير POS وفواتير البيع',
                'title_en' => 'POS Cashier Screen & Sales Invoices',
                'summary_ar' => 'منظومة مبيعات سريعة ومتكاملة تشمل شاشة كاشير تفاعلية، تحويل عروض الأسعار، وإصدار الفواتير الفورية.',
                'summary_en' => 'Complete fast-sales system including interactive cashier screen, quotation conversion, and instant invoicing.',
                'features_ar' => [
                    'شاشة كاشير (POS) فائقة السرعة تدعم البحث بالباركود والاسم.',
                    'دعم كامل لاختصارات لوحة المفاتيح لإعادة تنفيذ العمليات بسرعة عالية.',
                    'إمكانية تحويل عروض الأسعار إلى فواتير مبيعات نهائية بضغطة زر واحدة.',
                    'تخصيص الخصومات، الضرائب، ورسوم التوصيل والخدمة بكل مرونة.',
                    'طباعة فواتير المبيعات بتنسيق حراري (Thermal 80mm) أو A4 يدعم هوية المنشأة.',
                    'متابعة حالة الفواتير (مدفوعة، جزئية، غير مدفوعة، ملغاة).'
                ],
                'features_en' => [
                    'Ultra-fast POS cashier interface with barcode & item name search.',
                    'Full keyboard shortcuts support for lightning-fast cashier operation.',
                    'One-click conversion from Quotations to Sales Invoices.',
                    'Flexible discounts, VAT tax handling, and delivery fee calculation.',
                    'Instant invoice printing (Thermal 80mm & Standard A4) with company branding.',
                    'Real-time invoice status tracking (Paid, Partial, Unpaid, Voided).'
                ]
            ],
            [
                'id' => 'inventory-units',
                'category' => 'inventory',
                'icon' => 'bi-box-seam-fill',
                'badge_color' => 'success',
                'title_ar' => 'إدارة المخازن والأصناف متعددة الوحدات',
                'title_en' => 'Multi-Unit Inventory & Warehouse Management',
                'summary_ar' => 'التحكم الكامل في المخزون، تتبع الكميات، وإدارة متعدد الوحدات (حبة، كرتونة، طرد) بالتحويل الآلي.',
                'summary_en' => 'Full inventory control, stock tracking, and multi-unit handling (piece, box, carton) with automatic conversion.',
                'features_ar' => [
                    'ربط الصنف الواحد بأكثر من وحدة قياس (مثال: كرتونة تحتوي 24 حبة).',
                    'تحويل تلقائي ومعالجة الأسعار والكميات بين الوحدات الكبرى والصغرى.',
                    'إدارة مخازن متعددة ومتابعة رصيد كل مخزن بشكل مستقل.',
                    'نظام تنبيهات ذكي عند وصول المخزون للحد الأدنى (Reorder Level).',
                    'تقييم حركة الأصناف ورصد البضائع الأكثر مبيعاً أو الركود.',
                    'دعم الباركود، التصنيفات الفرعية، والأرقام التسلسلية.'
                ],
                'features_en' => [
                    'Multi-unit mapping for single items (e.g. Carton = 24 Pieces).',
                    'Automatic quantity and pricing conversion between master & sub-units.',
                    'Multi-warehouse management with real-time stock separation.',
                    'Smart low-stock alerts when reaching predefined reorder points.',
                    'Item movement evaluation & fast/slow-moving inventory analytics.',
                    'Barcode support, item sub-categories, and serial numbers.'
                ]
            ],
            [
                'id' => 'purchases-suppliers',
                'category' => 'purchases',
                'icon' => 'bi-cart-plus-fill',
                'badge_color' => 'warning',
                'title_ar' => 'المشتريات وحسابات الموردين',
                'title_en' => 'Purchases & Supplier Account Management',
                'summary_ar' => 'تسجيل فواتير الشراء، متابعة الموردين، وإدارة المستندات والمرفقات الرسمية.',
                'summary_en' => 'Record purchase invoices, manage supplier ledgers, and attach official documents.',
                'features_ar' => [
                    'إنشاء وتسجيل فواتير الشراء وتحديث كميات المخزن تلقائياً.',
                    'احتساب متوسط تكلفة الشراء وتكلفة الصنف اللحظية.',
                    'دليل شامل للموردين مع كشوفات حسابات تفصيلية للحركات.',
                    'رفع المرفقات والوثائق والعقود الخاصة بالموردين أو الشحنات.',
                    'سداد مستحقات الموردين وتوليد سندات الصرف المرتبطة.'
                ],
                'features_en' => [
                    'Create purchase invoices with automatic stock level increments.',
                    'Real-time average cost calculation for items.',
                    'Supplier directory with detailed financial account statements.',
                    'Attachment management for uploading purchase receipts & supplier contracts.',
                    'Supplier payment settlement linked to official payment vouchers.'
                ]
            ],
            [
                'id' => 'finance-accounts',
                'category' => 'finance',
                'icon' => 'bi-bank2',
                'badge_color' => 'info',
                'title_ar' => 'المنظومة المالية وشجرة الحسابات (5 مستويات) والشيكات',
                'title_en' => 'Financial System, 5-Level Chart of Accounts & Cheques',
                'summary_ar' => 'نظام محاسبي احترافي متكامل مع خزن سيولة، ورديات الكاشير، سندات قبض وصرف، وشجرة حسابات 5 مستويات.',
                'summary_en' => 'Professional accounting suite with cashbox liquidity, shifts, payment vouchers, and 5-level COA.',
                'features_ar' => [
                    'شجرة حسابات شجرية محاسبية حتى 5 مستويات (أصول، خصوم، ملكية، إيرادات، مصروفات).',
                    'إدارة الخزن النقدية والنقدية بالبنوك وفتح/إغلاق ورديات الكاشير.',
                    'إصدار وتصميم سندات المقبوضات وسندات المدفوعات مع خيارات الإلغاء الطباعة.',
                    'منظومة تتبع الشيكات الصادرة والواردة وتحديث حالاتها (مستحق، محصل، مرجع).',
                    'قيود محاسبية آمنة يتم توليدها تلقائياً مع المبيعات والمشتريات.',
                    'دعم متعدد العملات وأسعار الصرف بالنسبة للعملة الأساسية.'
                ],
                'features_en' => [
                    'Flexible 5-Level Chart of Accounts (Assets, Liabilities, Equity, Revenues, Expenses).',
                    'Cashbox & liquidity management with Cashier Shift Open/Close routines.',
                    'Issuance of official Payment & Receipt Vouchers with printing & cancellation options.',
                    'Complete Cheque management lifecycle (Due, Deposited, Collected, Bounced).',
                    'Automatic background double-entry journal entries for sales and purchases.',
                    'Multi-currency support with custom exchange rates against base currency.'
                ]
            ],
            [
                'id' => 'users-audit',
                'category' => 'security',
                'icon' => 'bi-shield-lock-fill',
                'badge_color' => 'danger',
                'title_ar' => 'إدارة المستخدمين والصلاحيات وسجل تتبع الرقابة',
                'title_en' => 'Users, Dynamic RBAC Permissions & Audit Trail Log',
                'summary_ar' => 'مصفوفة أدوار وصلاحيات دقيقة لحماية البيانات، مع سجل تدقيق تفصيلي لكل إجراء في النظام.',
                'summary_en' => 'Granular role-based access control matrix with full audit trail logging for accountability.',
                'features_ar' => [
                    'مصفوفة تفاعلية مخصصة لإسناد الأدوار والصلاحيات (عرض، إضافة، تعديل، حذف).',
                    'حظر وتفعيل حسابات المستخدمين وإعادة تعيين كلمة المرور بكل سهولة.',
                    'سجل تدقيق كامل (Audit Trail & Activity Log) يسجل اسم المستخدم، وقت الحركة، نوع العملية، والتفاصيل.',
                    'تخصيص صلاحيات الوصول حسب الفروع المعتمدة للمستخدم.',
                    'دعم مصادقة آمنة لجلسات الدخول وتنبيهات الأمان.'
                ],
                'features_en' => [
                    'Interactive RBAC Permission Matrix for fine-grained action rights (View, Create, Edit, Delete).',
                    'User account status toggling (Active/Inactive) and quick password resets.',
                    'Comprehensive Audit Trail Log recording user, timestamp, operation type, and exact data changes.',
                    'Branch-level data access restrictions for multi-branch environments.',
                    'Secure session handling and security event logging.'
                ]
            ],
            [
                'id' => 'reports-analytics',
                'category' => 'reports',
                'icon' => 'bi-bar-chart-line-fill',
                'badge_color' => 'purple',
                'title_ar' => 'مركز التقارير المتقدمة والتحليلات',
                'title_en' => 'Advanced Reports & Business Intelligence',
                'summary_ar' => 'تقارير مالية، كشوفات حسابات تفصيلية للعملاء والموردين، وتقارير حركة المخزون.',
                'summary_en' => 'Comprehensive financial reporting, detailed customer/supplier account statements, and inventory analysis.',
                'features_ar' => [
                    'مركز تقارير موحد يستعرض الأداء المالي والمبيعات لفترات مخصصة.',
                    'كشف حساب عميل وكشف حساب مورد تفصيلي بالرصيد المتبقي والعمليات.',
                    'تقارير المخزون وقيمة المواد والمنتجات المتوفرة بالمخازن.',
                    'تقارير المبيعات والأرباح وتتبع إجمالي الفواتير والخصومات.',
                    'خيارات طباعة مباشرة وتصدير البيانات بصيغ قياسية.'
                ],
                'features_en' => [
                    'Unified reporting center providing business analytics for custom date ranges.',
                    'Detailed Customer & Supplier account statements showing transactions and net balance.',
                    'Inventory valuation & stock movement breakdown across all warehouses.',
                    'Sales & Profitability reports tracking gross revenue, net profit, and discounts.',
                    'Direct printing and export options for all system reports.'
                ]
            ]
        ];

        return view('guide.index', compact('facilityName', 'primaryColor', 'modules'));
    }
}
