<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractPaymentTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'milestone_name',
        'due_date',
        'amount_type',
        'value',
        'calculated_amount',
        'paid_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'value' => 'float',
        'calculated_amount' => 'float',
        'paid_amount' => 'float',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
