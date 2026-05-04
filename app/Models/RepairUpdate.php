<?php

namespace App\Models;

use App\Enums\DamageStatus;
use App\Enums\RepairUpdateType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'damage_report_id',
    'updated_by_user_id',
    'update_type',
    'status_after',
    'result_summary',
    'notes',
    'logged_at',
])]
class RepairUpdate extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'update_type' => RepairUpdateType::class,
            'status_after' => DamageStatus::class,
            'logged_at' => 'datetime',
        ];
    }

    public function damageReport(): BelongsTo
    {
        return $this->belongsTo(DamageReport::class, 'damage_report_id');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function scopeRecentFirst(Builder $query): Builder
    {
        return $query->orderByDesc('logged_at')->orderByDesc('id');
    }
}
