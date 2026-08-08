<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

class InventoryItem extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'item_code',
        'code',
        'barcode',
        'category_id',
        'unit',
        'base_unit_id',
        'wholesale_unit_id',
        'conversion_factor',
        'cost_price',
        'wholesale_price',
        'retail_price',
        'min_stock_alert',
        'default_purchase_price',
        'default_sale_price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'cost_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'min_stock_alert' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function wholesaleUnit()
    {
        return $this->belongsTo(Unit::class, 'wholesale_unit_id');
    }

    public function warehouseItems()
    {
        return $this->hasMany(WarehouseItem::class, 'inventory_item_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'item_id');
    }

    /**
     * Get total quantity across all warehouses in base units
     */
    public function getTotalStockInBaseUnitsAttribute()
    {
        return $this->warehouseItems()->sum('qty_in_base_units');
    }

    /**
     * Helper to format total stock into Wholesale + Base units string
     * e.g. "5 كرتونة و 3 قطعة"
     */
    public function getFormattedStockAttribute()
    {
        $totalBase = $this->total_stock_in_base_units;
        $factor = (float) $this->conversion_factor;

        if ($factor > 1 && $this->wholesaleUnit && $this->baseUnit) {
            $wholesaleQty = floor($totalBase / $factor);
            $remainingBase = fmod($totalBase, $factor);

            $parts = [];
            if ($wholesaleQty > 0) {
                $parts[] = "{$wholesaleQty} " . $this->wholesaleUnit->name;
            }
            if ($remainingBase > 0 || $wholesaleQty == 0) {
                $parts[] = "{$remainingBase} " . $this->baseUnit->name;
            }
            return implode(' و ', $parts);
        }

        $unitName = $this->baseUnit?->name ?? $this->unit ?? 'قطعة';
        return "{$totalBase} {$unitName}";
    }
}
