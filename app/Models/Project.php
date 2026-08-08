<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_number',
        'name',
        'contract_id',
        'customer_id',
        'branch_id',
        'manager_id',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'budget',
        'completion_percentage',
        'status',
        'warranty_start_date',
        'warranty_end_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
        'budget' => 'float',
        'completion_percentage' => 'float',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProjectStage::class);
    }

    public function changeOrders(): HasMany
    {
        return $this->hasMany(ProjectChangeOrder::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function signageOrders(): HasMany
    {
        return $this->hasMany(SignageOrder::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public static function generateProjectNumber(): string
    {
        $year = date('Y');
        $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'PRJ-' . $year . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
