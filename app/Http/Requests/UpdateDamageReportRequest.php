<?php

namespace App\Http\Requests;

use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use App\Models\DamageReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDamageReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id' => ['sometimes', 'required', 'integer', Rule::exists('assets', 'id')],
            'reported_by_user_id' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')],
            'location_id' => ['sometimes', 'nullable', 'integer', Rule::exists('locations', 'id')],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'severity' => ['sometimes', Rule::enum(DamageSeverity::class)],
            'status' => ['sometimes', Rule::enum(DamageStatus::class)],
            'reported_at' => ['sometimes', 'nullable', 'date'],
            'resolved_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function validatedForUpdate(DamageReport $damageReport): array
    {
        $validated = $this->validated();

        if (! array_key_exists('status', $validated)) {
            return $validated;
        }

        if ($validated['status'] === DamageStatus::Resolved->value) {
            if (! array_key_exists('resolved_at', $validated) || $validated['resolved_at'] === null) {
                $validated['resolved_at'] = $damageReport->resolved_at ?? now();
            }

            return $validated;
        }

        if (! array_key_exists('resolved_at', $validated)) {
            $validated['resolved_at'] = null;
        }

        return $validated;
    }
}
