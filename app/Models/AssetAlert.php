<?php

namespace App\Models;

use App\Enums\AlertStatus;
use App\Enums\AlertType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'asset_id',
    'geofence_id',
    'location_id',
    'tracking_event_id',
    'alert_type',
    'message',
    'status',
    'triggered_at',
    'acknowledged_at',
    'acknowledged_by_user_id',
    'resolved_at',
    'resolution_notes',
])]
class AssetAlert extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'alert_type' => AlertType::class,
            'status' => AlertStatus::class,
            'triggered_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function geofence(): BelongsTo
    {
        return $this->belongsTo(AssetGeofence::class, 'geofence_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function trackingEvent(): BelongsTo
    {
        return $this->belongsTo(AssetTrackingEvent::class, 'tracking_event_id');
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_user_id');
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', AlertStatus::New->value);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            AlertStatus::New->value,
            AlertStatus::Acknowledged->value,
        ]);
    }

    public function scopeAcknowledged(Builder $query): Builder
    {
        return $query->where('status', AlertStatus::Acknowledged->value);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', AlertStatus::Resolved->value);
    }

    public function scopeRecentFirst(Builder $query): Builder
    {
        return $query->orderByDesc('triggered_at')->orderByDesc('id');
    }
}
