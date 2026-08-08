<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'customer_id',
        'customer_order_id',
        'branch_id',
        'status',
        'is_approved',
        'approved_by',
        'approved_at',
        'issue_date',
        'expiry_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'terms_conditions',
        'created_by',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public static function generateQuotationNumber(): string
    {
        $prefix = setting('doc_prefix_quotation', 'OFFER-');
        $year = date('Y');
        $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
        return $prefix . $year . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
