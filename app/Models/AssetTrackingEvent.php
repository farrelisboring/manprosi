<?php

namespace App\Models;

use App\Enums\TrackingEventType;
use App\Enums\TrackingSource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'asset_id',
    'location_id',
    'reader_identifier',
    'source',
    'event_type',
    'raw_tag',
    'payload',
    'detected_at',
])]
class AssetTrackingEvent extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'source' => TrackingSource::class,
            'event_type' => TrackingEventType::class,
            'payload' => 'array',
            'detected_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(AssetAlert::class, 'tracking_event_id');
    }

    public function scopeRecentFirst(Builder $query): Builder
    {
        return $query->orderByDesc('detected_at')->orderByDesc('id');
    }

    public function scopeForAsset(Builder $query, Asset|int $asset): Builder
    {
        $assetId = $asset instanceof Asset ? $asset->getKey() : $asset;

        return $query->where('asset_id', $assetId);
    }

    public function scopeAtLocation(Builder $query, Location|int $location): Builder
    {
        $locationId = $location instanceof Location ? $location->getKey() : $location;

        return $query->where('location_id', $locationId);
    }

    public function scopeFromSource(Builder $query, TrackingSource|string $source): Builder
    {
        $sourceValue = $source instanceof TrackingSource ? $source->value : $source;

        return $query->where('source', $sourceValue);
    }
}
