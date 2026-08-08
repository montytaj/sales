<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderAuthorization extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'authorized_by',
        'authorized_at',
        'paid_amount',
        'remaining_balance',
        'is_override',
        'override_reason',
        'notes',
    ];

    protected $casts = [
        'authorized_at' => 'datetime',
        'paid_amount' => 'float',
        'remaining_balance' => 'float',
        'is_override' => 'boolean',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
