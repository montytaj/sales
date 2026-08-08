<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SignageOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'project_id',
        'dimensions',
        'design_file_path',
        'design_approved',
        'design_approved_at',
        'design_approved_by',
        'manufacturing_status',
        'installation_status',
        'installation_date',
        'installer_name',
        'warranty_months',
        'maintenance_notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'design_approved' => 'boolean',
        'design_approved_at' => 'datetime',
        'installation_date' => 'date',
        'warranty_months' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function designApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'design_approved_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'SIG-' . $year . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
