<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Warehouse $warehouse;
    protected InventoryItem $item;
    protected Unit $unit;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::first();
        $this->warehouse = Warehouse::first();
        $this->item = InventoryItem::first();
        $this->unit = Unit::first();
        $this->customer = Customer::first();
        $this->customer->update(['credit_limit' => 1000000.00]);
    }

    public function test_invoice_stock_deduction_with_row_locking_handles_limits_correctly(): void
    {
        // Set stock to 10
        WarehouseItem::updateOrCreate(
            ['warehouse_id' => $this->warehouse->id, 'inventory_item_id' => $this->item->id],
            ['qty_in_base_units' => 10]
        );

        $invoiceData1 = [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'issue_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'items' => [
                [
                    'inventory_item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 8,
                    'unit_price' => 50,
                ]
            ]
        ];

        // First sale: 8 units -> stock left = 2
        $response1 = $this->actingAs($this->admin)->post('/ar/invoices', $invoiceData1);
        $response1->assertSessionHasNoErrors();
        $response1->assertRedirect('/ar/invoices');

        $whItem = WarehouseItem::where('warehouse_id', $this->warehouse->id)
            ->where('inventory_item_id', $this->item->id)
            ->first();
        $this->assertEquals(2, (float)$whItem->qty_in_base_units);

        // Second sale: requesting 5 units (stock is 2) -> Should fail cleanly with error session
        $invoiceData2 = [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'issue_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'items' => [
                [
                    'inventory_item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 5,
                    'unit_price' => 50,
                ]
            ]
        ];

        $response2 = $this->actingAs($this->admin)->post('/ar/invoices', $invoiceData2);
        $response2->assertSessionHas('error');

        // Stock should remain 2
        $whItem->refresh();
        $this->assertEquals(2, (float)$whItem->qty_in_base_units);
    }
}
