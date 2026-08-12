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
            if (in_array($movementType, ['out', 'reservation', 'waste', 'transfer'])) {
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

            // Sync with warehouse_items table safely using row locking
            $whItem = \App\Models\WarehouseItem::where('warehouse_id', $warehouse->id)
                ->where('inventory_item_id', $item->id)
                ->lockForUpdate()
                ->first();

            if (!$whItem) {
                $whItem = \App\Models\WarehouseItem::create([
                    'warehouse_id' => $warehouse->id,
                    'inventory_item_id' => $item->id,
                    'qty_in_base_units' => 0,
                ]);
            }


            if (in_array($movementType, ['in', 'return', 'adjustment'])) {
                $whItem->increment('qty_in_base_units', $quantity);
            } else if (in_array($movementType, ['out', 'reservation', 'waste', 'transfer'])) {
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

    /**
     * Process and complete a warehouse transfer.
     */
    public function executeTransfer(\App\Models\WarehouseTransfer $transfer): \App\Models\WarehouseTransfer
    {
        return DB::transaction(function () use ($transfer) {
            if ($transfer->status !== 'pending') {
                throw new InvalidArgumentException('عفواً، لا يمكن اعتماد طلب تحويل محول سابقاً أو ملغى.');
            }

            $transfer->load(['items.item', 'fromWarehouse', 'toWarehouse']);

            // 1. Verify stock availability for all items in source warehouse
            foreach ($transfer->items as $transferItem) {
                $item = $transferItem->item;
                $currentStock = $this->getStockQuantity($item, $transfer->fromWarehouse);
                if (($currentStock - (float)$transferItem->quantity) < 0) {
                    throw new InvalidArgumentException("عفواً، رصيد الصنف ({$item->name}) في المخزن المصدر ({$transfer->fromWarehouse->name}) غير كافٍ. المتاح حالياً ({$currentStock}) والمطلوب تحويله ({$transferItem->quantity}).");
                }
            }

            // 2. Perform movements
            foreach ($transfer->items as $transferItem) {
                $item = $transferItem->item;
                $qty = (float)$transferItem->quantity;

                // Out from source warehouse
                $this->recordMovement(
                    $transfer->fromWarehouse,
                    $item,
                    'transfer',
                    $qty,
                    'WarehouseTransfer',
                    $transfer->id,
                    "تحويل مخزني صادر إلى ({$transfer->toWarehouse->name}) - رقم " . $transfer->transfer_number
                );

                // In to destination warehouse
                $this->recordMovement(
                    $transfer->toWarehouse,
                    $item,
                    'in',
                    $qty,
                    'WarehouseTransfer',
                    $transfer->id,
                    "تحويل مخزني وارد من ({$transfer->fromWarehouse->name}) - رقم " . $transfer->transfer_number
                );
            }

            $userId = auth()->id() ?? User::first()?->id ?? 1;

            $transfer->update([
                'status' => 'completed',
                'approved_by' => $userId,
                'completed_at' => now(),
            ]);

            ActivityLog::log(
                'warehouse_transfer_completed',
                $transfer,
                "Completed warehouse transfer {$transfer->transfer_number} from {$transfer->fromWarehouse->name} to {$transfer->toWarehouse->name}"
            );

            return $transfer;
        });
    }

    /**
     * Reverse a completed warehouse transfer and restore stock back to source warehouse.
     */
    public function reverseTransfer(\App\Models\WarehouseTransfer $transfer): \App\Models\WarehouseTransfer
    {
        return DB::transaction(function () use ($transfer) {
            if ($transfer->status !== 'completed') {
                throw new InvalidArgumentException('عفواً، لا يمكن إلغاء وعكس إلا طلبات التحويل المكتملة والمرحلة.');
            }

            $transfer->load(['items.item', 'fromWarehouse', 'toWarehouse']);

            // 1. Verify stock availability in destination warehouse (toWarehouse) to return items back
            foreach ($transfer->items as $transferItem) {
                $item = $transferItem->item;
                $qty = (float)$transferItem->quantity;
                $currentStock = $this->getStockQuantity($item, $transfer->toWarehouse);
                if (($currentStock - $qty) < 0) {
                    throw new InvalidArgumentException("عفواً، لا يمكن عكس التحويل المخزني لأن رصيد الصنف ({$item->name}) بالمخزن المستلم ({$transfer->toWarehouse->name}) غير كافٍ. المتاح حالياً ({$currentStock}) والمطلوب إعادة خصمه ({$qty}).");
                }
            }

            // 2. Perform reverse movements
            foreach ($transfer->items as $transferItem) {
                $item = $transferItem->item;
                $qty = (float)$transferItem->quantity;

                // Out from destination warehouse
                $this->recordMovement(
                    $transfer->toWarehouse,
                    $item,
                    'transfer',
                    $qty,
                    'WarehouseTransfer',
                    $transfer->id,
                    "إلغاء وعكس تحويل مخزني - إعادة الصنف إلى ({$transfer->fromWarehouse->name}) - رقم " . $transfer->transfer_number
                );

                // In to original source warehouse
                $this->recordMovement(
                    $transfer->fromWarehouse,
                    $item,
                    'in',
                    $qty,
                    'WarehouseTransfer',
                    $transfer->id,
                    "إلغاء وعكس تحويل مخزني - استرجاع الصنف من ({$transfer->toWarehouse->name}) - رقم " . $transfer->transfer_number
                );
            }

            $transfer->update([
                'status' => 'cancelled',
            ]);

            ActivityLog::log(
                'warehouse_transfer_reversed',
                $transfer,
                "Reversed completed warehouse transfer {$transfer->transfer_number} from {$transfer->toWarehouse->name} back to {$transfer->fromWarehouse->name}"
            );

            return $transfer;
        });
    }
}


