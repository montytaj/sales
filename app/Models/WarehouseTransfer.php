<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'transfer_date',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'completed_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseTransferItem::class, 'warehouse_transfer_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill"><i class="bi bi-clock-history me-1"></i> ' . (app()->getLocale() == 'ar' ? 'قيد الانتظار' : 'Pending') . '</span>',
            'completed' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i> ' . (app()->getLocale() == 'ar' ? 'مكتمل ومرحل' : 'Completed') . '</span>',
            'cancelled' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill"><i class="bi bi-x-circle me-1"></i> ' . (app()->getLocale() == 'ar' ? 'ملغى' : 'Cancelled') . '</span>',
            default => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }
}
