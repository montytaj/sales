<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Services\InventoryService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Warehouse $warehouse;
    protected InventoryItem $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->warehouse = Warehouse::create(['name' => 'المستودع الرئيسي', 'code' => 'WH-MAIN', 'is_active' => true]);
        $this->item = InventoryItem::create([
            'name' => 'ألواح MDF 18mm',
            'item_code' => 'RAW-MDF-18',
            'unit' => 'sheet',
            'is_sheet_material' => true,
            'sheet_dimensions' => '122x244 cm',
            'min_stock_alert' => 10,
            'default_purchase_price' => 120.00,
        ]);
    }

    public function test_inventory_feature_flag_toggle(): void
    {
        $settingsService = app(SettingsService::class);

        // Disable Inventory
        $settingsService->set('inventory_enabled', false, 'feature_flags', 'boolean');
        $response = $this->actingAs($this->admin)->get('/ar/inventory');
        $response->assertRedirect('/ar/dashboard');

        // Enable Inventory
        $settingsService->set('inventory_enabled', true, 'feature_flags', 'boolean');
        $response = $this->actingAs($this->admin)->get('/ar/inventory');
        $response->assertStatus(200);
    }

    public function test_stock_movement_and_negative_stock_prevention(): void
    {
        $inventoryService = app(InventoryService::class);
        $settingsService = app(SettingsService::class);

        $settingsService->set('allow_negative_stock', false, 'financial', 'boolean');

        // Record Stock IN
        $inventoryService->recordMovement($this->warehouse, $this->item, 'in', 50.0);
        $this->assertEquals(50.0, $inventoryService->getStockQuantity($this->item, $this->warehouse));

        // Record Stock OUT (Valid)
        $inventoryService->recordMovement($this->warehouse, $this->item, 'out', 30.0);
        $this->assertEquals(20.0, $inventoryService->getStockQuantity($this->item, $this->warehouse));

        // Record Stock OUT exceeding balance (Fails)
        $this->expectException(\InvalidArgumentException::class);
        $inventoryService->recordMovement($this->warehouse, $this->item, 'out', 25.0);
    }

    public function test_sheet_scrap_logging(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/inventory/scraps', [
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item->id,
            'dimensions' => '60x122 cm',
            'quantity' => 2,
            'notes' => 'متبقي من أمر قص ورشة',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('inventory_scraps', [
            'item_id' => $this->item->id,
            'dimensions' => '60x122 cm',
            'quantity' => 2,
        ]);
    }
}
