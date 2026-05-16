<?php

namespace App\Http\Requests;

use App\Enums\DamageStatus;
use App\Enums\RepairUpdateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRepairUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('logged_at')) {
            return;
        }

        $this->merge([
            'logged_at' => str_replace('T', ' ', (string) $this->input('logged_at')),
        ]);
    }

    public function rules(): array
    {
        return [
            'updated_by_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'update_type' => ['nullable', Rule::enum(RepairUpdateType::class)],
            'status_after' => ['nullable', Rule::enum(DamageStatus::class)],
            'result_summary' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string'],
            'logged_at' => ['required', 'date'],
        ];
    }

    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        $validated['update_type'] = $validated['update_type'] ?? RepairUpdateType::Note->value;
        $validated['logged_at'] = $validated['logged_at'] ?? now();

        return $validated;
    }
}
