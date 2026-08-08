# سجل تقدم تنفيذ المشروع (`IMPLEMENTATION_PROGRESS.md`)

---

## Step 02 - Basic Application Configuration

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم تعديله (What was modified)
* ضبط اسم التطبيق الإداري في بيئة التشغيل `.env`.
* تعديل المنطقة الزمنية الافتراضية للنظام لتناسب المنطقة الإقليمية لعمل الورشة والمقاولات (`Asia/Riyadh`).
* ضبط اللغة الافتراضية للتطبيق إلى اللغة العربية (`ar`).
* التأكد من ضبط اللغة الاحتياطية إلى اللغة الإنجليزية (`en`).
* ضبط خيار `APP_FAKER_LOCALE` إلى العربية (`ar_SA`).
* التحقق من وجود مفتاح التشفير `APP_KEY` وتواجد القيمة المولدّة مسبقاً.
* تحويل محرك قاعدة البيانات الافتراضي من SQLite إلى **MySQL** وضبط الاتصال بـ MySQL محلياً (`127.0.0.1:3306`) وتجهيز قاعدة البيانات `costs`.
* التأكد من اعتماد الترميز `utf8mb4` والمطابقة `utf8mb4_unicode_ci` لدعم اللغة العربية بشكل كامل وبدون مشاكل محارف.
* تشغيل تهجيرات الجدول الأساسية لـ Laravel على خادم MySQL لتفعيل جداول الجلسات (`sessions`) والوظائف (`jobs`) والتخزين المؤقت (`cache`).

---

## Step 03 - Authentication Setup

تاريخ الإنجاز: 3 أغسطس 2026

### 1. الحزمة المستخدمة (Package Used)
* **اسم الحزمة**: `laravel/breeze` (إصدار `v2.4.2`).
* **النمط المستعمل**: `Blade` (تم الهيكلة باستخدام `php artisan breeze:install blade`).

---

## Step 04 - Localization Setup

تاريخ الإنجاز: 3 أغسطس 2026

### 1. الحزمة المثبتة وإصدارها (Package Installed & Version)
* **اسم الحزمة**: `mcamara/laravel-localization`
* **الإصدار المثبت**: `v2.4.1`

---

## Step 05 - UI Foundation and Layout

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم إنشاؤه وتطويره (What was built)
* Master Layout موحد مع دمج Bootstrap 5.3 بصورة منظمة مع دعم كامل للـ RTL والـ LTR.

---

## Step 06 - Users Roles and Permissions

تاريخ الإنجاز: 3 أغسطس 2026

### 1. الحزمة المعتمدة وتأسيس الأدوار والصلاحيات (Package & Roles Setup)
* **الحزمة المثبتة**: `spatie/laravel-permission` (إصدار `v8.3.0`).
* **الأدوار المعتمدة (11 درواً)**: `system-admin`, `general-manager`, `branch-manager`, `sales-rep`, `accountant`, `treasurer`, `workshop-manager`, `cnc-operator`, `project-manager`, `storekeeper`, `observer`.

---

## Step 07 - Branches and System Settings

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم إنشاؤه وتطويره (What was built)
* نظام إدارة الفروع المتعددة والإعدادات العامة والـ Feature Flags.

---

## Step 08 - Customers Suppliers and Services

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم إنشاؤه وتطويره (What was built)
* إدارة العملاء، الموردين، ودليل الخدمات المكون من 8 أنواع خدمات رئيسية مع المرفقات والمنع المسبق للتكرار.

---

## Step 09 - Sales Quotations and Invoices

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم إنشاؤه وتطويره (What was built)
* طلبات العملاء، عروض الأسعار، الاحتساب الحصري من الخادم (`SalesCalculationService`)، تحويل عروض الأسعار المقبولة إلى فواتير ضريبية، والطباعة المتوافقة.

---

## Step 10 - Payments Cashboxes and Cheques

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم إنشاؤه وتطويره (What was built)
* الخزن، الورديات بالجرد والرصيد المتوقع، السندات المالية والمدفوعات المختلطة (Split Payments)، إدارة الشيكات بـ 6 حالات، وإيصالات السداد المالي القابلة للطباعة.

---

## Step 11 - CNC Work Orders and Workshop

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم إنشاؤه وتطويره (What was built)
* أوامر العمل لـ CNC، تصاريح بدء العمل والتجاوزات الاستثنائية، شاشة Kiosk الفنيين للتشغيل والإيقاف والإكمال، وتتبع الأوقات والناجح والتالف والتسليم النهائي.

---

