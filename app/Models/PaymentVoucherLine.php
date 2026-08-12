<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucherLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_voucher_id',
        'payment_method',
        'account_id',
        'amount',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class, 'payment_voucher_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
