<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\PaymentVoucher;
use App\Models\Branch;
use App\Models\Cashbox;
use App\Models\WorkOrder;
use App\Models\Project;
use App\Models\InventoryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->customer = Customer::first();
        $this->supplier = Supplier::first();
    }

    public function test_guest_user_is_redirected_to_login(): void
    {
        $response = $this->get('/ar/reports');
        $response->assertRedirect('/ar/login');
    }

    public function test_authorized_admin_can_access_reports_hub(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/reports');
        $response->assertStatus(200);
        $response->assertSee('مركز التقارير والاستعلامات');
    }

    public function test_customer_statement_ledger_calculation(): void
    {
        $branch = Branch::first();

        // Create an Invoice for customer (Debit = 1000)
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'customer_id' => $this->customer->id,
            'branch_id' => $branch->id,
            'created_by' => $this->admin->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'subtotal' => 1000.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'net_amount' => 1000.00,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'remaining_amount' => 1000.00,
            'status' => 'issued',
        ]);


        // Create a Payment Voucher for customer (Credit = 400)
        $cashbox = Cashbox::first();
        $voucher = PaymentVoucher::create([
            'voucher_number' => 'VCH-TEST-001',
            'type' => 'receipt',
            'customer_id' => $this->customer->id,
            'created_by' => $this->admin->id,
            'cashbox_id' => $cashbox?->id,
            'amount' => 400.00,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->get("/ar/reports/customer-statement?customer_id={$this->customer->id}");

        $response->assertStatus(200);
        $response->assertSee('INV-TEST-001');
        $response->assertSee('VCH-TEST-001');
        $response->assertSee('1,000.00');
        $response->assertSee('400.00');
        $response->assertSee('600.00'); // Ending balance = 1000 - 400 = 600
    }

    public function test_supplier_statement_ledger_calculation(): void
    {
        $branch = Branch::first();
        $cashbox = Cashbox::first();

        // Create a payment voucher to supplier (Debit = 500)
        $voucher = PaymentVoucher::create([
            'voucher_number' => 'VCH-SUPP-001',
            'type' => 'payment',
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->admin->id,
            'cashbox_id' => $cashbox?->id,
            'amount' => 500.00,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->get("/ar/reports/supplier-statement?supplier_id={$this->supplier->id}");

        $response->assertStatus(200);
        $response->assertSee('VCH-SUPP-001');
        $response->assertSee('500.00');
    }

    public function test_sales_report_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/reports/sales');
        $response->assertStatus(200);
        $response->assertSee('تقرير المبيعات التفصيلي');
    }

    public function test_workshop_report_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/reports/workshop');
        $response->assertStatus(200);
        $response->assertSee('تقرير إنتاج الورشة');
    }

    public function test_projects_report_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/reports/projects');
        $response->assertStatus(200);
        $response->assertViewHas('total_expenses', 185000.0);
        $response->assertViewHas('projects', function ($projects) {
            return (float) $projects->first()->total_expenses === 185000.0;
        });
        $response->assertSee('تقرير إنجاز المشاريع');
    }

    public function test_financial_report_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/reports/financial');
        $response->assertStatus(200);
        $response->assertSee('التقرير المالي العام');
    }

    public function test_inventory_report_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/reports/inventory');
        $response->assertStatus(200);
        $response->assertSee('تقرير حركة المخزون');
    }


    public function test_customer_statement_csv_export(): void
    {
        $response = $this->actingAs($this->admin)->get("/ar/reports/customer-statement?customer_id={$this->customer->id}&export=csv");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_inventory_report_csv_export(): void
    {
        $response = $this->actingAs($this->admin)->get("/ar/reports/inventory?export=csv");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_customer_statement_print_view(): void
    {
        $response = $this->actingAs($this->admin)->get("/ar/reports/customer-statement?customer_id={$this->customer->id}&export=print");
        $response->assertStatus(200);
        $response->assertSee('تاريخ الطباعة');
        $response->assertSee('توقيع المحاسب');
    }
}