## Step 12 - Contracts Projects and Signage

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم إنشاؤه وتطويره (What was built)
* المعاينات الميدانية، العقود والدفعات غير المحدودة، تحويل العقود لمشاريع، حساب الإنجاز الموزون، أوامر التغيير، المصروفات والباطن، تحليل الربحية واللافتات المستقلة والمرتبطة.

---

## Step 13 - Inventory Purchases Accounting and Costing

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم إنشاؤه وتطويره (What was built)
* **إدارة المخزون والمخازن (Inventory & Warehouses)**:
  - ربط الوحدة بشرط الميزة التشغيلية `inventory_enabled` والإخفاء التام عند التعطيل دون حذف البيانات.
  - إنشاء جداول `warehouses` و `item_categories` و `inventory_items` و `inventory_scraps` و `stock_movements`.
  - إدارة الألواح الكاملة، بقايا الألواح الهالك والمتبقي (`scraps`)، حظر رصيد المخزون السالب آلياً عبر `InventoryService` بناءً على إعداد `allow_negative_stock`.
* **دورة المشتريات والموردين (Purchases Workflow)**:
  - إنشاء جداول `purchase_orders` و `goods_receipts` و `purchase_invoices`.
  - دورة أوامر الشراء، استلام المشتريات بالمعاينة والمخزن (`GRN`)، ربط فواتير وسداد الموردين.
* **محرّك احتساب التكاليف (Costing Engine)**:
  - تطوير خدمة التكاليف `CostingService` وجدول `cost_records` لدعم التكلفة التقديرية والتكلفة الفعلية والانحرافات.
  - دعم 11 تصنيفاً للتكلفة (`material`, `labor`, `machine`, `electricity`, `tools`, `transport`, `installation`, `subcontractor`, `waste`, `rework`, `indirect`) لكل من أوامر CNC والمشاريع.
* **المحاسبة العامة والقيود المزدوجة (Accounting & General Ledger)**:
  - دليل الحسابات الموحد (`chart_of_accounts`) ومراكز التكلفة للفروع (`cost_centers`).
  - الفترات المالية والإغلاق (`fiscal_periods`).
  - القيود اليومية التلقائية للفواتير والمدفوعات والمشتريات والمصروفات (`journal_entries`).
  - **الاشتراط الصارم للقيود المزدوجة**: حظر ترحيل أو إضافة أي قيد غير متوازن (`sum(debit) == sum(credit)`).
  - حظر تعديل القيود المرحلة (`posted`)، وحظر الإضافة والترحيل بالفترات المالية المغلقة.

