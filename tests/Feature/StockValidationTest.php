<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\InventoryItem;
use App\Models\Unit;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockValidationTest extends TestCase
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

    public function test_pos_prevents_sale_when_stock_is_insufficient()
    {
        $warehouse = Warehouse::first();
        $unit = Unit::first();
        $item = InventoryItem::create([
            'name' => 'صنف تجريبي للمخزون',
            'item_code' => 'TST-STK-01',
            'base_unit_id' => $unit->id,
            'conversion_factor' => 1,
            'cost_price' => 10,
            'retail_price' => 20,
        ]);

        // Set stock to 5 units
        WarehouseItem::create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item->id,
            'qty_in_base_units' => 5,
        ]);

        // Try selling 10 units in POS -> Should fail with 422
        $response = $this->postJson('/ar/pos', [
            'warehouse_id' => $warehouse->id,
            'payment_type' => 'cash',
            'items' => [
                [
                    'id' => $item->id,
                    'qty' => 10,
                    'unit_type' => 'base',
                    'price' => 20,
                ]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false
        ]);

        // Verify stock is still 5
        $whItem = WarehouseItem::where('warehouse_id', $warehouse->id)
            ->where('inventory_item_id', $item->id)
            ->first();
        $this->assertEquals(5, (float)$whItem->qty_in_base_units);
    }

    public function test_pos_allows_sale_up_to_available_stock_and_does_not_go_negative()
    {
        $warehouse = Warehouse::first();
        $unit = Unit::first();
        $item = InventoryItem::create([
            'name' => 'صنف تاني للمخزون',
            'item_code' => 'TST-STK-02',
            'base_unit_id' => $unit->id,
            'conversion_factor' => 1,
            'cost_price' => 10,
            'retail_price' => 20,
        ]);

        WarehouseItem::create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item->id,
            'qty_in_base_units' => 5,
        ]);

        // Sell exactly 5 units -> Should succeed and reduce stock to 0
        $response = $this->postJson('/ar/pos', [
            'warehouse_id' => $warehouse->id,
            'payment_type' => 'cash',
            'items' => [
                [
                    'id' => $item->id,
                    'qty' => 5,
                    'unit_type' => 'base',
                    'price' => 20,
                ]
            ]
        ]);

        $response->assertStatus(200);

        $whItem = WarehouseItem::where('warehouse_id', $warehouse->id)
            ->where('inventory_item_id', $item->id)
            ->first();
        $this->assertEquals(0, (float)$whItem->qty_in_base_units);
    }

    public function test_invoice_prevents_sale_when_stock_exceeded()
    {
        $warehouse = Warehouse::first();
        $customer = Customer::first();
        $unit = Unit::first();
        $item = InventoryItem::create([
            'name' => 'صنف فاتورة مبيعات',
            'item_code' => 'TST-INV-01',
            'base_unit_id' => $unit->id,
            'conversion_factor' => 1,
            'cost_price' => 15,
            'retail_price' => 25,
        ]);

        WarehouseItem::create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item->id,
            'qty_in_base_units' => 2,
        ]);

        // Try creating invoice with quantity 10 -> Should redirect back with error
        $response = $this->post('/ar/invoices', [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => date('Y-m-d'),
            'payment_type' => 'cash',
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'unit_id' => $unit->id,
                    'quantity' => 10,
                    'unit_price' => 25,
                ]
            ]
        ]);

        $response->assertSessionHas('error');

        $whItem = WarehouseItem::where('warehouse_id', $warehouse->id)
            ->where('inventory_item_id', $item->id)
            ->first();
        $this->assertEquals(2, (float)$whItem->qty_in_base_units);
    }
}
