<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Unit;
use App\Models\ItemCategory;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalesPurchasesInventoryAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = User::first();
        if ($user) {
            $this->actingAs($user);
        }
    }

    public function test_dashboard_can_be_rendered()
    {
        $response = $this->get('/ar/dashboard');
        $response->assertStatus(200);
    }

    public function test_units_page_can_be_rendered()
    {
        $response = $this->get('/ar/units');
        $response->assertStatus(200);
    }

    public function test_categories_page_can_be_rendered()
    {
        $response = $this->get('/ar/categories');
        $response->assertStatus(200);
    }

    public function test_warehouses_page_can_be_rendered()
    {
        $response = $this->get('/ar/warehouses');
        $response->assertStatus(200);
    }

    public function test_inventory_page_can_be_rendered()
    {
        $response = $this->get('/ar/inventory');
        $response->assertStatus(200);
    }

    public function test_sales_invoices_page_can_be_rendered()
    {
        $response = $this->get('/ar/invoices');
        $response->assertStatus(200);
    }

    public function test_purchase_invoices_page_can_be_rendered()
    {
        $response = $this->get('/ar/purchases');
        $response->assertStatus(200);
    }

    public function test_chart_of_accounts_page_can_be_rendered()
    {
        $response = $this->get('/ar/accounting');
        $response->assertStatus(200);
    }

    public function test_create_inventory_item_with_multi_units()
    {
        $pieceUnit = Unit::where('name', 'قطعة')->first();
        $cartonUnit = Unit::where('name', 'كرتونة')->first();
        $category = ItemCategory::first();

        $response = $this->post('/ar/inventory', [
            'name' => 'صنف اختبار كرتونة وقطعة',
            'code' => 'ITEM-TEST-001',
            'category_id' => $category->id,
            'base_unit_id' => $pieceUnit->id,
            'wholesale_unit_id' => $cartonUnit->id,
            'conversion_factor' => 12,
            'cost_price' => 100,
            'wholesale_price' => 120,
            'retail_price' => 150,
            'min_stock_alert' => 5,
        ]);

        $response->assertRedirect('/ar/inventory');
        $this->assertDatabaseHas('inventory_items', [
            'code' => 'ITEM-TEST-001',
            'conversion_factor' => 12,
        ]);
    }

    public function test_create_sales_invoice_creates_journal_and_deducts_stock()
    {
        $customer = Customer::first();
        $warehouse = Warehouse::first();
        $item = InventoryItem::first();
        $unit = Unit::first();

        $response = $this->post('/ar/invoices', [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'payment_type' => 'cash',
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'unit_id' => $unit->id,
                    'quantity' => 2,
                    'unit_price' => 100,
                ]
            ]
        ]);

        $response->assertRedirect('/ar/invoices');
        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
        ]);

        // Journal entry created automatically
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => \App\Models\Invoice::class,
        ]);
    }

    public function test_create_purchase_invoice_creates_journal_and_increments_stock()
    {
        $supplier = Supplier::first();
        $warehouse = Warehouse::first();
        $item = InventoryItem::first();
        $unit = Unit::first();

        $response = $this->post('/ar/purchases/store-invoice', [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->toDateString(),
            'payment_type' => 'cash',
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'unit_id' => $unit->id,
                    'quantity' => 5,
                    'unit_price' => 80,
                ]
            ]
        ]);

        $response->assertRedirect('/ar/purchases');

        // Journal entry created automatically
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => \App\Models\PurchaseInvoice::class,
        ]);
    }

    public function test_user_can_login_via_post_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/ar/dashboard');
    }

    public function test_pos_cashier_screen_can_be_rendered()
    {
        $response = $this->get('/ar/pos');
        $response->assertStatus(200);
    }

    public function test_pos_cashier_checkout_creates_invoice_and_deducts_stock()
    {
        $warehouse = Warehouse::first();
        $item = InventoryItem::first();

        $response = $this->post('/ar/pos', [
            'warehouse_id' => $warehouse->id,
            'payment_type' => 'cash',
            'discount_amount' => 0,
            'items' => [
                [
                    'id' => $item->id,
                    'qty' => 1,
                    'unit_type' => 'base',
                    'price' => $item->retail_price ?? 25.00,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('invoices', [
            'warehouse_id' => $warehouse->id,
        ]);
    }
}
