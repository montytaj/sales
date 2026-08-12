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
        'payee_name',
        'amount',
        'issue_date',
        'due_date',
        'cleared_at',
        'status',
        'type',
        'cashbox_id',
        'account_id',
        'journal_entry_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'issue_date' => 'date',
        'due_date' => 'date',
        'cleared_at' => 'datetime',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class, 'payment_voucher_id');
    }

    public function cashbox(): BelongsTo
    {
        return $this->belongsTo(Cashbox::class, 'cashbox_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeIncoming($query)
    {
        return $query->where('type', 'incoming');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('type', 'outgoing');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['received', 'under_collection', 'deferred']);
    }

    public function scopeCleared($query)
    {
        return $query->where('status', 'collected');
    }

    public function scopeBounced($query)
    {
        return $query->where('status', 'returned');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
