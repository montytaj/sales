<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'inventory_item_id',
        'unit_id',
        'qty_in_base_units',
        'service_id',
        'item_name',
        'description',
        'quantity',
        'unit_of_measure',
        'unit_price',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'subtotal',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'float',
        'qty_in_base_units' => 'float',
        'unit_price' => 'float',
        'discount_amount' => 'float',
        'tax_percent' => 'float',
        'tax_amount' => 'float',
        'subtotal' => 'float',
        'total' => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
