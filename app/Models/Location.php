<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['parent_id', 'code', 'name', 'type', 'floor_number', 'description', 'is_active'])]
class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'floor_number' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function maps(): HasMany
    {
        return $this->hasMany(LocationMap::class, 'location_id');
    }

    public function currentAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'current_location_id');
    }

    public function incomingMovements(): HasMany
    {
        return $this->hasMany(AssetMovement::class, 'to_location_id');
    }

    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(AssetMovement::class, 'from_location_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(AssetTrackingEvent::class, 'location_id');
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'location_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(AssetAlert::class, 'location_id');
    }

    public function geofences(): HasMany
    {
        return $this->hasMany(AssetGeofence::class, 'location_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOnFloor(Builder $query, int $floorNumber): Builder
    {
        return $query->where('floor_number', $floorNumber);
    }
}
