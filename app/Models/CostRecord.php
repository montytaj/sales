<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CostRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'costable_type',
        'costable_id',
        'cost_type',
        'estimated_cost',
        'actual_cost',
        'notes',
    ];

    protected $casts = [
        'estimated_cost' => 'float',
        'actual_cost' => 'float',
    ];

    public function costable(): MorphTo
    {
        return $this->morphTo();
    }
}
