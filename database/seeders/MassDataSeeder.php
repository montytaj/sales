<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Service;
use App\Models\CustomerOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Cashbox;
use App\Models\PaymentVoucher;
use App\Models\PaymentVoucherLine;
use App\Models\Cheque;
use App\Models\WorkOrder;
use App\Models\WorkOrderAuthorization;
use App\Models\WorkOrderTimeLog;
use App\Models\SiteSurvey;
use App\Models\Contract;
use App\Models\ContractPaymentTerm;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\ProjectExpense;
use App\Models\SignageOrder;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\PurchaseInvoice;
use App\Models\Account;
use App\Models\CostCenter;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SystemNotification;

class MassDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first() ?: User::first();
        $salesUser = User::where('email', 'sales@example.com')->first() ?: $admin;
        $accountantUser = User::where('email', 'accountant@example.com')->first() ?: $admin;
        $workshopUser = User::where('email', 'workshop@example.com')->first() ?: $admin;
        $cncUser = User::where('email', 'cnc@example.com')->first() ?: $admin;
        $pmUser = User::where('email', 'pm@example.com')->first() ?: $admin;

        $branches = Branch::where('is_active', true)->get();
        if ($branches->isEmpty()) return;
        $mainBranch = $branches->first();
        $workshopBranch = $branches->skip(1)->first() ?? $mainBranch;

        $customers = Customer::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $services = Service::where('is_active', true)->get();
        $cashboxes = Cashbox::where('is_active', true)->get();
        $mainCashbox = $cashboxes->first();
        $salesCashbox = $cashboxes->skip(1)->first() ?? $mainCashbox;

        $now = Carbon::now();

        // 1. Seed 25 Customer Orders (طلبات المبيعات)
        $orderSummaries = [
            'تصنيع كاونترات وأرفف عرض ملامين للمحل الجديد',
            'تجهيز وتأثيث مكاتب إدارية وقواطع خشبية CNC',
            'رفع مقاسات وتصنيع مطبخ MDF ألماني فاخر',
            'لوحة إعلانية بارزة 3D ومضيئة بالزنكور والأكريليك',
            'ديكورات وتأثيث غرف نوم ودريسنج روم',
            'قص وتفريغ 40 لوح MDF بالـ CNC لحساب معرض الديكور',
            'تجهيز واجهة مطعم وسلسلة كاونترات استقبال',
            'تأثيث وتجهيز عيادة طبية ولافتات إرشادية داخلية',
        ];

        $orderStatuses = ['pending', 'quoted', 'converted', 'cancelled'];

        for ($i = 1; $i <= 25; $i++) {
            $cust = $customers->random();
            $br = $branches->random();
            $orderNum = 'ORD-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);

            CustomerOrder::firstOrCreate([
                'order_number' => $orderNum
            ], [
                'customer_id' => $cust->id,
                'branch_id' => $br->id,
                'status' => $orderStatuses[array_rand($orderStatuses)],
                'requirements_summary' => $orderSummaries[array_rand($orderSummaries)],
                'notes' => 'طلب مسجل من منصة النظام لعام 2026',
                'created_by' => $salesUser->id,
                'created_at' => $now->copy()->subDays(rand(1, 150)),
            ]);
        }

        // 2. Seed 20 Quotations (عروض الأسعار)
        for ($i = 1; $i <= 20; $i++) {
            $cust = $customers->random();
            $br = $branches->random();
            $qNum = 'OFFER-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $subtotal = rand(50000, 450000);
            $tax = $subtotal * 0.15;
            $total = $subtotal + $tax;

            $q = Quotation::firstOrCreate([
                'quotation_number' => $qNum
            ], [
                'customer_id' => $cust->id,
                'branch_id' => $br->id,
                'status' => rand(0, 1) ? 'accepted' : 'sent',
                'is_approved' => true,
                'approved_by' => $admin->id,
                'approved_at' => $now->copy()->subDays(rand(5, 90)),
                'issue_date' => $now->copy()->subDays(rand(10, 100)),
                'expiry_date' => $now->copy()->addDays(rand(15, 45)),
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'notes' => 'عرض سعر ساري لمدة 30 يوماً من تاريخ الإصدار',
                'terms_conditions' => 'الدفع 50% مقدماً والـ 50% عند التسليم',
                'created_by' => $salesUser->id,
            ]);

            if ($services->isNotEmpty()) {
                QuotationItem::firstOrCreate([
                    'quotation_id' => $q->id,
                    'item_name' => 'خدمة تصنيع وديكورات مخصصة'
                ], [
                    'service_id' => $services->random()->id,
                    'description' => 'أعمال خشبية ولافتات مخصصة حسب المواصفات',
                    'quantity' => rand(2, 10),
                    'unit_of_measure' => 'm2',
                    'unit_price' => rand(1500, 5000),
                    'subtotal' => $subtotal,
                    'tax_percent' => 15.00,
                    'tax_amount' => $tax,
                    'total' => $total,
                    'sort_order' => 1,
                ]);
            }
        }

        // 3. Seed 25 Sales Invoices & Payment Receipts (فواتير مبيعات وسندات قبض)
        for ($i = 1; $i <= 25; $i++) {
            $cust = $customers->random();
            $br = $branches->random();
            $invNum = 'INV-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $subtotal = rand(60000, 600000);
            $tax = $subtotal * 0.15;
            $total = $subtotal + $tax;
            $issueDate = $now->copy()->subDays(rand(2, 160));
            $isPaid = rand(0, 1);

            $inv = Invoice::firstOrCreate([
                'invoice_number' => $invNum
            ], [
                'customer_id' => $cust->id,
                'branch_id' => $br->id,
                'created_by' => $salesUser->id,
                'issue_date' => $issueDate,
                'due_date' => $issueDate->copy()->addDays(30),
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'status' => $isPaid ? 'paid' : 'partially_paid',
                'notes' => 'فاتورة مبيعات معتمدة بالنظام',
            ]);

            InvoiceItem::firstOrCreate([
                'invoice_id' => $inv->id,
                'item_name' => 'أعمال ومبيعات ديكور ولافتات'
            ], [
                'service_id' => $services->first()?->id,
                'description' => 'توريد وتصنيع حسب المخطط المعتمد',
                'quantity' => 1,
                'unit_of_measure' => 'job',
                'unit_price' => $subtotal,
                'subtotal' => $subtotal,
                'tax_percent' => 15.00,
                'tax_amount' => $tax,
                'total' => $total,
            ]);

            // Payment Voucher for Invoice
            $payAmount = $isPaid ? $total : ($total / 2);
            $vchNum = 'REC-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);

            $pv = PaymentVoucher::firstOrCreate([
                'voucher_number' => $vchNum
            ], [
                'type' => 'receipt',
                'customer_id' => $cust->id,
                'invoice_id' => $inv->id,
                'cashbox_id' => $cashboxes->random()->id,
                'created_by' => $accountantUser->id,
                'payment_date' => $issueDate->copy()->addDays(rand(1, 10)),
                'amount' => $payAmount,
                'notes' => 'سداد دفعة مالية نقداً/بنك',
                'status' => 'completed',
            ]);

            PaymentVoucherLine::firstOrCreate([
                'payment_voucher_id' => $pv->id,
                'reference_number' => 'REF-MASS-' . $i
            ], [
                'payment_method' => rand(0, 1) ? 'cash' : 'bank_transfer',
                'amount' => $payAmount,
                'notes' => 'تحصيل إلكتروني عبر النظام',
            ]);
        }

        // 4. Seed 15 Purchase Orders & Supplier Payments (أوامر شراء وسندات صرف)
        for ($i = 1; $i <= 15; $i++) {
            $supp = $suppliers->random();
            $br = $branches->random();
            $poNum = 'PO-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $totalAmount = rand(80000, 500000);
            $tax = $totalAmount * 0.15;
            $net = $totalAmount + $tax;

            $po = PurchaseOrder::firstOrCreate([
                'po_number' => $poNum
            ], [
                'supplier_id' => $supp->id,
                'branch_id' => $br->id,
                'total_amount' => $totalAmount,
                'tax_amount' => $tax,
                'net_amount' => $net,
                'status' => 'received',
                'order_date' => $now->copy()->subDays(rand(5, 120)),
                'notes' => 'أمر توريد أخشاب واكسسوارات ومواد خام',
                'created_by' => $admin->id,
            ]);

            // Supplier Payment Voucher
            $vchNum = 'PAY-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);
            PaymentVoucher::firstOrCreate([
                'voucher_number' => $vchNum
            ], [
                'type' => 'payment',
                'supplier_id' => $supp->id,
                'cashbox_id' => $mainCashbox->id,
                'created_by' => $accountantUser->id,
                'payment_date' => $now->copy()->subDays(rand(1, 100)),
                'amount' => $net,
                'notes' => 'سداد مستحقات توريد المورد',
                'status' => 'completed',
            ]);
        }

        // 5. Seed 15 Work Orders (أوامر عمل الورشة)
        for ($i = 1; $i <= 15; $i++) {
            $cust = $customers->random();
            $woNum = 'WO-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);

            WorkOrder::firstOrCreate([
                'work_order_number' => $woNum
            ], [
                'customer_id' => $cust->id,
                'branch_id' => $workshopBranch->id,
                'assigned_to' => $cncUser->id,
                'assigned_by' => $workshopUser->id,
                'sheet_count' => rand(10, 100),
                'sheet_type' => 'ألواح MDF ملامين 18 ملم',
                'dimensions' => '122cm x 244cm',
                'thickness' => '18mm',
                'due_date' => $now->copy()->addDays(rand(1, 30)),
                'status' => rand(0, 1) ? 'in_progress' : 'completed',
                'priority' => rand(0, 1) ? 'urgent' : 'high',
                'good_pieces' => rand(10, 80),
                'waste_pieces' => rand(0, 3),
                'notes' => 'أمر تنفيذ ورشة للـ CNC وتجميع الأخشاب',
                'created_by' => $workshopUser->id,
            ]);
        }

        // 6. Seed 10 Site Surveys & Contracts & Projects
        for ($i = 1; $i <= 10; $i++) {
            $cust = $customers->random();
            
            // Site Survey
            SiteSurvey::firstOrCreate([
                'survey_number' => 'SRV-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT)
            ], [
                'customer_id' => $cust->id,
                'site_address' => 'مقر الموقع الرئيسي - شارع ' . rand(10, 90),
                'dimensions_data' => 'المساحة ' . rand(100, 600) . ' م2، الارتفاع 3.5 م',
                'notes' => 'تم رفع كافة المقاسات واعتماد المخططات',
                'assigned_to' => $pmUser->id,
                'survey_date' => $now->copy()->subDays(rand(10, 90)),
                'status' => 'completed',
                'created_by' => $pmUser->id,
            ]);

            // Contract
            $cntNum = 'CNT-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $cAmount = rand(200000, 900000);
            $contract = Contract::firstOrCreate([
                'contract_number' => $cntNum
            ], [
                'customer_id' => $cust->id,
                'branch_id' => $mainBranch->id,
                'scope_of_work' => 'عقد المقاولة والتجهيزات الداخلية لبرج الشركات',
                'total_amount' => $cAmount,
                'discount_amount' => 0.00,
                'tax_amount' => $cAmount * 0.15,
                'net_amount' => $cAmount * 1.15,
                'start_date' => $now->copy()->subDays(rand(30, 90)),
                'end_date' => $now->copy()->addDays(rand(30, 120)),
                'status' => 'active',
                'is_approved' => true,
                'approved_by' => $admin->id,
                'approved_at' => $now->copy()->subDays(30),
                'created_by' => $pmUser->id,
            ]);

            // Project
            $prjNum = 'PRJ-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);
            Project::firstOrCreate([
                'project_number' => $prjNum
            ], [
                'name' => 'مشروع ديكور وتصنيع ' . $cust->name,
                'customer_id' => $cust->id,
                'contract_id' => $contract->id,
                'branch_id' => $mainBranch->id,
                'manager_id' => $pmUser->id,
                'budget' => $cAmount * 1.15,
                'completion_percentage' => rand(25, 90),
                'start_date' => $now->copy()->subDays(30),
                'expected_end_date' => $now->copy()->addDays(90),
                'status' => 'in_progress',
                'notes' => 'مشروع قائم وجاري متابعة المراحل',
                'created_by' => $pmUser->id,
            ]);

            // Signage Order
            $sigNum = 'SIG-2026-M' . str_pad($i, 4, '0', STR_PAD_LEFT);
            SignageOrder::firstOrCreate([
                'order_number' => $sigNum
            ], [
                'customer_id' => $cust->id,
                'dimensions' => '4.5m x 1.2m',
                'status' => 'manufacturing',
                'created_by' => $salesUser->id,
            ]);
        }
    }
}
