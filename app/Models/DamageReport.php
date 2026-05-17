<?php

namespace App\Models;

use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'asset_id',
    'reported_by_user_id',
    'location_id',
    'title',
    'description',
    'severity',
    'status',
    'reported_at',
    'resolved_at',
])]
class DamageReport extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'severity' => DamageSeverity::class,
            'status' => DamageStatus::class,
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function reportedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function repairUpdates(): HasMany
    {
        return $this->hasMany(RepairUpdate::class, 'damage_report_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            DamageStatus::Reported->value,
            DamageStatus::InProgress->value,
        ]);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', DamageStatus::Resolved->value);
    }

    public function scopeRecentFirst(Builder $query): Builder
    {
        return $query->orderByDesc('reported_at')->orderByDesc('id');
    }

    public function scopeWithFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['asset_id'] ?? null, fn (Builder $builder, $assetId) => $builder->forAsset((int) $assetId))
            ->when($filters['status'] ?? null, fn (Builder $builder, $status) => $builder->where('status', $status))
            ->when($filters['severity'] ?? null, fn (Builder $builder, $severity) => $builder->withSeverity($severity))
            ->when($filters['location_id'] ?? null, fn (Builder $builder, $locationId) => $builder->where('location_id', $locationId))
            ->when($filters['reported_by_user_id'] ?? null, fn (Builder $builder, $userId) => $builder->where('reported_by_user_id', $userId))
            ->when($filters['date_from'] ?? null, fn (Builder $builder, $date) => $builder->where('reported_at', '>=', Carbon::parse($date)->toDateTimeString()))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, $date) => $builder->where('reported_at', '<=', Carbon::parse($date)->toDateTimeString()));
    }

    public function scopeWithSeverity(Builder $query, DamageSeverity|string $severity): Builder
    {
        $severityValue = $severity instanceof DamageSeverity ? $severity->value : $severity;

        return $query->where('severity', $severityValue);
    }

    public function scopeForAsset(Builder $query, Asset|int $asset): Builder
    {
        $assetId = $asset instanceof Asset ? $asset->getKey() : $asset;

        return $query->where('asset_id', $assetId);
    }
}
