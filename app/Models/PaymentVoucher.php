<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'type',
        'customer_id',
        'supplier_id',
        'invoice_id',
        'purchase_invoice_id',
        'cashbox_id',
        'target_cashbox_id',
        'amount',
        'payment_date',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function cashbox(): BelongsTo
    {
        return $this->belongsTo(Cashbox::class);
    }

    public function targetCashbox(): BelongsTo
    {
        return $this->belongsTo(Cashbox::class, 'target_cashbox_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PaymentVoucherLine::class);
    }

    public function cheques(): HasMany
    {
        return $this->hasMany(Cheque::class);
    }

    public static function generateVoucherNumber(string $type): string
    {
        $prefix = match($type) {
            'receipt' => 'RCT-',
            'payment' => 'PAY-',
            'transfer' => 'TRF-',
            default => 'VCT-',
        };

        $year = date('Y');
        $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
        return $prefix . $year . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
