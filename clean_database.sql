-- ============================================================================
-- SQL Script to Clean Database and Prepare System for Production Client Handover
-- ملف استعلام تنظيف قاعدة البيانات وتجهيز النظام للتسليم للعميل
-- ============================================================================
-- الملف: clean_database.sql
-- الوصف: يقوم هذا السكربت بحذف كافة البيانات التجريبية والمعاملات الحسابية والتشغيلية
-- مع الإبقاء الكامل على المستخدمين، الصلاحيات والأدوار، الإعدادات، الوحدات، العملات،
-- وشجرة الحسابات (المستوى الأول فقط) والهياكل التنظيمية المرجعية للنظام.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- 1. جداول المبيعات وعروض الأسعار والفواتير والطلبات
-- ----------------------------------------------------------------------------
TRUNCATE TABLE `invoice_items`;
TRUNCATE TABLE `invoices`;
TRUNCATE TABLE `quotation_items`;
TRUNCATE TABLE `quotations`;
TRUNCATE TABLE `customer_orders`;
TRUNCATE TABLE `sales_return_items`;
TRUNCATE TABLE `sales_returns`;

-- ----------------------------------------------------------------------------
-- 2. جداول المشتريات وأوامر الشراء واستلام البضائع وفواتير الشراء
-- ----------------------------------------------------------------------------
TRUNCATE TABLE `purchase_invoice_items`;
TRUNCATE TABLE `purchase_invoices`;
TRUNCATE TABLE `goods_receipts`;
TRUNCATE TABLE `purchase_orders`;
TRUNCATE TABLE `purchase_return_items`;
TRUNCATE TABLE `purchase_returns`;

-- ----------------------------------------------------------------------------
-- 3. جداول القيود المحاسبية والحسابات والمالية والسندات والشيكات
-- ----------------------------------------------------------------------------
TRUNCATE TABLE `journal_entry_lines`;
TRUNCATE TABLE `journal_entries`;
TRUNCATE TABLE `payment_voucher_lines`;
TRUNCATE TABLE `payment_vouchers`;
TRUNCATE TABLE `cheques`;
TRUNCATE TABLE `cost_records`;
TRUNCATE TABLE `cost_centers`;
TRUNCATE TABLE `fiscal_periods`;

-- ----------------------------------------------------------------------------
-- 4. جداول المستودعات والمخزون وحركات البضائع والتسويات
-- ----------------------------------------------------------------------------
TRUNCATE TABLE `warehouse_items`;
TRUNCATE TABLE `stock_movements`;
TRUNCATE TABLE `stock_transfer_items`;
TRUNCATE TABLE `stock_transfers`;
TRUNCATE TABLE `warehouse_transfer_items`;
TRUNCATE TABLE `warehouse_transfers`;
TRUNCATE TABLE `stock_adjustment_items`;
TRUNCATE TABLE `stock_adjustments`;
TRUNCATE TABLE `inventory_scraps`;
TRUNCATE TABLE `inventory_items`;

-- ----------------------------------------------------------------------------
-- 5. جداول العملاء والموردين والخدمات التجريبية
-- ----------------------------------------------------------------------------
TRUNCATE TABLE `customers`;
TRUNCATE TABLE `suppliers`;
TRUNCATE TABLE `services`;

-- ----------------------------------------------------------------------------
-- 6. جداول أوامر العمل والمشاريع والعقود والمعاينات
-- ----------------------------------------------------------------------------
TRUNCATE TABLE `work_order_time_logs`;
TRUNCATE TABLE `work_order_authorizations`;
TRUNCATE TABLE `work_orders`;
TRUNCATE TABLE `signage_orders`;
TRUNCATE TABLE `project_expenses`;
TRUNCATE TABLE `project_change_orders`;
TRUNCATE TABLE `project_stages`;
TRUNCATE TABLE `projects`;
TRUNCATE TABLE `contract_payment_terms`;
TRUNCATE TABLE `contracts`;
TRUNCATE TABLE `site_surveys`;

-- ----------------------------------------------------------------------------
-- 7. جداول الورديات وسجلات النظام والمرفقات والإشعارات والجلسات
-- ----------------------------------------------------------------------------
TRUNCATE TABLE `cashbox_shifts`;
TRUNCATE TABLE `activity_logs`;
TRUNCATE TABLE `attachments`;
TRUNCATE TABLE `system_notifications`;
TRUNCATE TABLE `user_notification_preferences`;
TRUNCATE TABLE `password_reset_tokens`;
TRUNCATE TABLE `sessions`;

-- ----------------------------------------------------------------------------
-- 8. جداول المهام المؤقتة والذاكرة التخزينية (Cache & Jobs)
-- ----------------------------------------------------------------------------
TRUNCATE TABLE `cache`;
TRUNCATE TABLE `cache_locks`;
TRUNCATE TABLE `jobs`;
TRUNCATE TABLE `job_batches`;
TRUNCATE TABLE `failed_jobs`;

-- ----------------------------------------------------------------------------
-- 9. تنظيف وتصفية شجرة الحسابات (إبقاء المستوى الأول فقط وتصفير الأرصدة)
-- ----------------------------------------------------------------------------
DELETE FROM `accounts` WHERE `level` > 1 OR `parent_id` IS NOT NULL;
UPDATE `accounts` SET `balance` = 0.00;

DELETE FROM `chart_of_accounts` WHERE `level` > 1 OR `parent_id` IS NOT NULL;
UPDATE `chart_of_accounts` SET `balance` = 0.00;

-- ----------------------------------------------------------------------------
-- 10. إزالة أي ارتباطات متبقية بحسابات فرعية محذوفة
-- ----------------------------------------------------------------------------
UPDATE `cashboxes` SET `account_id` = NULL WHERE `account_id` NOT IN (SELECT `id` FROM `accounts`);

-- ----------------------------------------------------------------------------
-- 11. إعادة تفعيل فحص المفاتيح الأجنبية
-- ----------------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- تم تنظيف قاعدة البيانات بنجاح وتجهيز النظام للتسليم
-- ============================================================================