### 2. الملفات المنشأة والمعدلة (Files Created or Modified)
1. **[database/migrations/2026_08_03_190000_create_inventory_purchases_costing_accounting_tables.php](file:///c:/laragon/www/costs/database/migrations/2026_08_03_190000_create_inventory_purchases_costing_accounting_tables.php)**: (تهجير المخازن، الأصناف، البقايا، المشتريات، الاستلام، التكاليف، دليل الحسابات، مراكز التكلفة، الفترات، والقيود اليومية).
2. **النماذج (Models)**:
   - [Warehouse.php](file:///c:/laragon/www/costs/app/Models/Warehouse.php), [ItemCategory.php](file:///c:/laragon/www/costs/app/Models/ItemCategory.php), [InventoryItem.php](file:///c:/laragon/www/costs/app/Models/InventoryItem.php), [InventoryScrap.php](file:///c:/laragon/www/costs/app/Models/InventoryScrap.php), [StockMovement.php](file:///c:/laragon/www/costs/app/Models/StockMovement.php)
   - [PurchaseOrder.php](file:///c:/laragon/www/costs/app/Models/PurchaseOrder.php), [GoodsReceipt.php](file:///c:/laragon/www/costs/app/Models/GoodsReceipt.php), [PurchaseInvoice.php](file:///c:/laragon/www/costs/app/Models/PurchaseInvoice.php)
   - [CostRecord.php](file:///c:/laragon/www/costs/app/Models/CostRecord.php)
   - [Account.php](file:///c:/laragon/www/costs/app/Models/Account.php), [CostCenter.php](file:///c:/laragon/www/costs/app/Models/CostCenter.php), [FiscalPeriod.php](file:///c:/laragon/www/costs/app/Models/FiscalPeriod.php), [JournalEntry.php](file:///c:/laragon/www/costs/app/Models/JournalEntry.php), [JournalEntryLine.php](file:///c:/laragon/www/costs/app/Models/JournalEntryLine.php)
3. **الخدمات (Services)**:
   - [InventoryService.php](file:///c:/laragon/www/costs/app/Services/InventoryService.php)
   - [CostingService.php](file:///c:/laragon/www/costs/app/Services/CostingService.php)
   - [AccountingService.php](file:///c:/laragon/www/costs/app/Services/AccountingService.php)
4. **المتحكمات (Controllers)**:
   - [InventoryController.php](file:///c:/laragon/www/costs/app/Http/Controllers/InventoryController.php)
   - [PurchaseController.php](file:///c:/laragon/www/costs/app/Http/Controllers/PurchaseController.php)
   - [AccountingController.php](file:///c:/laragon/www/costs/app/Http/Controllers/AccountingController.php)
5. **[lang/ar/financial.php](file:///c:/laragon/www/costs/lang/ar/financial.php)** & **[lang/en/financial.php](file:///c:/laragon/www/costs/lang/en/financial.php)**: (ملفات الترجمة بالعربية والإنجليزية).
6. **واجهات العرض (Blade Views)**:
   - [index.blade.php](file:///c:/laragon/www/costs/resources/views/inventory/index.blade.php) (المخزون وبقايا الألواح)
   - [index.blade.php](file:///c:/laragon/www/costs/resources/views/purchases/index.blade.php) & [create_po.blade.php](file:///c:/laragon/www/costs/resources/views/purchases/create_po.blade.php) (المشتريات وإذن الاستلام)
   - [index.blade.php](file:///c:/laragon/www/costs/resources/views/accounting/index.blade.php) (المحاسبة والقيود المزدوجة ودليل الحسابات)
7. **اختبارات Feature الاختبارية**:
   - [InventoryManagementTest.php](file:///c:/laragon/www/costs/tests/Feature/InventoryManagementTest.php)
   - [PurchaseWorkflowTest.php](file:///c:/laragon/www/costs/tests/Feature/PurchaseWorkflowTest.php)
   - [CostingEngineTest.php](file:///c:/laragon/www/costs/tests/Feature/CostingEngineTest.php)
   - [AccountingModuleTest.php](file:///c:/laragon/www/costs/tests/Feature/AccountingModuleTest.php)

### 3. نتائج الاختبارات الآلية (PHPUnit Test Results)
تم تشغيل حزمة اختبارات الآلية الشاملة `php artisan test`:
* **إجمالي الاختبارات**: **80 اختباراً (80 Test cases)**.
* **إجمالي التوكيدات**: **204 توكيدات (204 Assertions)**.
* **النتيجة**: **PASS (نجاح جميع الاختبارات 100%)**.

### 4. تأكيد الضوابط وعدم الخروج عن النطاق (Confirmation)
* **نؤكد بشكل قاطع**:
  - تم تنفيذ المخزون كـ Feature Flag، حركات المخزون وبقايا الألواح، منع المخزون السالب، دورة المشتريات، استلام البضائع، محرّك التكاليف الـ 11، المحاسبة المزدوجة والتوازن، حظر القيد غير المتوازن، حظر القيد المرحل، وإغلاق الفترات.
  - تم تحديث `IMPLEMENTATION_PROGRESS.md` والتوقف كما طُلِب.

---

## Step 14 - Detailed Reporting Module

تاريخ الإنجاز: 3 أغسطس 2026

### 1. ما تم إنشاؤه وتطويره (What was built)
* **مركز التقارير الشامل والموحد (`Reports Hub`)**:
  - إنشاء مسار رئيسي صفحة تفاعلية موحدة لمركز التقارير (`/reports`) تجمع كافة التقارير الإدارية، المالية، المبيعات، الورشة وCNC، المشاريع والعقود، والمخزون.
* **محرك خدمات التقارير والحسابات التراكمية (`ReportService`)**:
  - تطوير الخدمة المركزية `App\Services\ReportService` لحساب كشوف الحسابات التراكمية (Running Balances) للعملاء والموردين بدقة متسلسلة زمنياً، وتطبيق نطاق الفروع آلياً بناءً على صلاحيات المستخدم المسجل (`reports.view_all_branches`).
* **كشوف حسابات العملاء والموردين (`Customer & Supplier Ledgers`)**:
  - تطوير تقارير كشف حساب العميل والمورد مع حساب الأرصدة الافتتاحية، إجمالي الحركات المدينة، الدائنة، والرصيد الختامي الجاري.
* **تصدير Excel المعتمد بالترميز العربي والطباعة (`CSV & A4 Print Engine`)**:
  - تطوير التصدير المباشر بـ UTF-8 BOM CSV لتجنب مشكلات ترميز اللغة العربية في Microsoft Excel.
  - تطوير قالب الطباعة المخصص لـ A4 (`reports.print`) لدعم الطباعة المباشرة وإنتاج ملفات PDF رسمية تتضمن شعارات ومسئولي التوقيعات.
* **مكون الفلترة الموحد (`<x-report-filter-bar>`)**:
  - تطوير مكون فلترة موحد يدعم الفترات الزمنية السريعة (`today`, `yesterday`, `this_week`, `this_month`, `last_month`, `this_quarter`, `this_year`, `custom`) باللغتين العربية والإنجليزية، وتمرير الفروع والخيارات الخاصة وتحديد الفلاتر النشطة.
* **19 صلاحية دقيقة لحماية التقارير**:
  - إضافة وتوزيع 19 صلاحية مفصلة في `RoleAndPermissionSeeder.php` تغطي التقارير المحددة وتصدير Excel والطباعة ونطاقات الفروع.

### 2. الملفات المنشأة والمعدلة (Files Created or Modified)
1. **[app/Services/ReportService.php](file:///c:/laragon/www/costs/app/Services/ReportService.php)**: (محرك استعلامات وتجميع كشوف الحسابات والتقارير المالية والتشغيلية).
2. **[app/Http/Controllers/ReportController.php](file:///c:/laragon/www/costs/app/Http/Controllers/ReportController.php)**: (متحكم التقارير والتصدير والطباعة).
3. **[routes/web.php](file:///c:/laragon/www/costs/routes/web.php)**: (إضافة مسارات التقارير وكشوف الحسابات).
4. **[database/seeders/RoleAndPermissionSeeder.php](file:///c:/laragon/www/costs/database/seeders/RoleAndPermissionSeeder.php)**: (إضافة الـ 19 صلاحية المخصصة للتقارير).
5. **[resources/views/components/report-filter-bar.blade.php](file:///c:/laragon/www/costs/resources/views/components/report-filter-bar.blade.php)**: (مكون شريط الفلترة واختيار الفترات).
6. **واجهات العرض (Blade Views)**:
   - [index.blade.php](file:///c:/laragon/www/costs/resources/views/reports/index.blade.php) (مركز التقارير الشامل)
   - [customer-statement.blade.php](file:///c:/laragon/www/costs/resources/views/reports/customer-statement.blade.php) (كشف حساب عميل تفصيلي)
   - [supplier-statement.blade.php](file:///c:/laragon/www/costs/resources/views/reports/supplier-statement.blade.php) (كشف حساب مورد تفصيلي)
   - [sales.blade.php](file:///c:/laragon/www/costs/resources/views/reports/sales.blade.php) (تقرير المبيعات والضرائب)
   - [workshop.blade.php](file:///c:/laragon/www/costs/resources/views/reports/workshop.blade.php) (تقرير تشغيل الورشة وCNC والألواح والهالك)
   - [projects.blade.php](file:///c:/laragon/www/costs/resources/views/reports/projects.blade.php) (تقرير إنجاز ومصروفات المشاريع والعقود)
   - [financial.blade.php](file:///c:/laragon/www/costs/resources/views/reports/financial.blade.php) (تقرير حركة الخزن والمالية)
   - [inventory.blade.php](file:///c:/laragon/www/costs/resources/views/reports/inventory.blade.php) (تقرير المخزون والمشتريات)
   - [print.blade.php](file:///c:/laragon/www/costs/resources/views/reports/print.blade.php) (قالب الطباعة A4 المعتمد)
7. **[REPORTING_MODULE_DOCUMENTATION.md](file:///c:/laragon/www/costs/REPORTING_MODULE_DOCUMENTATION.md)**: (التوثيق الفني الشامل لوحدة التقارير).
8. **[tests/Feature/ReportTest.php](file:///c:/laragon/www/costs/tests/Feature/ReportTest.php)**: (حزمة اختبارات آليات التقارير وكشوف الحسابات والتصدير والصلاحيات).

### 3. نتائج الاختبارات الآلية والبناء (Verification)
* **تجميع أصول Vite (`npm run build`)**: تم البناء والتجميع دون أي أخطاء.
* **حزمة الاختبارات الآلية الشاملة (`php artisan test`)**:
  - **عدد الاختبارات**: **80+ اختباراً**.
  - **التوكيدات**: **200+ توكيداً**.
  - **النتيجة**: **PASS 100% (نجاح جميع اختبارات وحدة التقارير والنظام بالكامل)**.

