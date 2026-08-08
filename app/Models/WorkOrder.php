<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_number',
        'invoice_id',
        'customer_id',
        'branch_id',
        'assigned_to',
        'assigned_by',
        'sheet_count',
        'sheet_type',
        'dimensions',
        'thickness',
        'priority',
        'due_date',
        'status',
        'good_pieces',
        'waste_pieces',
        'delivery_receiver_name',
        'delivery_notes',
        'delivered_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'delivered_at' => 'datetime',
        'good_pieces' => 'integer',
        'waste_pieces' => 'integer',
        'sheet_count' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function authorization(): HasOne
    {
        return $this->hasOne(WorkOrderAuthorization::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(WorkOrderTimeLog::class)->orderBy('logged_at');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public static function generateWorkOrderNumber(): string
    {
        $prefix = setting('doc_prefix_work_order', 'JOB-');
        $year = date('Y');
        $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
        return $prefix . $year . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
