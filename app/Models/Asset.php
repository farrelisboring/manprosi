<?php

namespace App\Models;

use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'asset_code',
    'name',
    'category_id',
    'description',
    'brand',
    'model',
    'serial_number',
    'barcode_value',
    'qr_code_value',
    'rfid_tag',
    'status',
    'current_location_id',
    'current_map_id',
    'position_x',
    'position_y',
    'notes',
    'created_by',
    'updated_by',
])]
class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => AssetStatus::class,
            'position_x' => 'float',
            'position_y' => 'float',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function currentMap(): BelongsTo
    {
        return $this->belongsTo(LocationMap::class, 'current_map_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(AssetMovement::class, 'asset_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(AssetTrackingEvent::class, 'asset_id');
    }

    public function geofences(): HasMany
    {
        return $this->hasMany(AssetGeofence::class, 'asset_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(AssetAlert::class, 'asset_id');
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'asset_id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('asset_code', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhere('serial_number', 'like', "%{$term}%")
                ->orWhere('barcode_value', 'like', "%{$term}%")
                ->orWhere('qr_code_value', 'like', "%{$term}%")
                ->orWhere('rfid_tag', 'like', "%{$term}%")
                ->orWhereHas('category', function (Builder $categoryQuery) use ($term): void {
                    $categoryQuery->where('name', 'like', "%{$term}%");
                });
        });
    }

    public function scopeForCategory(Builder $query, AssetCategory|int $category): Builder
    {
        $categoryId = $category instanceof AssetCategory ? $category->getKey() : $category;

        return $query->where('category_id', $categoryId);
    }

    public function scopeAtLocation(Builder $query, Location|int $location): Builder
    {
        $locationId = $location instanceof Location ? $location->getKey() : $location;

        return $query->where('current_location_id', $locationId);
    }

    public function scopeWithStatus(Builder $query, AssetStatus|string $status): Builder
    {
        $statusValue = $status instanceof AssetStatus ? $status->value : $status;

        return $query->where('status', $statusValue);
    }

    public function hasMapPlacement(): bool
    {
        return $this->current_map_id !== null
            && $this->position_x !== null
            && $this->position_y !== null;
    }

    public function hasRfid(): bool
    {
        return filled($this->rfid_tag);
    }

    public function hasPrintableCode(): bool
    {
        return filled($this->qr_code_value) || filled($this->barcode_value);
    }
}
