<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function baseItems()
    {
        return $this->hasMany(InventoryItem::class, 'base_unit_id');
    }

    public function wholesaleItems()
    {
        return $this->hasMany(InventoryItem::class, 'wholesale_unit_id');
    }
}
