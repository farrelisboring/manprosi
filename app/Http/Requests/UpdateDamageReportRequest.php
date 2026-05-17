<?php

namespace App\Http\Requests;

use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use App\Models\DamageReport;
use App\Services\DamageReportWorkflow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDamageReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['reported_at', 'resolved_at'] as $field) {
            if (! $this->filled($field)) {
                continue;
            }

            $this->merge([
                $field => str_replace('T', ' ', (string) $this->input($field)),
            ]);
        }
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
        return app(DamageReportWorkflow::class)->prepareForUpdate($damageReport, $this->validated());
    }
}
