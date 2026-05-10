<?php

namespace App\Http\Requests;

use App\Enums\AssetStatus;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'qr_code_value' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('assets', 'qr_code_value')->ignore($asset)],
            'rfid_tag' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('assets', 'rfid_tag')->ignore($asset)],
            'status' => ['sometimes', 'nullable', Rule::enum(AssetStatus::class)],
            'current_location_id' => ['sometimes', 'nullable', 'integer', Rule::exists('locations', 'id')],
            'current_map_id' => ['sometimes', 'nullable', 'integer', Rule::exists('location_maps', 'id')],
            'position_x' => ['sometimes', 'nullable', 'numeric', 'between:-9999.9999,9999.9999'],
            'position_y' => ['sometimes', 'nullable', 'numeric', 'between:-9999.9999,9999.9999'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
