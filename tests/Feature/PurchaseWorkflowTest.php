<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Supplier $supplier;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->supplier = Supplier::first();
        $this->warehouse = Warehouse::create(['name' => 'المستودع الرئيسي', 'code' => 'WH-MAIN', 'is_active' => true]);
    }

    public function test_purchase_order_creation_and_goods_receipt(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/purchases/store-po', [
            'supplier_id' => $this->supplier->id,
            'total_amount' => 10000.00,
            'tax_amount' => 1500.00,
            'order_date' => '2026-08-01',
            'notes' => 'شراء 50 لوح MDF وشريط تكسية',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'net_amount' => 11500.00,
            'status' => 'issued',
        ]);

        $po = PurchaseOrder::latest()->first();

        // Receive Goods (GRN)
        $response = $this->post('/ar/purchases/orders/' . $po->id . '/receive-goods', [
            'warehouse_id' => $this->warehouse->id,
            'receipt_date' => '2026-08-02',
        ]);

        $response->assertRedirect();
        $po->refresh();

        $this->assertEquals('received', $po->status);
        $this->assertDatabaseHas('goods_receipts', [
            'purchase_order_id' => $po->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
    }
}
