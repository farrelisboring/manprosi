<?php

namespace App\Models;

use App\Enums\GeofenceRuleType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'asset_id', 'category_id', 'location_id', 'rule_type', 'is_active', 'notes'])]
class AssetGeofence extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rule_type' => GeofenceRuleType::class,
            'is_active' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(AssetAlert::class, 'geofence_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
