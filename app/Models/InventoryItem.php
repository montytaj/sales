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
     * Get stock in base units, optionally filtered by warehouse ID
     */
    public function getStockInBaseUnits($warehouseId = null)
    {
        if ($warehouseId) {
            return (float) $this->warehouseItems->where('warehouse_id', $warehouseId)->sum('qty_in_base_units');
        }
        return (float) $this->warehouseItems->sum('qty_in_base_units');
    }

    /**
     * Get wholesale (major unit) quantity for total or specific warehouse
     */
    public function getWholesaleQty($warehouseId = null)
    {
        $factor = (float) $this->conversion_factor;
        if ($factor > 0) {
            $baseStock = $this->getStockInBaseUnits($warehouseId);
            return floor($baseStock / $factor);
        }
        return 0;
    }

    /**
     * Get formatted wholesale (major unit) stock string
     * e.g. "10 كرتونة"
     */
    public function getWholesaleQtyFormatted($warehouseId = null)
    {
        $unitName = $this->wholesaleUnit?->name;
        if (!$unitName) {
            return '-';
        }
        $qty = $this->getWholesaleQty($warehouseId);
        return "{$qty} {$unitName}";
    }

    /**
     * Get formatted base (minor unit) stock string
     * e.g. "20 قطعة"
     */
    public function getBaseQtyFormatted($warehouseId = null)
    {
        $unitName = $this->baseUnit?->name ?? $this->unit ?? 'قطعة';
        $qty = $this->getStockInBaseUnits($warehouseId);
        return "{$qty} {$unitName}";
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

    /**
     * Get effective cost price (with fallback to purchase price, latest purchase invoice, or retail/wholesale estimate).
     * Prevents 0.00 COGS when cost_price is zero or null.
     */
    public function getEffectiveCostPrice($fallbackPrice = 0): float
    {
        $cost = (float) $this->cost_price;
        if ($cost > 0) {
            return $cost;
        }

        $defaultPurchase = (float) $this->default_purchase_price;
        if ($defaultPurchase > 0) {
            return $defaultPurchase;
        }

        $latestPurchaseItem = PurchaseInvoiceItem::where('inventory_item_id', $this->id)
            ->where('unit_price', '>', 0)
            ->latest('id')
            ->first();

        if ($latestPurchaseItem && (float)$latestPurchaseItem->unit_price > 0) {
            return (float) $latestPurchaseItem->unit_price;
        }

        if ($fallbackPrice > 0) {
            return round((float)$fallbackPrice * 0.75, 2);
        }

        $retail = (float) $this->retail_price ?: (float) $this->default_sale_price;
        if ($retail > 0) {
            return round($retail * 0.75, 2);
        }

        $wholesale = (float) $this->wholesale_price;
        if ($wholesale > 0) {
            return round($wholesale * 0.75, 2);
        }

        return 0.00;
    }
}


