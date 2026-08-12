<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getReferenceTypeNameAttribute(): string
    {
        $type = $this->reference_type;
        if (empty($type)) {
            return __('inventory.reference_types.unnamed');
        }

        if (str_contains($type, 'Invoice') && !str_contains($type, 'Purchase')) {
            return __('inventory.reference_types.sales_invoice');
        }
        if (str_contains($type, 'PurchaseInvoice') || str_contains($type, 'Purchase')) {
            return __('inventory.reference_types.purchase_invoice');
        }
        if (str_contains($type, 'WarehouseTransfer') || str_contains($type, 'Transfer')) {
            return __('inventory.reference_types.warehouse_transfer');
        }
        if ($type === 'opening_balance' || str_contains($type, 'Opening')) {
            return __('inventory.reference_types.opening_balance');
        }
        if ($type === 'adjustment' || str_contains($type, 'Adjustment')) {
            return __('inventory.reference_types.adjustment');
        }

        return class_basename($type);
    }

    public function getReferenceUrlAttribute(): ?string
    {
        $type = $this->reference_type;
        $id = $this->reference_id;

        if (!$id) {
            return null;
        }

        if (str_contains($type, 'Invoice') && !str_contains($type, 'Purchase')) {
            return \Illuminate\Support\Facades\Route::has('invoices.show') ? route('invoices.show', $id) : null;
        }
        if (str_contains($type, 'PurchaseInvoice') || str_contains($type, 'Purchase')) {
            return \Illuminate\Support\Facades\Route::has('purchases.show_invoice') ? route('purchases.show_invoice', $id) : null;
        }
        if (str_contains($type, 'WarehouseTransfer') || str_contains($type, 'Transfer')) {
            return \Illuminate\Support\Facades\Route::has('warehouse-transfers.show') ? route('warehouse-transfers.show', $id) : null;
        }

        return null;
    }
}
