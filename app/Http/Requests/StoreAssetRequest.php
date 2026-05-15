<?php

namespace App\Http\Requests;

use App\Enums\AssetStatus;
use App\Http\Requests\Concerns\ValidatesAssetPlacement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    use ValidatesAssetPlacement;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeAssetPlacementInput();
    }

    public function rules(): array
    {
        return [
            'asset_code' => ['required', 'string', 'max:255', Rule::unique('assets', 'asset_code')],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists('asset_categories', 'id')],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'barcode_value' => ['nullable', 'string', 'max:255', Rule::unique('assets', 'barcode_value')],
            'qr_code_value' => ['nullable', 'string', 'max:255', Rule::unique('assets', 'qr_code_value')],
            'rfid_tag' => ['nullable', 'string', 'max:255', Rule::unique('assets', 'rfid_tag')],
            'status' => ['nullable', Rule::enum(AssetStatus::class)],
            'current_location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            ...$this->assetPlacementRules(),
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return $this->assetPlacementAfter();
    }
}
