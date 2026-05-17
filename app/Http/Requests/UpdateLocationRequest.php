<?php

namespace App\Http\Requests;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        /** @var Location $location */
        $location = $this->route('location');

        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:255', Rule::unique('locations', 'code')->ignore($location)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:30'],
            'floor_number' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Location|null $location */
                $location = $this->route('location');
                $parentId = $this->input('parent_id');

                if (! $location || ! filled($parentId)) {
                    return;
                }

                if ((int) $parentId === $location->id) {
                    $validator->errors()->add('parent_id', 'A location cannot be its own parent.');

                    return;
                }

                if (in_array((int) $parentId, $this->descendantIds($location), true)) {
                    $validator->errors()->add('parent_id', 'Choose a parent location outside the current location hierarchy.');
                }
            },
        ];
    }

    private function descendantIds(Location $location): array
    {
        $descendantIds = [];
        $pendingIds = [$location->id];

        while ($pendingIds !== []) {
            $childIds = Location::query()
                ->whereIn('parent_id', $pendingIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $childIds = array_values(array_diff($childIds, $descendantIds));

            if ($childIds === []) {
                break;
            }

            $descendantIds = array_merge($descendantIds, $childIds);
            $pendingIds = $childIds;
        }

        return $descendantIds;
    }
}
