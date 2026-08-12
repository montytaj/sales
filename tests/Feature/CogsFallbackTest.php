<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CogsFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_effective_cost_price_fallback_when_cost_price_is_zero(): void
    {
        $unit = Unit::first();
        $item = InventoryItem::create([
            'name' => 'صنف بدون سعر تكلفة مسبق',
            'item_code' => 'ITEM-COGS-TEST',
            'cost_price' => 0.00,
            'default_purchase_price' => 40.00,
            'retail_price' => 100.00,
            'base_unit_id' => $unit->id,
            'is_active' => true,
        ]);


        // 1. Should fallback to default_purchase_price (40.00)
        $this->assertEquals(40.00, $item->getEffectiveCostPrice());

        // 2. If default_purchase_price is 0, fallback to purchase invoice history or ratio estimate from selling price
        $item->update(['default_purchase_price' => 0.00]);
        $supplier = Supplier::first();
        $warehouse = Warehouse::first();

        $pInvoice = PurchaseInvoice::create([
            'invoice_number' => 'PINV-COST-001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now()->toDateString(),
            'net_amount' => 500,
            'status' => 'paid',
        ]);

        PurchaseInvoiceItem::create([
            'purchase_invoice_id' => $pInvoice->id,
            'inventory_item_id' => $item->id,
            'unit_id' => $unit->id,
            'quantity' => 10,
            'qty_in_base_units' => 10,
            'unit_price' => 45.00,
            'subtotal' => 450,
            'tax_amount' => 0,
            'total' => 450,
        ]);

        // Should return 45.00 from purchase invoice history
        $this->assertEquals(45.00, $item->getEffectiveCostPrice());

        // 3. If no purchase history exists, fallback to selling price ratio estimate (75% of 100 = 75.00)
        PurchaseInvoiceItem::where('inventory_item_id', $item->id)->delete();
        $this->assertEquals(75.00, $item->getEffectiveCostPrice());
    }
}
