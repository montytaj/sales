<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cashbox extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'branch_id',
        'account_id',
        'opening_balance',
        'current_balance',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'float',
        'current_balance' => 'float',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cashbox_user');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(CashboxShift::class);
    }

    public function activeShift(): ?CashboxShift
    {
        return $this->shifts()->where('status', 'open')->first();
    }

    public static function generateCode(): string
    {
        $lastId = static::max('id') ?? 0;
        return 'CB-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
    }
}
