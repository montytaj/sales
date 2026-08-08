<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'service_type',
        'default_price',
        'unit_of_measure',
        'is_taxable',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
        'default_price' => 'float',
    ];

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'en' && !empty($this->name_en)
            ? $this->name_en
            : $this->name_ar;
    }

    /**
     * Generate next service code sequentially.
     */
    public static function generateCode(): string
    {
        $lastId = static::max('id') ?? 0;
        return 'SRV-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
    }
}
