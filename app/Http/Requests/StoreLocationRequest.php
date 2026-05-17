<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
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
        return [
            'location_map_id' => ['nullable', 'integer', Rule::exists('location_maps', 'id')],
            'code' => ['required', 'string', 'max:255', Rule::unique('locations', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:30'],
            'floor_number' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
