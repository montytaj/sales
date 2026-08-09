<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\InventoryScrap;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Get total current stock quantity for an item across all or specific warehouse.
     */
    public function getStockQuantity(InventoryItem $item, ?Warehouse $warehouse = null): float
    {
        if ($warehouse) {
            $whItem = \App\Models\WarehouseItem::where('inventory_item_id', $item->id)->where('warehouse_id', $warehouse->id)->first();
            return max(0, (float)($whItem?->qty_in_base_units ?? 0));
        }
        return max(0, (float)$item->warehouseItems()->sum('qty_in_base_units'));
    }

    /**
     * Record a stock movement.
     */
    public function recordMovement(
        Warehouse $warehouse,
        InventoryItem $item,
        string $movementType,
        float $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        return DB::transaction(function () use ($warehouse, $item, $movementType, $quantity, $referenceType, $referenceId, $notes) {
            if (in_array($movementType, ['out', 'reservation', 'waste'])) {
                $currentStock = $this->getStockQuantity($item, $warehouse);
                if (($currentStock - $quantity) < 0) {
                    throw new InvalidArgumentException("عفواً، لا يمكن صرف الكمية المطلوبة ({$quantity}) للصنف ({$item->name}) لعدم كفاية رصيد المخزون الحالي بالمخزن ({$currentStock}).");
                }
            }

            $userId = auth()->id() ?? User::first()?->id ?? 1;

            $movement = StockMovement::create([
                'warehouse_id' => $warehouse->id,
                'item_id' => $item->id,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            // Sync with warehouse_items table
            $whItem = \App\Models\WarehouseItem::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'inventory_item_id' => $item->id],
                ['qty_in_base_units' => 0]
            );

            if (in_array($movementType, ['in', 'return', 'adjustment'])) {
                $whItem->increment('qty_in_base_units', $quantity);
            } else if (in_array($movementType, ['out', 'reservation', 'waste'])) {
                $newQty = max(0, (float)$whItem->qty_in_base_units - $quantity);
                $whItem->update(['qty_in_base_units' => $newQty]);
            }

            ActivityLog::log(
                'stock_movement_recorded',
                $item,
                "Recorded stock movement {$movementType} of {$quantity} {$item->unit} in warehouse {$warehouse->name}"
            );

            return $movement;
        });
    }

    /**
     * Record sheet offcut / scrap.
     */
    public function recordScrap(InventoryItem $item, Warehouse $warehouse, string $dimensions, float $quantity = 1.0, ?string $notes = null): InventoryScrap
    {
        return InventoryScrap::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'dimensions' => $dimensions,
            'quantity' => $quantity,
            'status' => 'available',
            'notes' => $notes,
        ]);
    }
}
