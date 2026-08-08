<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'inventory_item_id',
        'qty_in_base_units',
    ];

    protected $casts = [
        'qty_in_base_units' => 'decimal:4',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * Formatted stock for this specific warehouse
     */
    public function getFormattedStockAttribute()
    {
        $qty = (float) $this->qty_in_base_units;
        $item = $this->item;
        if (!$item) return "{$qty}";

        $factor = (float) $item->conversion_factor;
        if ($factor > 1 && $item->wholesaleUnit && $item->baseUnit) {
            $wholesaleQty = floor($qty / $factor);
            $remainingBase = fmod($qty, $factor);

            $parts = [];
            if ($wholesaleQty > 0) {
                $parts[] = "{$wholesaleQty} " . $item->wholesaleUnit->name;
            }
            if ($remainingBase > 0 || $wholesaleQty == 0) {
                $parts[] = "{$remainingBase} " . $item->baseUnit->name;
            }
            return implode(' و ', $parts);
        }

        $unitName = $item->baseUnit?->name ?? $item->unit ?? 'قطعة';
        return "{$qty} {$unitName}";
    }
}
