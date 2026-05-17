<?php

namespace App\Models;

use App\Enums\MovementSource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'asset_id',
    'from_location_id',
    'to_location_id',
    'moved_by_user_id',
    'movement_source',
    'reason',
    'notes',
    'moved_at',
])]
class AssetMovement extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'movement_source' => MovementSource::class,
            'moved_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function movedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by_user_id');
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('moved_at')->orderByDesc('id');
    }

    public function scopeWithFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['movement_source'] ?? null, fn (Builder $builder, $source) => $builder->where('movement_source', $source))
            ->when(($filters['from_location_id'] ?? null) !== null, fn (Builder $builder) => $builder->where('from_location_id', $filters['from_location_id']))
            ->when($filters['to_location_id'] ?? null, fn (Builder $builder, $locationId) => $builder->where('to_location_id', $locationId))
            ->when($filters['date_from'] ?? null, fn (Builder $builder, $date) => $builder->where('moved_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, $date) => $builder->where('moved_at', '<=', $date));
    }
}
