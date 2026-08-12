<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Service;
use App\Models\Unit;
use App\Models\ItemCategory;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Cashbox;
use App\Models\PaymentVoucher;
use App\Models\Cheque;
use App\Models\Account;
use App\Models\Contract;
use App\Models\Project;
use App\Models\WorkOrder;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemFullLiveDatabaseAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->first()
            ?: User::where('email', 'admin@a.a')->first()
            ?: User::first();
    }

    /**
     * Audit: Check that database is populated with actual seed records across all modules.
     */
    public function test_database_has_seeded_records_across_all_modules(): void
    {
        $this->assertGreaterThan(0, User::count(), 'Users table should have records');
        $this->assertGreaterThan(0, Branch::count(), 'Branches table should have records');
        $this->assertGreaterThan(0, Customer::count(), 'Customers table should have records');
        $this->assertGreaterThan(0, Supplier::count(), 'Suppliers table should have records');
        $this->assertGreaterThan(0, Service::count(), 'Services table should have records');
        $this->assertGreaterThan(0, Unit::count(), 'Units table should have records');
        $this->assertGreaterThan(0, ItemCategory::count(), 'Item categories table should have records');
        $this->assertGreaterThan(0, Warehouse::count(), 'Warehouses table should have records');
        $this->assertGreaterThan(0, InventoryItem::count(), 'Inventory items table should have records');
        $this->assertGreaterThan(0, Account::count(), 'Chart of accounts table should have records');
        $this->assertGreaterThan(0, Cashbox::count(), 'Cashboxes table should have records');
        $this->assertGreaterThan(0, Currency::count(), 'Currencies table should have records');
    }

    /**
     * Audit: Check that all system pages load successfully (HTTP 200 OK) for authenticated admin.
     */
    public function test_all_system_pages_render_successfully(): void
    {
        $routes = [
            '/ar/dashboard',
            '/ar/guide',
            '/ar/users',
            '/ar/users/create',
            '/ar/roles',
            '/ar/roles/matrix',
            '/ar/branches',
            '/ar/settings',
            '/ar/customers',
            '/ar/customers/create',
            '/ar/suppliers',
            '/ar/suppliers/create',
            '/ar/services',
            '/ar/units',
            '/ar/categories',
            '/ar/warehouses',
            '/ar/inventory',
            '/ar/inventory/create',
            '/ar/quotations',
            '/ar/quotations/create',
            '/ar/signage-orders',
            '/ar/surveys',
            '/ar/pos',
            '/ar/invoices',
            '/ar/invoices/create',
            '/ar/purchases',
            '/ar/purchases/create-invoice',
            '/ar/cashboxes',
            '/ar/payments',
            '/ar/payments/create',
            '/ar/cheques',
            '/ar/cheques/deposit-slip',
            '/ar/accounting',
            '/ar/contracts',
            '/ar/projects',
            '/ar/work-orders',
            '/ar/workshop-kiosk',
            '/ar/notifications',
            '/ar/reports',
            '/ar/reports/customer-statement',
            '/ar/reports/supplier-statement',
            '/ar/reports/sales',
            '/ar/reports/workshop',
            '/ar/reports/projects',
            '/ar/reports/financial',
            '/ar/reports/financial-comparison',
            '/ar/reports/profitable-items',
            '/ar/reports/inventory',
            '/ar/activity-logs',
            '/ar/currencies',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get($route);
            $response->assertStatus(200, "Route {$route} failed to return HTTP 200");
        }
    }

    /**
     * Audit: Check creating actual records in the database via form endpoints.
     */
    public function test_creating_actual_data_in_database(): void
    {
        // 1. Create a new Customer
        $customerData = [
            'type' => 'company',
            'name' => 'شركة الاختيار الذكي المحدودة',
            'company_name' => 'شركة الاختيار الذكي',
            'phone' => '0599988776',
            'email' => 'smart_choice_' . time() . '@example.com',
            'vat_number' => '300998877665543',
            'address' => 'الرياض - حي الملز',
            'credit_limit' => 50000.00,
            'credit_period_days' => 30,
            'category' => 'corporate',
            'is_active' => true,
        ];

        $resCustomer = $this->actingAs($this->admin)->post('/ar/customers', $customerData);
        $resCustomer->assertSessionHasNoErrors();
        $this->assertDatabaseHas('customers', ['name' => 'شركة الاختيار الذكي المحدودة']);

        // 2. Create a new Service
        $serviceData = [
            'code' => 'SRV-TEST-' . rand(100, 999),
            'name_ar' => 'خدمة تركيب واجهات كلادينج',
            'service_type' => 'outfitting',
            'unit_of_measure' => 'm2',
            'description' => 'تركيب وتثبيت واجهات كلادينج مع الضمان',
            'default_price' => 1200.00,
            'is_active' => true,
        ];
        $resService = $this->actingAs($this->admin)->post('/ar/services', $serviceData);
        $resService->assertSessionHasNoErrors();
        $this->assertDatabaseHas('services', ['name_ar' => 'خدمة تركيب واجهات كلادينج']);

        // 3. Create a new Cashbox
        $cashboxData = [
            'name_ar' => 'صندوق الطوارئ والصيانة',
            'branch_id' => Branch::first()->id,
            'opening_balance' => 15000.00,
            'is_active' => true,
        ];
        $resCashbox = $this->actingAs($this->admin)->post('/ar/cashboxes', $cashboxData);
        $resCashbox->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cashboxes', ['name_ar' => 'صندوق الطوارئ والصيانة']);
    }
}
