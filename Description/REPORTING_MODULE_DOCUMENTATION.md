# توثيق وحدة التقارير التفصيلية (`REPORTING_MODULE_DOCUMENTATION.md`)
# Workshop ERP - Comprehensive Reporting Engine

---

## 1. نظرة عامة والهدف (Overview & Vision)
توفر وحدة **التقارير التفصيلية والشاملة** لـ **Workshop ERP** محركاً مركزياً موحداً يغطي كافة الأنشطة والعمليات المالية، الإدارية، المبيعات، الورش وقطع CNC، المشاريع والعقود، والمالية والخزن والمخزون والمشتريات.

تم تصميم الوحدة لتكون:
* **دقيقة 100%**: حساب الأرصدة التراكمية (Running Balances) للأدفار والكشوف بشكل متسلسل زمنياً.
* **آمنة خاضعة للصلاحيات**: التحكم في الصلاحيات عبر Spatie Permissions وحظر الوصول غير المصرح به على مستوى النقطة النهائية (`Endpoint`) والواجهة.
* **خاضعة لنطاق الفروع (Multi-Branch Scoped)**: تقييد رؤية البيانات آلياً بناءً على الفروع المتاحة للمستخدم المسجل.
* **مرنة وقابلة للتصدير والطباعة**: تصدير مباشر بتنسيق Excel CSV يدعم الترميز العربي UTF-8 BOM وتنسيق الطباعة A4 المخصص لـ Direct Print / PDF.

---

## 2. المسارات المنفذة (Implemented Routes & Endpoints)

| اسم المسار (Route Name) | المسار (URI) | الوصف (Description) | الصلاحية (Permission) |
|---|---|---|---|
| `reports.index` | `/reports` | الصفحة الرئيسية لمركز التقارير الشامل | `reports.access` |
| `reports.customer-statement` | `/reports/customer-statement` | كشف حساب عميل تفصيلي (أرصدة جارية وفواتير وسداد) | `reports.customers.view` |
| `reports.supplier-statement` | `/reports/supplier-statement` | كشف حساب مورد تفصيلي (مشتريات وسداد) | `reports.suppliers.view` |
| `reports.sales` | `/reports/sales` | تقرير المبيعات والضرائب والمبالغ غير المسددة | `reports.sales.view` |
| `reports.workshop` | `/reports/workshop` | تقرير الورشة، أوامر القص، وعدد الألواح والهالك | `reports.workshop.view` |
| `reports.projects` | `/reports/projects` | تقرير إنجاز ومصروفات وميزانيات المشاريع والعقود | `reports.projects.view` |
| `reports.financial` | `/reports/financial` | تقرير حركة الخزن والمالية والتدفق النقدي | `reports.financial.view` |
| `reports.inventory` | `/reports/inventory` | تقرير أرصدة وحركات المواد والمخزون والمشتريات | `reports.inventory.view` |

---

## 3. الصلاحيات المخصصة (Permissions Model)

تم تسجيل وتخصيص 19 صلاحية دقيقة للتقارير في `RoleAndPermissionSeeder.php`:

1. `reports.access`: الصلاحية العامة لفتح مركز التقارير.
2. `reports.sales.view`: عرض تقارير المبيعات والفواتير.
3. `reports.financial.view`: عرض التقارير المالية وحركة الخزن.
4. `reports.customers.view`: عرض كشوف حسابات والتقرير المالي للعملاء.
5. `reports.suppliers.view`: عرض كشوف حسابات وتقرير الموردين.
6. `reports.workshop.view`: عرض تقارير الورشة وأوامر العمل.
7. `reports.cnc.view`: عرض تقارير ماكينات CNC وقطيع الألواح.
8. `reports.projects.view`: عرض تقارير المشاريع والعقود.
9. `reports.signage.view`: عرض تقارير تصنيع وتركيب اللافتات.
10. `reports.inventory.view`: عرض تقارير المخزون والأصناف.
11. `reports.purchases.view`: عرض تقارير طلبات وفواتير المشتريات.
12. `reports.accounting.view`: عرض القيود اليومية وشجرة الحسابات.
13. `reports.profitability.view`: عرض الأرباح وربحية العقود والمشاريع.
14. `reports.costs.view`: عرض التكاليف التقديرية والفعلية.
15. `reports.export_excel`: تصدير التقارير إلى ملفات Excel / CSV.
16. `reports.export_pdf`: تصدير التقارير إلى ملفات PDF.
17. `reports.print`: طباعة التقارير الرسمية.
18. `reports.view_all_branches`: رؤية تقارير كافة الفروع (استثناء من نطاق فرع المستخدم).
19. `reports.view_sensitive_financial_data`: رؤية البيانات المالية الحساسة.

---

## 4. البنية البرمجية والمكونات (Architecture & Components)

### أ. خدمة التقارير المركزية (`App\Services\ReportService`)
تتولى الخدمة تنفيذ جميع الاستعلامات المعقدة والحسابات التراكمية ومنع مشكلة `N+1 Queries` وتطبيق نطاق الفروع:
* `getCustomerStatement()`: تجميع الفواتير وسندات القبض وحساب الرصيد الافتتاحي والرصيد التراكمي لكل حركة.
* `getSupplierStatement()`: تجميع المشتريات وسندات الصرف وحساب رصيد المورد.
* `getSalesReport()`, `getWorkshopReport()`, `getProjectsReport()`, `getFinancialReport()`, `getInventoryReport()`.

### ب. مكون شريط الفلترة الموحد (`<x-report-filter-bar>`)
مكون يدعم الاختيارات السريعة للفترات الزمنية (`today`, `yesterday`, `this_week`, `this_month`, `last_month`, `this_quarter`, `this_year`) باللغتين العربية والإنجليزية، وتمرير الفروع والخيارات الخاصة.

### ج. محرك تصدير Excel والطباعة (`CSV Streamer & Print Template`)
* **CSV Streamer**: تصدير مباشر عبر البث بـ `fputs($file, "\xEF\xBB\xBF");` لدعم الحروف العربية في Microsoft Excel دون تشويه.
* **Print View (`reports.print`)**: قالب A4 مخصص يدعم نمط `@media print` وشعار المنشأة ومسئولي التوقيع.

---

## 5. نتائج الاختبارات الشاملة (Automated Tests Verification)
* تم إنشاء ملف الاختبارات **`tests/Feature/ReportTest.php`**.
* جميع اختبارات التقارير وكشوف الحسابات وتدفقات الصلاحيات وحزمة الاختبارات العامة للمشروع ناجحة بنسبة **100% (80 Test Cases - 207 Assertions)**.
