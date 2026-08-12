<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
use App\Models\CashboxShift;
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
use App\Models\ProjectChangeOrder;
use App\Models\ProjectExpense;
use App\Models\SignageOrder;
use App\Models\Warehouse;
use App\Models\ItemCategory;
use App\Models\InventoryItem;
use App\Models\InventoryScrap;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\PurchaseInvoice;
use App\Models\CostRecord;
use App\Models\Account;
use App\Models\CostCenter;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SystemNotification;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get or Create Branches
        $mainBranch = Branch::where('code', 'MAIN')->first() ?: Branch::create([
            'code' => 'MAIN',
            'name' => 'الفرع الرئيسي - الخرطوم',
            'address' => 'شارع الستين، الرياض، الخرطوم',
            'phone' => '0912345678',
            'email' => 'khartoum@company.com',
            'is_main' => true,
            'is_active' => true,
        ]);

        $workshopBranch = Branch::where('code', 'BR-01')->first() ?: Branch::create([
            'code' => 'BR-01',
            'name' => 'فرع الورشة والإنتاج - بحري',
            'address' => 'المنطقة الصناعية، بحري',
            'phone' => '0918765432',
            'email' => 'workshop@company.com',
            'is_main' => false,
            'is_active' => true,
        ]);

        // 1. Seed Staff Users
        $usersData = [
            ['email' => 'admin@a.a', 'name' => 'المهندس أحمد مصطفى (المدير العام)', 'role' => 'system-admin'],
            ['email' => 'sales@example.com', 'name' => 'سارة محمود (مسؤول المبيعات)', 'role' => 'sales-rep'],
            ['email' => 'accountant@example.com', 'name' => 'عمر خالد (المحاسب الرئيسي)', 'role' => 'accountant'],
            ['email' => 'workshop@example.com', 'name' => 'المهندس حسن علي (مدير الورشة)', 'role' => 'workshop-manager'],
            ['email' => 'cnc@example.com', 'name' => 'الفني طارق سعيد (مشغل CNC)', 'role' => 'cnc-operator'],
            ['email' => 'pm@example.com', 'name' => 'م. ياسر عبد السلام (مدير المشاريع)', 'role' => 'project-manager'],
        ];

        $seededUsers = [];
        foreach ($usersData as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('Test@1234'),
                    'is_active' => true,
                    'main_branch_id' => $mainBranch->id,
                ]
            );
            $user->branches()->syncWithoutDetaching([$mainBranch->id, $workshopBranch->id]);
            if (!$user->hasRole($u['role'])) {
                $user->assignRole($u['role']);
            }
            $seededUsers[$u['role']] = $user;
        }

        $admin = $seededUsers['system-admin'] ?? User::first();
        $salesUser = $seededUsers['sales-rep'] ?? $admin;
        $accountantUser = $seededUsers['accountant'] ?? $admin;
        $workshopUser = $seededUsers['workshop-manager'] ?? $admin;
        $cncUser = $seededUsers['cnc-operator'] ?? $admin;
        $pmUser = $seededUsers['project-manager'] ?? $admin;

        // 2. Customers
        $customersList = [
            [
                'code' => 'CUST-0001',
                'type' => 'company',
                'name' => 'شركة الأعمال المتقدمة للمقاولات',
                'company_name' => 'مجموعة الأعمال المتقدمة القابضة',
                'phone' => '0912345678',
                'phone_secondary' => '0183456789',
                'email' => 'info@advanced-corp.com',
                'address' => 'شارع الستين، تقاطع العمارة',
                'city' => 'الخرطوم',
                'cr_number' => '1010123456',
                'vat_number' => '300012345600003',
                'credit_limit' => 750000.00,
                'credit_period_days' => 45,
                'category' => 'corporate',
                'is_active' => true,
                'notes' => 'عميل شركات رئيسي لمشاريع الديكور والمقاولات',
                'branch_id' => $mainBranch->id,
            ],
            [
                'code' => 'CUST-0002',
                'type' => 'company',
                'name' => 'فندق النيل الأزرق الفاخر',
                'company_name' => 'شركة الفنادق والمنتجعات',
                'phone' => '0922334455',
                'email' => 'procurement@bluenilehotel.com',
                'address' => 'شارع النيل، المنيقر',
                'city' => 'الخرطوم',
                'cr_number' => '1010654321',
                'vat_number' => '300098765400003',
                'credit_limit' => 1000000.00,
                'credit_period_days' => 60,
                'category' => 'vip',
                'is_active' => true,
                'notes' => 'تأثيث غرف وأجنحة الفندق واللافتات الضوئية',
                'branch_id' => $mainBranch->id,
            ],
            [
                'code' => 'CUST-0003',
                'type' => 'company',
                'name' => 'مجموعة المطاعم الحديثة',
                'company_name' => 'شركة الأغذية والمطاعم المحدودة',
                'phone' => '0933445566',
                'email' => 'projects@modernrest.com',
                'address' => 'شارع المطار، حي الرياض',
                'city' => 'الخرطوم',
                'cr_number' => '1010888999',
                'vat_number' => '300088899900003',
                'credit_limit' => 300000.00,
                'credit_period_days' => 30,
                'category' => 'corporate',
                'is_active' => true,
                'notes' => 'ديكورات وتصنيع كاونترات واجهات مطاعم',
                'branch_id' => $mainBranch->id,
            ],
            [
                'code' => 'CUST-0004',
                'type' => 'individual',
                'name' => 'د. خالد عبد الرحمن',
                'phone' => '0944556677',
                'email' => 'dr.khaled@example.com',
                'address' => 'حي المنشية، برج الياسمين',
                'city' => 'الخرطوم',
                'credit_limit' => 100000.00,
                'credit_period_days' => 15,
                'category' => 'vip',
                'is_active' => true,
                'notes' => 'تأثيث فيلا سكنية وتجهيز دريسنج روم',
                'branch_id' => $mainBranch->id,
            ],
            [
                'code' => 'CUST-0005',
                'type' => 'individual',
                'name' => 'أ. منى سليمان عثمان',
                'phone' => '0955667788',
                'email' => 'mona.osman@example.com',
                'address' => 'حي الملازمين',
                'city' => 'أم درمان',
                'credit_limit' => 50000.00,
                'credit_period_days' => 14,
                'category' => 'regular',
                'is_active' => true,
                'notes' => 'طقم مطبخ MDF الماني مقاس 6 أمتار',
                'branch_id' => $mainBranch->id,
            ],
            [
                'code' => 'CUST-0006',
                'type' => 'company',
                'name' => 'مستشفى الشفاء التخصصي',
                'company_name' => 'شركة الرعاية الطبية المحدودة',
                'phone' => '0966778899',
                'email' => 'maintenance@alshifa.sd',
                'address' => 'حي كافوري، مربع 6',
                'city' => 'بحري',
                'cr_number' => '1010333222',
                'vat_number' => '300033322200003',
                'credit_limit' => 500000.00,
                'credit_period_days' => 30,
                'category' => 'corporate',
                'is_active' => true,
                'notes' => 'لافتات أرشادية وكاونترات استقبال طبية',
                'branch_id' => $workshopBranch->id,
            ],
            [
                'code' => 'CUST-0007',
                'type' => 'company',
                'name' => 'معرض العالمية للأثاث والديكور',
                'company_name' => 'شركة العالمية للتجارة',
                'phone' => '0977889900',
                'email' => 'sales@alalamiya.sd',
                'address' => 'المنطقة الصناعية',
                'city' => 'أم درمان',
                'credit_limit' => 400000.00,
                'credit_period_days' => 30,
                'category' => 'regular',
                'is_active' => true,
                'notes' => 'قص وتفريغ ألواح خشبيات بالـ CNC لحسابهم',
                'branch_id' => $workshopBranch->id,
            ],
            [
                'code' => 'CUST-0008',
                'type' => 'individual',
                'name' => 'م. طارق المحجوب',
                'phone' => '0988990011',
                'email' => 'tareq.mahjoub@example.com',
                'address' => 'حي المعمورة',
                'city' => 'الخرطوم',
                'credit_limit' => 80000.00,
                'credit_period_days' => 15,
                'category' => 'regular',
                'is_active' => true,
                'notes' => 'مكتبة منزلية وقواطع خشبية CNC بارتفاع 3 أمتار',
                'branch_id' => $mainBranch->id,
            ],
        ];

        $seededCustomers = [];
        foreach ($customersList as $c) {
            $cust = Customer::firstOrCreate(['code' => $c['code']], $c);
            $seededCustomers[] = $cust;
        }

        // 3. Suppliers
        $suppliersList = [
            [
                'code' => 'SUPP-0001',
                'name' => 'الشركة الوطنية لتجارة الأخشاب',
                'company_name' => 'الوطنية للأخشاب والملامين',
                'contact_person' => 'المهندس ياسين إبراهيم',
                'phone' => '0911002233',
                'email' => 'sales@nationalwood.sd',
                'address' => 'المنطقة الصناعية كوبر',
                'city' => 'بحري',
                'cr_number' => '2020111222',
                'vat_number' => '310011122200003',
                'services_provided' => 'أخشاب MDF، ألواح ملامين، خشب زان وسويدي، مفصلات أثاث',
                'rating' => 5,
                'is_active' => true,
                'branch_id' => $mainBranch->id,
            ],
            [
                'code' => 'SUPP-0002',
                'name' => 'مصنع الخليج للاكريليك والبلاستيك',
                'company_name' => 'شركة الخليج للصناعات البلاستيكية',
                'contact_person' => 'أ. حسام فؤاد',
                'phone' => '0922113344',
                'email' => 'info@gulfacrylic.com',
                'address' => 'شارع المصفاة',
                'city' => 'بحري',
                'cr_number' => '2020333444',
                'vat_number' => '310033344400003',
                'services_provided' => 'ألواح أكريليك شفاف ومظلم، لوحات فوم بورد، إكسسوارات إضاءة LED',
                'rating' => 4,
                'is_active' => true,
                'branch_id' => $workshopBranch->id,
            ],
        ];

        $seededSuppliers = [];
        foreach ($suppliersList as $s) {
            $supp = Supplier::firstOrCreate(['code' => $s['code']], $s);
            $seededSuppliers[] = $supp;
        }

        // 4. Standard Services
        $servicesCatalog = [
            [
                'code' => 'SRV-0001',
                'name_ar' => 'قص وتفريغ أخشاب CNC',
                'name_en' => 'CNC Wood Cutting & Engraving',
                'service_type' => 'cnc_cutting',
                'default_price' => 2500.00,
                'unit_of_measure' => 'm2',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'قص أخشاب MDF وألواح الملامين بالـ CNC الدقيقة',
            ],
            [
                'code' => 'SRV-0002',
                'name_ar' => 'تصنيع وتجميع خزائن ومطابخ MDF',
                'name_en' => 'Custom Cabinet Fabrication',
                'service_type' => 'furniture_manufacturing',
                'default_price' => 18000.00,
                'unit_of_measure' => 'm',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'تصنيع وتجميع الخزائن والدواليب والمطابخ',
            ],
            [
                'code' => 'SRV-0003',
                'name_ar' => 'تصنيع لوحات وحروف 3D بارزة ضوئية',
                'name_en' => 'Illuminated 3D Signage Fabrication',
                'service_type' => 'signage',
                'default_price' => 4500.00,
                'unit_of_measure' => 'm2',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'تصنيع الحروف البارزة من الأكريليك والزنكور',
            ],
        ];

        $seededServices = [];
        foreach ($servicesCatalog as $srv) {
            $sObj = Service::firstOrCreate(['code' => $srv['code']], $srv);
            $seededServices[] = $sObj;
        }

        // 5. Inventory Items & Warehouses
        $whMain = Warehouse::firstOrCreate(['code' => 'WH-DEMO-MAIN'], [
            'name' => 'المخزن الرئيسي للأخشاب والأنماط',
            'branch_id' => $workshopBranch->id,
            'is_active' => true,
        ]);

        $whHardware = Warehouse::firstOrCreate(['code' => 'WH-DEMO-HW'], [
            'name' => 'مخزن الاكسسوارات والدهانات واللافتات',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
        ]);

        $catWood = ItemCategory::firstOrCreate(['code' => 'CAT-WOOD'], ['name' => 'ألوح الخشب والملامين']);
        $catAcrylic = ItemCategory::firstOrCreate(['code' => 'CAT-ACRYLIC'], ['name' => 'ألواح الأكريليك والفوم']);

        $itemsData = [
            [
                'item_code' => 'ITM-001',
                'category_id' => $catWood->id,
                'name' => 'ألواح MDF إسباني 18 ملم (122×244 سم)',
                'unit' => 'sheet',
                'is_sheet_material' => true,
                'sheet_dimensions' => '122x244',
                'min_stock_alert' => 20,
                'default_purchase_price' => 1250.00,
                'default_sale_price' => 1650.00,
            ],
            [
                'item_code' => 'ITM-002',
                'category_id' => $catWood->id,
                'name' => 'ألواح ملامين أبيض ألماني 18 ملم',
                'unit' => 'sheet',
                'is_sheet_material' => true,
                'sheet_dimensions' => '122x244',
                'min_stock_alert' => 25,
                'default_purchase_price' => 1400.00,
                'default_sale_price' => 1850.00,
            ],
            [
                'item_code' => 'ITM-003',
                'category_id' => $catAcrylic->id,
                'name' => 'ألواح أكريليك شفاف 3 ملم',
                'unit' => 'sheet',
                'is_sheet_material' => true,
                'sheet_dimensions' => '122x244',
                'min_stock_alert' => 15,
                'default_purchase_price' => 950.00,
                'default_sale_price' => 1350.00,
            ],
        ];

        $seededItems = [];
        foreach ($itemsData as $itm) {
            $itemObj = InventoryItem::firstOrCreate(['item_code' => $itm['item_code']], $itm);
            $seededItems[] = $itemObj;
        }

        InventoryScrap::firstOrCreate([
            'item_id' => $seededItems[0]->id,
            'warehouse_id' => $whMain->id,
            'dimensions' => '140cm x 80cm',
        ], [
            'quantity' => 12,
            'status' => 'available',
            'notes' => 'متبقي قطوعات مشروع الفندق صالحة لكاونترات صغيرة',
        ]);

        // 6. Cashboxes & Shifts
        $mainCashbox = Cashbox::where('code', 'CB-0001')->first() ?: Cashbox::create([
            'code' => 'CB-0001',
            'name_ar' => 'الخزنة الرئيسية (مقر الشركة)',
            'name_en' => 'Main Headquarter Cashbox',
            'branch_id' => $mainBranch->id,
            'opening_balance' => 150000.00,
            'current_balance' => 345000.00,
            'is_active' => true,
        ]);

        $salesCashbox = Cashbox::where('code', 'CB-0002')->first() ?: Cashbox::create([
            'code' => 'CB-0002',
            'name_ar' => 'صندوق صالة العرض والمبيعات',
            'name_en' => 'Showroom Sales Cashbox',
            'branch_id' => $mainBranch->id,
            'opening_balance' => 20000.00,
            'current_balance' => 85000.00,
            'is_active' => true,
        ]);

        CashboxShift::create([
            'cashbox_id' => $salesCashbox->id,
            'user_id' => $salesUser->id,
            'opened_at' => Carbon::now()->subHours(6),
            'closed_at' => null,
            'opening_balance' => 20000.00,
            'expected_closing_balance' => 65000.00,
            'actual_closing_balance' => null,
            'difference_amount' => 0,
            'status' => 'open',
            'notes' => 'وردية المبيعات الصباحية اليومية',
        ]);

        CashboxShift::create([
            'cashbox_id' => $mainCashbox->id,
            'user_id' => $accountantUser->id,
            'opened_at' => Carbon::now()->subDays(1)->setHour(8),
            'closed_at' => Carbon::now()->subDays(1)->setHour(18),
            'opening_balance' => 150000.00,
            'expected_closing_balance' => 290000.00,
            'actual_closing_balance' => 290000.00,
            'difference_amount' => 0,
            'status' => 'closed',
            'notes' => 'تم إغلاق وتدقيق حسابات الخزنة بنجاح',
        ]);

        // 6.5. Seed Quotations
        $now = Carbon::now();
        $dateM5 = $now->copy()->subMonths(5);
        $cust1 = $seededCustomers[0];
        $cust2 = $seededCustomers[1];

        $q1 = Quotation::create([
            'quotation_number' => 'OFFER-2026-00001',
            'customer_id' => $cust1->id,
            'branch_id' => $mainBranch->id,
            'status' => 'accepted',
            'is_approved' => true,
            'approved_by' => $admin->id,
            'approved_at' => $dateM5->copy()->subDays(5),
            'issue_date' => $dateM5->copy()->subDays(10),
            'expiry_date' => $dateM5->copy()->addDays(20),
            'subtotal' => 150000.00,
            'discount_amount' => 5000.00,
            'tax_amount' => 21750.00,
            'total_amount' => 166750.00,
            'notes' => 'عرض سعر توريد وتصنيع قواطع وديكورات مقر شركة الأعمال المتقدمة',
            'terms_conditions' => 'الدفع 50% مقدم و 50% عند التسليم النهائي',
            'created_by' => $salesUser->id,
        ]);

        QuotationItem::create([
            'quotation_id' => $q1->id,
            'service_id' => $seededServices[1]->id,
            'item_name' => 'تصنيع وتثبيت خزائن ومكاتب إدارية MDF',
            'description' => 'تصنيع وتثبيت خزائن ومكاتب إدارية MDF إسباني فاخر',
            'quantity' => 10,
            'unit_of_measure' => 'متر طولي',
            'unit_price' => 15000.00,
            'discount_amount' => 5000.00,
            'tax_percent' => 15.00,
            'tax_amount' => 21750.00,
            'subtotal' => 145000.00,
            'total' => 166750.00,
            'sort_order' => 1,
        ]);

        $q2 = Quotation::create([
            'quotation_number' => 'OFFER-2026-00002',
            'customer_id' => $cust2->id,
            'branch_id' => $workshopBranch->id,
            'status' => 'sent',
            'is_approved' => false,
            'issue_date' => Carbon::now()->subDays(3),
            'expiry_date' => Carbon::now()->addDays(27),
            'subtotal' => 45000.00,
            'discount_amount' => 0.00,
            'tax_amount' => 6750.00,
            'total_amount' => 51750.00,
            'notes' => 'عرض سعر تصنيع لافتة كلادينج مضاءة بشعار الألومنيوم',
            'terms_conditions' => 'ساري لمدة 30 يوم من تاريخ الصدور',
            'created_by' => $salesUser->id,
        ]);

        QuotationItem::create([
            'quotation_id' => $q2->id,
            'service_id' => $seededServices[0]->id,
            'item_name' => 'تصنيع لافتة كلادينج مضاءة',
            'description' => 'تصنيع لافتة كلادينج 3D مضاءة LED عالية الجودة',
            'quantity' => 1,
            'unit_of_measure' => 'متر مربع',
            'unit_price' => 45000.00,
            'discount_amount' => 0.00,
            'tax_percent' => 15.00,
            'tax_amount' => 6750.00,
            'subtotal' => 45000.00,
            'total' => 51750.00,
            'sort_order' => 1,
        ]);

        $inv1 = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'customer_id' => $cust1->id,
            'branch_id' => $mainBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $dateM5->copy()->startOfMonth(),
            'due_date' => $dateM5->copy()->startOfMonth()->addDays(30),
            'subtotal' => 150000.00,
            'discount_amount' => 5000.00,
            'tax_amount' => 21750.00,
            'total_amount' => 166750.00,
            'status' => 'paid',
            'notes' => 'فاتورة توريد وتصنيع قواطع وديكورات مقر شركة الأعمال المتقدمة',
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'service_id' => $seededServices[1]->id,
            'item_name' => 'تصنيع وتثبيت خزائن ومكاتب إدارية MDF',
            'description' => 'تصنيع وتثبيت خزائن ومكاتب إدارية MDF إسباني فاخر',
            'quantity' => 8,
            'unit_of_measure' => 'm',
            'unit_price' => 18000.00,
            'subtotal' => 144000.00,
            'discount_amount' => 4000.00,
            'tax_percent' => 15.00,
            'tax_amount' => 21000.00,
            'total' => 161000.00,
        ]);

        $pv1 = PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0001',
            'type' => 'receipt',
            'customer_id' => $cust1->id,
            'invoice_id' => $inv1->id,
            'cashbox_id' => $mainCashbox->id,
            'created_by' => $accountantUser->id,
            'payment_date' => $dateM5->copy()->startOfMonth()->addDays(5),
            'amount' => 166750.00,
            'notes' => 'تحصيل كامل قيمة الفاتورة عبر تحويل بنكي',
            'status' => 'completed',
        ]);

        PaymentVoucherLine::create([
            'payment_voucher_id' => $pv1->id,
            'payment_method' => 'bank_transfer',
            'amount' => 166750.00,
            'reference_number' => 'TRF-99881122',
            'notes' => 'بنك الخرطوم',
        ]);

        $dateM4 = $now->copy()->subMonths(4);
        $inv2 = Invoice::create([
            'invoice_number' => 'INV-2026-0002',
            'customer_id' => $cust2->id,
            'branch_id' => $mainBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $dateM4->copy()->addDays(2),
            'due_date' => $dateM4->copy()->addDays(32),
            'subtotal' => 240000.00,
            'discount_amount' => 10000.00,
            'tax_amount' => 34500.00,
            'total_amount' => 264500.00,
            'status' => 'paid',
            'notes' => 'تأثيث وتجهيز 15 جناحاً في فندق النيل الأزرق',
        ]);

        $pv2 = PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0002',
            'type' => 'receipt',
            'customer_id' => $cust2->id,
            'invoice_id' => $inv2->id,
            'cashbox_id' => $mainCashbox->id,
            'created_by' => $accountantUser->id,
            'payment_date' => $dateM4->copy()->addDays(10),
            'amount' => 264500.00,
            'notes' => 'سداد بشيك بنكي مصدق تحصيل مباشر',
            'status' => 'completed',
        ]);

        PaymentVoucherLine::create([
            'payment_voucher_id' => $pv2->id,
            'payment_method' => 'cheque',
            'amount' => 264500.00,
            'reference_number' => 'CHQ-889900',
            'notes' => 'شيك بنك الخرطوم',
        ]);

        Cheque::create([
            'payment_voucher_id' => $pv2->id,
            'cheque_number' => 'CHQ-889900',
            'bank_name' => 'بنك الخرطوم',
            'drawer_name' => 'شركة الفنادق والمنتجعات',
            'amount' => 264500.00,
            'issue_date' => $dateM4->copy()->addDays(8),
            'due_date' => $dateM4->copy()->addDays(10),
            'status' => 'collected',
            'notes' => 'تم مقاصة الشيك وإيداع المبلغ بالخزنة',
            'created_by' => $accountantUser->id,
        ]);

        $dateM3 = $now->copy()->subMonths(3);
        $cust3 = $seededCustomers[2];
        $inv3 = Invoice::create([
            'invoice_number' => 'INV-2026-0003',
            'customer_id' => $cust3->id,
            'branch_id' => $mainBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $dateM3->copy()->addDays(5),
            'due_date' => $dateM3->copy()->addDays(35),
            'subtotal' => 95000.00,
            'discount_amount' => 3000.00,
            'tax_amount' => 13800.00,
            'total_amount' => 105800.00,
            'status' => 'partially_paid',
            'notes' => 'تصنيع كاونترات واجهات لفرع مطاعم الحديثة',
        ]);

        $pv3 = PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0003',
            'type' => 'receipt',
            'customer_id' => $cust3->id,
            'invoice_id' => $inv3->id,
            'cashbox_id' => $salesCashbox->id,
            'created_by' => $salesUser->id,
            'payment_date' => $dateM3->copy()->addDays(6),
            'amount' => 50000.00,
            'notes' => 'دفعة مقدماً نقداً بصندوق المبيعات',
            'status' => 'completed',
        ]);

        PaymentVoucherLine::create([
            'payment_voucher_id' => $pv3->id,
            'payment_method' => 'cash',
            'amount' => 50000.00,
            'reference_number' => 'CASH-0012',
        ]);

        // 8. Work Orders
        $wo1 = WorkOrder::create([
            'work_order_number' => 'WO-2026-001',
            'customer_id' => $cust1->id,
            'branch_id' => $workshopBranch->id,
            'invoice_id' => $inv1->id,
            'assigned_to' => $cncUser->id,
            'assigned_by' => $workshopUser->id,
            'sheet_count' => 50,
            'sheet_type' => 'MDF إسباني 18 ملم',
            'dimensions' => '122cm x 244cm',
            'thickness' => '18mm',
            'due_date' => $now->copy()->addDays(3),
            'status' => 'in_progress',
            'priority' => 'high',
            'good_pieces' => 32,
            'waste_pieces' => 1,
            'notes' => 'المشروع يحتاج دقة عالية في التفريغ الداخلي',
            'created_by' => $workshopUser->id,
        ]);

        WorkOrderAuthorization::create([
            'work_order_id' => $wo1->id,
            'authorized_by' => $workshopUser->id,
            'authorized_at' => $now->copy()->subDays(1),
            'paid_amount' => 166750.00,
            'remaining_balance' => 0.00,
            'is_override' => false,
            'notes' => 'تم الفحص والتصريح بالبدء فوراً',
        ]);

        WorkOrderTimeLog::create([
            'work_order_id' => $wo1->id,
            'user_id' => $cncUser->id,
            'action' => 'start',
            'logged_at' => $now->copy()->subHours(4),
            'notes' => 'بدء تشغيل ماكينة CNC',
        ]);

        $wo2 = WorkOrder::create([
            'work_order_number' => 'WO-2026-002',
            'customer_id' => $cust2->id,
            'branch_id' => $workshopBranch->id,
            'invoice_id' => $inv2->id,
            'assigned_to' => $cncUser->id,
            'assigned_by' => $workshopUser->id,
            'sheet_count' => 80,
            'sheet_type' => 'ملامين ألماني أبيض',
            'dimensions' => '122cm x 244cm',
            'thickness' => '18mm',
            'due_date' => $now->copy()->subDays(2),
            'status' => 'completed',
            'priority' => 'urgent',
            'good_pieces' => 80,
            'waste_pieces' => 0,
            'notes' => 'تم إنجاز كامل الكمية المطلوبة وتسليمها',
            'created_by' => $workshopUser->id,
        ]);

        // 9. Site Surveys, Contracts & Projects
        $survey = SiteSurvey::create([
            'survey_number' => 'SRV-2026-001',
            'customer_id' => $cust1->id,
            'site_address' => 'مقر شركة الأعمال المتقدمة - شارع الستين',
            'dimensions_data' => 'المساحة الكلية 450 م2، الارتفاع 3.20 م',
            'notes' => 'تم رفع كافة المقاسات واعتماد المخططات الأولية',
            'assigned_to' => $pmUser->id,
            'survey_date' => $dateM3->copy()->startOfMonth(),
            'status' => 'completed',
            'created_by' => $pmUser->id,
        ]);

        $contract = Contract::create([
            'contract_number' => 'CNT-2026-001',
            'customer_id' => $cust1->id,
            'branch_id' => $mainBranch->id,
            'scope_of_work' => 'عقد المقاولة والديكور الداخلي لبرج الشركات وتأثيث المكاتب',
            'total_amount' => 500000.00,
            'discount_amount' => 0.00,
            'tax_amount' => 75000.00,
            'net_amount' => 575000.00,
            'start_date' => $dateM3->copy()->addDays(5),
            'end_date' => $now->copy()->addDays(45),
            'status' => 'active',
            'is_approved' => true,
            'approved_by' => $admin->id,
            'approved_at' => $dateM3->copy()->addDays(5),
            'created_by' => $pmUser->id,
        ]);

        ContractPaymentTerm::create([
            'contract_id' => $contract->id,
            'milestone_name' => 'الدفعة المقدمة عند التوقيع',
            'due_date' => $dateM3->copy()->addDays(5),
            'amount_type' => 'percentage',
            'value' => 40.00,
            'calculated_amount' => 230000.00,
            'paid_amount' => 230000.00,
            'status' => 'paid',
        ]);

        $project = Project::create([
            'project_number' => 'PRJ-2026-001',
            'name' => 'مشروع المقاولات والديكور لبرج الأعمال المتقدمة',
            'contract_id' => $contract->id,
            'customer_id' => $cust1->id,
            'branch_id' => $mainBranch->id,
            'manager_id' => $pmUser->id,
            'start_date' => $dateM3->copy()->addDays(6),
            'expected_end_date' => $now->copy()->addDays(40),
            'budget' => 500000.00,
            'completion_percentage' => 75.00,
            'status' => 'in_progress',
            'notes' => 'نسبة الإنجاز الفعلية بالموقع تسير وفق الجدول الزمني',
            'created_by' => $pmUser->id,
        ]);

        ProjectStage::create([
            'project_id' => $project->id,
            'name' => 'المعاينة وإعداد المخططات',
            'weight_percentage' => 15.00,
            'completion_percentage' => 100.00,
            'status' => 'completed',
        ]);

        ProjectStage::create([
            'project_id' => $project->id,
            'name' => 'تصنيع القطوعات والأثاث بالورشة',
            'weight_percentage' => 45.00,
            'completion_percentage' => 90.00,
            'status' => 'in_progress',
        ]);

        ProjectExpense::create([
            'project_id' => $project->id,
            'type' => 'material',
            'description' => 'مشتريات أخشاب ملامين وأكريليك ومفصلات للمشروع',
            'amount' => 185000.00,
            'expense_date' => $dateM3->copy()->addDays(10),
            'created_by' => $pmUser->id,
        ]);

        SignageOrder::create([
            'order_number' => 'SGN-2026-001',
            'customer_id' => $cust1->id,
            'project_id' => $project->id,
            'dimensions' => '6.00m x 1.20m',
            'design_approved' => true,
            'design_approved_at' => $now->copy()->subDays(10),
            'design_approved_by' => $salesUser->id,
            'manufacturing_status' => 'in_progress',
            'installation_status' => 'pending',
            'status' => 'manufacturing',
            'created_by' => $salesUser->id,
        ]);

        // 10. Accounting & General Ledger Double-Entry Seeds
        $period = FiscalPeriod::firstOrCreate(['period_name' => 'FP-2026'], [
            'start_date' => '2026-02-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
        ]);

        $ccMain = CostCenter::firstOrCreate(['code' => 'CC-MAIN'], [
            'name' => 'مركز تكلفة الفرع الرئيسي',
            'branch_id' => $mainBranch->id,
        ]);

        $accAssets = Account::where('code', '111101')->first() ?: Account::firstOrCreate(['code' => '111101'], [
            'name' => 'الخزينة الرئيسية',
            'level' => 5,
            'type' => 'asset',
            'nature' => 'debit',
            'is_selectable' => true,
            'is_active' => true,
        ]);

        $accAR = Account::where('code', '112101')->first() ?: Account::firstOrCreate(['code' => '112101'], [
            'name' => 'حساب العملاء التجاريين - عام',
            'level' => 5,
            'type' => 'asset',
            'nature' => 'debit',
            'is_selectable' => true,
            'is_active' => true,
        ]);

        $accRevenue = Account::where('code', '411101')->first() ?: Account::firstOrCreate(['code' => '411101'], [
            'name' => 'حـ/ إيرادات مبيعات الجملة',
            'level' => 5,
            'type' => 'revenue',
            'nature' => 'credit',
            'is_selectable' => true,
            'is_active' => true,
        ]);

        $accVat = Account::where('code', '212101')->first() ?: Account::firstOrCreate(['code' => '212101'], [
            'name' => 'حـ/ ضريبة القيمة المضافة (15%)',
            'level' => 5,
            'type' => 'liability',
            'nature' => 'credit',
            'is_selectable' => true,
            'is_active' => true,
        ]);

        $je = JournalEntry::create([
            'entry_number' => 'JE-2026-0001',
            'fiscal_period_id' => $period->id,
            'entry_date' => $now->copy()->subDays(2),
            'reference_type' => 'Invoice',
            'reference_id' => $inv1->id,
            'description' => 'اثبات استحقاق مبيعات واجهات أكريليك ولائحة ضوئية',
            'status' => 'posted',
            'posted_by' => $accountantUser->id,
            'posted_at' => $now->copy()->subDays(2),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je->id,
            'account_id' => $accAR->id,
            'cost_center_id' => $ccMain->id,
            'debit' => 124200.00,
            'credit' => 0.00,
            'description' => 'استحقاق على عميل شركة الأعمال المتقدمة',
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je->id,
            'account_id' => $accRevenue->id,
            'cost_center_id' => $ccMain->id,
            'debit' => 0.00,
            'credit' => 108000.00,
            'description' => 'إيراد خدمات واجهات ولافتات ضوئية',
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je->id,
            'account_id' => $accVat->id,
            'cost_center_id' => $ccMain->id,
            'debit' => 0.00,
            'credit' => 16200.00,
            'description' => 'ضريبة القيمة المضافة 15%',
        ]);

        // Costing Records for Work Orders
        CostRecord::create([
            'costable_type' => WorkOrder::class,
            'costable_id' => $wo1->id,
            'cost_type' => 'material',
            'estimated_cost' => 45000.00,
            'actual_cost' => 48200.00,
            'notes' => 'انحراف بسيط بسبب تلف لوح MDF أثناء القص',
        ]);

        CostRecord::create([
            'costable_type' => WorkOrder::class,
            'costable_id' => $wo1->id,
            'cost_type' => 'labor',
            'estimated_cost' => 12000.00,
            'actual_cost' => 11500.00,
            'notes' => 'تكلفة ساعات مشغل الـ CNC والتجميع',
        ]);

        // 10. Current Month Active Sales & Payments (Ensure Non-Zero Dashboard Collections & Revenue)
        $currentMonthDate = $now->copy()->startOfMonth()->addDays(2);
        
        // Current Month Invoice 1
        $invCurrent1 = Invoice::create([
            'invoice_number' => 'INV-2026-0004',
            'customer_id' => $cust1->id,
            'branch_id' => $mainBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $currentMonthDate,
            'due_date' => $currentMonthDate->copy()->addDays(30),
            'subtotal' => 380000.00,
            'discount_amount' => 10000.00,
            'tax_amount' => 55500.00,
            'total_amount' => 425500.00,
            'status' => 'paid',
            'notes' => 'مبيعات ديكورات وتصاميم الشهر الحالي',
        ]);

        $pvCurrent1 = PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0004',
            'type' => 'receipt',
            'customer_id' => $cust1->id,
            'invoice_id' => $invCurrent1->id,
            'cashbox_id' => $mainCashbox->id,
            'created_by' => $accountantUser->id,
            'payment_date' => $currentMonthDate->copy()->addDay(),
            'amount' => 425500.00,
            'notes' => 'سداد نقدي مباشر بخزينة البنك الرئيسي',
            'status' => 'completed',
        ]);

        PaymentVoucherLine::create([
            'payment_voucher_id' => $pvCurrent1->id,
            'payment_method' => 'bank_transfer',
            'amount' => 425500.00,
            'reference_number' => 'TRF-CURR-001',
            'notes' => 'تحصيل إلكتروني عبر بنكك',
        ]);

        // Current Month Invoice 2
        $currentMonthDate2 = $now->copy()->startOfMonth()->addDays(4);
        $invCurrent2 = Invoice::create([
            'invoice_number' => 'INV-2026-0005',
            'customer_id' => $cust2->id,
            'branch_id' => $mainBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $currentMonthDate2,
            'due_date' => $currentMonthDate2->copy()->addDays(30),
            'subtotal' => 520000.00,
            'discount_amount' => 20000.00,
            'tax_amount' => 75000.00,
            'total_amount' => 575000.00,
            'status' => 'paid',
            'notes' => 'طلب تجهيز لوحات واجهات الفنادق',
        ]);

        $pvCurrent2 = PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0005',
            'type' => 'receipt',
            'customer_id' => $cust2->id,
            'invoice_id' => $invCurrent2->id,
            'cashbox_id' => $salesCashbox->id,
            'created_by' => $salesUser->id,
            'payment_date' => $currentMonthDate2->copy()->addDay(),
            'amount' => 575000.00,
            'notes' => 'تحصيل مباشر بمكتب المبيعات',
            'status' => 'completed',
        ]);

        PaymentVoucherLine::create([
            'payment_voucher_id' => $pvCurrent2->id,
            'payment_method' => 'cash',
            'amount' => 575000.00,
            'reference_number' => 'CASH-CURR-002',
        ]);

        // Transactions for Customers 4, 5, 6, 7 (معرض العالمية), and 8
        $cust4 = $seededCustomers[3];
        $cust5 = $seededCustomers[4];
        $cust6 = $seededCustomers[5];
        $cust7 = $seededCustomers[6]; // Customer ID 7: معرض العالمية للأثاث والديكور
        $cust8 = $seededCustomers[7];

        // Customer 4 Invoices & Payments
        $invCust4 = Invoice::create([
            'invoice_number' => 'INV-2026-0006',
            'customer_id' => $cust4->id,
            'branch_id' => $mainBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $now->copy()->subDays(10),
            'due_date' => $now->copy()->addDays(15),
            'subtotal' => 120000.00,
            'discount_amount' => 5000.00,
            'tax_amount' => 17250.00,
            'total_amount' => 132250.00,
            'status' => 'partially_paid',
            'notes' => 'تأثيث دريسنج روم وتصنيع خزائن',
        ]);

        PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0006',
            'type' => 'receipt',
            'customer_id' => $cust4->id,
            'invoice_id' => $invCust4->id,
            'cashbox_id' => $mainCashbox->id,
            'created_by' => $accountantUser->id,
            'payment_date' => $now->copy()->subDays(8),
            'amount' => 80000.00,
            'notes' => 'دفعة أولى نقداً',
            'status' => 'completed',
        ]);

        // Customer 5 Invoices & Payments
        $invCust5 = Invoice::create([
            'invoice_number' => 'INV-2026-0007',
            'customer_id' => $cust5->id,
            'branch_id' => $mainBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $now->copy()->subDays(12),
            'due_date' => $now->copy()->addDays(5),
            'subtotal' => 75000.00,
            'discount_amount' => 0.00,
            'tax_amount' => 11250.00,
            'total_amount' => 86250.00,
            'status' => 'paid',
            'notes' => 'طقم مطبخ MDF الماني 6 أمتار',
        ]);

        PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0007',
            'type' => 'receipt',
            'customer_id' => $cust5->id,
            'invoice_id' => $invCust5->id,
            'cashbox_id' => $salesCashbox->id,
            'created_by' => $salesUser->id,
            'payment_date' => $now->copy()->subDays(11),
            'amount' => 86250.00,
            'notes' => 'سداد كامل القيمة نقداً',
            'status' => 'completed',
        ]);

        // Customer 6 (Hospital) Invoices & Payments
        $invCust6 = Invoice::create([
            'invoice_number' => 'INV-2026-0008',
            'customer_id' => $cust6->id,
            'branch_id' => $workshopBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $now->copy()->subDays(15),
            'due_date' => $now->copy()->addDays(20),
            'subtotal' => 350000.00,
            'discount_amount' => 10000.00,
            'tax_amount' => 51000.00,
            'total_amount' => 391000.00,
            'status' => 'partially_paid',
            'notes' => 'كاونترات استقبال طبية ولافتات إرشادية',
        ]);

        PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0008',
            'type' => 'receipt',
            'customer_id' => $cust6->id,
            'invoice_id' => $invCust6->id,
            'cashbox_id' => $mainCashbox->id,
            'created_by' => $accountantUser->id,
            'payment_date' => $now->copy()->subDays(10),
            'amount' => 200000.00,
            'notes' => 'دفعة تحت الحساب تحويل بنكي',
            'status' => 'completed',
        ]);

        // Customer 7 (معرض العالمية للأثاث والديكور - Customer ID 7) Invoices & Payments
        $invCust7_1 = Invoice::create([
            'invoice_number' => 'INV-2026-0009',
            'customer_id' => $cust7->id,
            'branch_id' => $workshopBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $now->copy()->subDays(20),
            'due_date' => $now->copy()->addDays(10),
            'subtotal' => 450000.00,
            'discount_amount' => 15000.00,
            'tax_amount' => 65250.00,
            'total_amount' => 500250.00,
            'status' => 'paid',
            'notes' => 'طلب قص وتفريغ ألواح خشبية CNC لحساب المعرض',
        ]);

        PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0009',
            'type' => 'receipt',
            'customer_id' => $cust7->id,
            'invoice_id' => $invCust7_1->id,
            'cashbox_id' => $mainCashbox->id,
            'created_by' => $accountantUser->id,
            'payment_date' => $now->copy()->subDays(18),
            'amount' => 300000.00,
            'notes' => 'دفعة أولى بشيك تحويل بنكك',
            'status' => 'completed',
        ]);

        PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0010',
            'type' => 'receipt',
            'customer_id' => $cust7->id,
            'invoice_id' => $invCust7_1->id,
            'cashbox_id' => $salesCashbox->id,
            'created_by' => $salesUser->id,
            'payment_date' => $now->copy()->subDays(5),
            'amount' => 200250.00,
            'notes' => 'تصفية المتبقي نقداً',
            'status' => 'completed',
        ]);

        // Customer 8 Invoices & Payments
        $invCust8 = Invoice::create([
            'invoice_number' => 'INV-2026-0010',
            'customer_id' => $cust8->id,
            'branch_id' => $mainBranch->id,
            'created_by' => $salesUser->id,
            'issue_date' => $now->copy()->subDays(7),
            'due_date' => $now->copy()->addDays(20),
            'subtotal' => 95000.00,
            'discount_amount' => 3000.00,
            'tax_amount' => 13800.00,
            'total_amount' => 105800.00,
            'status' => 'partially_paid',
            'notes' => 'تصنيع مكتبة منزلية وقواطع CNC',
        ]);

        PaymentVoucher::create([
            'voucher_number' => 'REC-2026-0011',
            'type' => 'receipt',
            'customer_id' => $cust8->id,
            'invoice_id' => $invCust8->id,
            'cashbox_id' => $salesCashbox->id,
            'created_by' => $salesUser->id,
            'payment_date' => $now->copy()->subDays(4),
            'amount' => 60000.00,
            'notes' => 'دفعة مقدماً',
            'status' => 'completed',
        ]);

        // 11. Seed System Notifications
        $adminUserObj = User::where('email', 'admin@example.com')->first();
        if ($adminUserObj) {
            SystemNotification::create([
                'user_id' => $adminUserObj->id,
                'type' => 'sales_update',
                'priority' => 'high',
                'title' => 'مبيعات جديدة للشهر الحالي 📈',
                'message' => 'تم تسجيل سداد فاتورة بقيمة 575,000.00 SDG للعميل شركة الفنادق والمنتجعات بنجاح.',
                'action_url' => route('invoices.show', $invCurrent2->id),
                'is_read' => false,
                'deduplication_key' => 'sales_inv_575k',
            ]);

            SystemNotification::create([
                'user_id' => $adminUserObj->id,
                'type' => 'inventory_alert',
                'priority' => 'high',
                'title' => 'تنبيه مخزون جديد 📦',
                'message' => 'تم إضافة صنف جديد ودعم الوحدات المزدوجة بالكرتونة والفرادي.',
                'action_url' => route('inventory.index'),
                'is_read' => false,
                'deduplication_key' => 'inv_multi_unit_added',
            ]);

            SystemNotification::create([
                'user_id' => $adminUserObj->id,
                'type' => 'financial_alert',
                'priority' => 'normal',
                'title' => 'تم تحصيل شيك بنكي 💰',
                'message' => 'شيك بنك الخرطوم بقيمة 264,500.00 SDG تم مقاصته وتأكيده بالميزانية.',
                'action_url' => route('cheques.index'),
                'is_read' => true,
                'read_at' => $now->copy()->subHours(2),
                'deduplication_key' => 'cheque_collected_264k',
            ]);
        }
    }
}
