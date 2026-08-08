<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number',
        'customer_id',
        'branch_id',
        'scope_of_work',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'net_amount',
        'start_date',
        'end_date',
        'warranty_terms',
        'payment_terms',
        'status',
        'is_approved',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'total_amount' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'net_amount' => 'float',
        'is_approved' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paymentTerms(): HasMany
    {
        return $this->hasMany(ContractPaymentTerm::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public static function generateContractNumber(): string
    {
        $year = date('Y');
        $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'CNT-' . $year . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
