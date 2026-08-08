<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectChangeOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'order_number',
        'description',
        'cost_impact',
        'time_impact_days',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'cost_impact' => 'float',
        'time_impact_days' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'PCO-' . $year . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
