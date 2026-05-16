<?php

namespace App\Http\Requests;

use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use App\Services\DamageReportWorkflow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDamageReportRequest extends FormRequest
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
        return app(DamageReportWorkflow::class)->prepareForStore($this->validated());
    }
}
