<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => UserRole::class,
            'password' => 'hashed',
        ];
    }

    public function createdAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'created_by');
    }

    public function updatedAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'updated_by');
    }

    public function recordedMovements(): HasMany
    {
        return $this->hasMany(AssetMovement::class, 'moved_by_user_id');
    }

    public function acknowledgedAlerts(): HasMany
    {
        return $this->hasMany(AssetAlert::class, 'acknowledged_by_user_id');
    }

    public function reportedDamageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'reported_by_user_id');
    }

    public function repairUpdates(): HasMany
    {
        return $this->hasMany(RepairUpdate::class, 'updated_by_user_id');
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isNurse(): bool
    {
        return $this->role === UserRole::Nurse;
    }
}
