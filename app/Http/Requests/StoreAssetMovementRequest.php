<?php

namespace App\Http\Requests;

use App\Enums\MovementSource;
use App\Models\Asset;
use App\Models\LocationMap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAssetMovementRequest extends FormRequest
{
    public const EARLIEST_MOVED_AT = '1970-01-01 00:00:01';

    public const LATEST_MOVED_AT = '2038-01-19 03:14:07';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_location_id' => ['required', 'integer', Rule::exists('locations', 'id')],
            'movement_source' => ['nullable', Rule::enum(MovementSource::class)],
            'moved_by_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'moved_at' => ['nullable', 'date', 'after_or_equal:'.self::EARLIEST_MOVED_AT, 'before_or_equal:'.self::LATEST_MOVED_AT],
            'current_map_id' => ['nullable', 'integer', Rule::exists('location_maps', 'id'), 'required_with:position_x,position_y'],
            'position_x' => ['nullable', 'numeric', 'between:-9999.9999,9999.9999', 'required_with:current_map_id,position_y'],
            'position_y' => ['nullable', 'numeric', 'between:-9999.9999,9999.9999', 'required_with:current_map_id,position_x'],
        ];
    }

    public function messages(): array
    {
        return [
            'moved_at.after_or_equal' => 'The movement date must be on or after 1970-01-01 00:00.',
            'moved_at.before_or_equal' => 'The movement date must be on or before 2038-01-19 03:14.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Asset|null $asset */
                $asset = $this->route('asset');
                $toLocationId = $this->integer('to_location_id');
                $hasPlacementChange = $this->hasAny(['current_map_id', 'position_x', 'position_y']);

                if ($asset instanceof Asset && $asset->current_location_id === $toLocationId && ! $hasPlacementChange) {
                    $validator->errors()->add('to_location_id', 'The asset is already assigned to this location.');
                }

                if (! $this->filled('current_map_id')) {
                    return;
                }

                $mapBelongsToLocation = LocationMap::query()
                    ->whereKey($this->integer('current_map_id'))
                    ->where('location_id', $toLocationId)
                    ->exists();

                if (! $mapBelongsToLocation) {
                    $validator->errors()->add('current_map_id', 'The selected map must belong to the destination location.');
                }
            },
        ];
    }
}
