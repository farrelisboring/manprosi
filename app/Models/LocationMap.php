<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['location_id', 'name', 'image_path', 'image_width', 'image_height', 'notes'])]
class LocationMap extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'image_width' => 'integer',
            'image_height' => 'integer',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'location_map_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'current_map_id');
    }
}
