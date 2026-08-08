<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'name',
        'company_name',
        'phone',
        'phone_secondary',
        'email',
        'address',
        'city',
        'cr_number',
        'vat_number',
        'credit_limit',
        'credit_period_days',
        'category',
        'is_active',
        'notes',
        'branch_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'float',
        'credit_period_days' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Generate next customer code sequentially.
     */
    public static function generateCode(): string
    {
        $lastId = static::max('id') ?? 0;
        return 'CUST-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
