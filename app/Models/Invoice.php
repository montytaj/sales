<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\Auditable;

class Invoice extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'invoice_number',
        'quotation_id',
        'customer_id',
        'branch_id',
        'warehouse_id',
        'status',
        'issue_date',
        'due_date',
        'payment_type',
        'cash_amount',
        'bank_amount',
        'due_amount',
        'cash_account_id',
        'bank_account_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'cash_amount' => 'float',
        'bank_amount' => 'float',
        'due_amount' => 'float',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = setting('doc_prefix_invoice', 'INV-');
        $year = date('Y');
        $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
        return $prefix . $year . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
