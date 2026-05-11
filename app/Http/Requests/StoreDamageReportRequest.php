<?php

namespace App\Http\Requests;

use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDamageReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id' => ['required', 'integer', Rule::exists('assets', 'id')],
            'reported_by_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => [Rule::enum(DamageSeverity::class)],
            'status' => [Rule::enum(DamageStatus::class)],
            'reported_at' => ['nullable', 'date'],
            'resolved_at' => ['nullable', 'date'],
        ];
    }

    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        $asset = Asset::find($validated['asset_id']);

        $validated['location_id'] = $validated['location_id'] ?? $asset?->current_location_id;
        $validated['severity'] = $validated['severity'] ?? DamageSeverity::Medium->value;
        $validated['status'] = $validated['status'] ?? DamageStatus::Reported->value;
        $validated['reported_at'] = $validated['reported_at'] ?? now();

        if (($validated['status'] ?? null) === DamageStatus::Resolved->value) {
            $validated['resolved_at'] = $validated['resolved_at'] ?? now();
        }

        return $validated;
    }
}
