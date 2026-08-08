<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_base',
        'is_active',
    ];

    protected $casts = [
        'exchange_rate' => 'float',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the base currency of the system.
     */
    public static function getBaseCurrency(): ?self
    {
        return static::where('is_base', true)->first() 
            ?? static::where('code', 'SAR')->first() 
            ?? static::first();
    }
}
