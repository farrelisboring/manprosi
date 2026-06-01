<?php

namespace App\Http\Requests;

use App\Enums\AssetStatus;
use App\Http\Requests\Concerns\ValidatesAssetPlacement;
use App\Services\QrCodeValueGenerator;
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
        $this->normalizeGeofenceInput();

        if ($this->exists('qr_code_value')) {
            $this->merge([
                'qr_code_value' => QrCodeValueGenerator::normalize($this->input('qr_code_value')),
            ]);
        }

        if (! $this->filled('status')) {
            $this->merge([
                'status' => AssetStatus::Available->value,
            ]);
        }
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
            'qr_code_value' => ['nullable', ...QrCodeValueGenerator::validationRules(), Rule::unique('assets', 'qr_code_value')],
            'rfid_tag' => ['nullable', 'string', 'max:255', Rule::unique('assets', 'rfid_tag')],
            'status' => ['nullable', Rule::enum(AssetStatus::class)],
            'current_location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            ...$this->assetPlacementRules(),
            'notes' => ['nullable', 'string'],
            'geofence_enabled' => ['nullable', 'boolean'],
            'geofence_on_room_change' => ['nullable', 'boolean'],
            'geofence_forbidden_location_ids' => ['nullable', 'array'],
            'geofence_forbidden_location_ids.*' => ['integer', 'distinct', Rule::exists('locations', 'id')],
        ];
    }

    public function after(): array
    {
        return $this->assetPlacementAfter();
    }

    protected function normalizeGeofenceInput(): void
    {
        $forbiddenLocationIds = collect((array) $this->input('geofence_forbidden_location_ids', []))
            ->filter(fn (mixed $value) => $value !== null && $value !== '')
            ->map(fn (mixed $value) => (int) $value)
            ->values()
            ->all();

        $this->merge([
            'geofence_enabled' => $this->boolean('geofence_enabled'),
            'geofence_on_room_change' => $this->boolean('geofence_on_room_change'),
            'geofence_forbidden_location_ids' => $forbiddenLocationIds,
        ]);
    }
}
