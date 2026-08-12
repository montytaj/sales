<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'level',
        'type',
        'nature',
        'balance',
        'is_selectable',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'balance' => 'decimal:2',
        'is_selectable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id')->orderBy('code');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    protected static function booted()
    {
        static::saved(function () {
            static::clearAccountCache();
        });

        static::deleted(function () {
            static::clearAccountCache();
        });
    }

    public static function clearAccountCache()
    {
        \Illuminate\Support\Facades\Cache::forget('chart_of_accounts_tree');
        \Illuminate\Support\Facades\Cache::forget('chart_of_accounts_selectable');
    }

    public static function getTreeCached()
    {
        return \Illuminate\Support\Facades\Cache::remember('chart_of_accounts_tree', 3600, function () {
            return static::whereNull('parent_id')
                ->where('is_active', true)
                ->with('children.children.children')
                ->orderBy('code')
                ->get();
        });
    }

    public static function getSelectableCached()
    {
        return \Illuminate\Support\Facades\Cache::remember('chart_of_accounts_selectable', 3600, function () {
            return static::where('is_selectable', true)
                ->where('is_active', true)
                ->orderBy('code')
                ->get();
        });
    }
}
