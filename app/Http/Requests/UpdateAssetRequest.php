<?php

namespace App\Http\Requests;

use App\Enums\AssetStatus;
use App\Http\Requests\Concerns\ValidatesAssetPlacement;
use App\Models\Asset;
use App\Services\QrCodeValueGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    use ValidatesAssetPlacement;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeAssetPlacementInput();

        if ($this->exists('qr_code_value')) {
            $this->merge([
                'qr_code_value' => QrCodeValueGenerator::normalize($this->input('qr_code_value')),
            ]);
        }
    }

    public function rules(): array
    {
        /** @var Asset|null $asset */
        $asset = $this->route('asset');

        return [
            'asset_code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('assets', 'asset_code')->ignore($asset)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'required', 'integer', Rule::exists('asset_categories', 'id')],
            'description' => ['sometimes', 'nullable', 'string'],
            'brand' => ['sometimes', 'nullable', 'string', 'max:255'],
            'model' => ['sometimes', 'nullable', 'string', 'max:255'],
            'serial_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'barcode_value' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('assets', 'barcode_value')->ignore($asset)],
            'qr_code_value' => ['sometimes', 'nullable', ...QrCodeValueGenerator::validationRules(), Rule::unique('assets', 'qr_code_value')->ignore($asset)],
            'rfid_tag' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('assets', 'rfid_tag')->ignore($asset)],
            'status' => ['sometimes', 'nullable', Rule::enum(AssetStatus::class)],
            'current_location_id' => ['sometimes', 'nullable', 'integer', Rule::exists('locations', 'id')],
            'current_map_id' => ['sometimes', ...$this->assetPlacementRules()['current_map_id']],
            'position_x' => ['sometimes', ...$this->assetPlacementRules()['position_x']],
            'position_y' => ['sometimes', ...$this->assetPlacementRules()['position_y']],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return $this->assetPlacementAfter();
    }
}
