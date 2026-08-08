<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'weight_percentage',
        'completion_percentage',
        'start_date',
        'due_date',
        'status',
    ];

    protected $casts = [
        'weight_percentage' => 'float',
        'completion_percentage' => 'float',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
