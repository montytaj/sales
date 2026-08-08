<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_voucher_id',
        'cheque_number',
        'bank_name',
        'drawer_name',
        'amount',
        'issue_date',
        'due_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class, 'payment_voucher_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
