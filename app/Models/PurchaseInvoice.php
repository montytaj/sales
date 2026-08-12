<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'payment_type',
        'cash_amount',
        'bank_amount',
        'due_amount',
        'cash_account_id',
        'bank_account_id',
        'total_amount',
        'tax_amount',
        'net_amount',
        'status',
        'invoice_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'cash_amount' => 'float',
        'bank_amount' => 'float',
        'due_amount' => 'float',
        'total_amount' => 'float',
        'tax_amount' => 'float',
        'net_amount' => 'float',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class, 'purchase_invoice_id');
    }

    public function getTotalPaidAttribute(): float
    {
        $initialPaid = (float)($this->cash_amount + $this->bank_amount);
        $vouchersPaid = (float)$this->payments()->where('status', 'completed')->sum('amount');
        return $initialPaid + $vouchersPaid;
    }

    public function getRemainingDueAttribute(): float
    {
        return max(0, (float)$this->net_amount - $this->total_paid);
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'PINV-' . $year . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
