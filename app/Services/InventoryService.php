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
        $query = StockMovement::where('item_id', $item->id);

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        $inQty = (float) (clone $query)->whereIn('movement_type', ['in', 'return', 'adjustment'])->sum('quantity');
        $outQty = (float) (clone $query)->whereIn('movement_type', ['out', 'reservation', 'waste'])->sum('quantity');

        return round($inQty - $outQty, 2);
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
            $allowNegativeStock = setting('allow_negative_stock', false);

            if (in_array($movementType, ['out', 'reservation', 'waste']) && !$allowNegativeStock) {
                $currentStock = $this->getStockQuantity($item, $warehouse);
                if (($currentStock - $quantity) < 0) {
                    throw new InvalidArgumentException("لا يمكن صرف الكمية المطلوب ({$quantity}) لعدم كفاية رصيد المخزون الحالي ({$currentStock}) حسب إعدادات النظام.");
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
