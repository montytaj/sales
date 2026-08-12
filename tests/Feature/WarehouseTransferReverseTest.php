<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseTransferReverseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Warehouse $warehouse1;
    protected Warehouse $warehouse2;
    protected InventoryItem $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::first();
        $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'delete-warehouse-transfers', 'guard_name' => 'web']);
        $this->admin->givePermissionTo($perm);

        
        $warehouses = Warehouse::all();
        $this->warehouse1 = $warehouses->first();
        $this->warehouse2 = $warehouses->last();

        $this->item = InventoryItem::first();

        // Initial stock: Warehouse 1 = 100, Warehouse 2 = 0
        WarehouseItem::updateOrCreate(
            ['warehouse_id' => $this->warehouse1->id, 'inventory_item_id' => $this->item->id],
            ['qty_in_base_units' => 100]
        );

        WarehouseItem::updateOrCreate(
            ['warehouse_id' => $this->warehouse2->id, 'inventory_item_id' => $this->item->id],
            ['qty_in_base_units' => 0]
        );
    }

    public function test_reversing_completed_warehouse_transfer(): void
    {
        // 1. Create and complete a transfer of 20 units from W1 to W2
        $transfer = WarehouseTransfer::create([
            'transfer_number' => 'TR-TEST-001',
            'from_warehouse_id' => $this->warehouse1->id,
            'to_warehouse_id' => $this->warehouse2->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        WarehouseTransferItem::create([
            'warehouse_transfer_id' => $transfer->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 20,
        ]);

        $inventoryService = app(InventoryService::class);
        $inventoryService->executeTransfer($transfer);

        $transfer->refresh();
        $this->assertEquals('completed', $transfer->status);

        // Check stock after completion: W1 = 80, W2 = 20
        $w1Stock = $inventoryService->getStockQuantity($this->item, $this->warehouse1);
        $w2Stock = $inventoryService->getStockQuantity($this->item, $this->warehouse2);
        $this->assertEquals(80.0, $w1Stock);
        $this->assertEquals(20.0, $w2Stock);

        // 2. Reverse completed transfer
        $response = $this->actingAs($this->admin)->post("/ar/warehouse-transfers/{$transfer->id}/cancel");
        $response->assertSessionHas('success');


        $transfer->refresh();
        $this->assertEquals('cancelled', $transfer->status);

        // Check stock after reversal: W1 = 100, W2 = 0
        $w1StockAfter = $inventoryService->getStockQuantity($this->item, $this->warehouse1);
        $w2StockAfter = $inventoryService->getStockQuantity($this->item, $this->warehouse2);
        $this->assertEquals(100.0, $w1StockAfter);
        $this->assertEquals(0.0, $w2StockAfter);
    }
}
