<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'is_active',
        'main_branch_id',
    ];

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function mainBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'main_branch_id');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user')->withTimestamps();
    }

    /**
     * Return collection of all branches this user is authorized to access.
     */
    public function accessibleBranches(): Collection
    {
        if ($this->hasRole(['system-admin', 'general-manager'])) {
            return Branch::where('is_active', true)->get();
        }

        $branchIds = $this->branches->pluck('id')->toArray();
        if ($this->main_branch_id) {
            $branchIds[] = $this->main_branch_id;
        }

        return Branch::whereIn('id', array_unique($branchIds))->where('is_active', true)->get();
    }

    /**
     * Check if user can access a specific branch ID
     */
    public function hasAccessToBranch(int|string $branchId): bool
    {
        if ($this->hasRole(['system-admin', 'general-manager'])) {
            return true;
        }

        if ($this->main_branch_id == $branchId) {
            return true;
        }

        return $this->branches()->where('branches.id', $branchId)->exists();
    }
}
