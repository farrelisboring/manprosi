<?php

namespace App\Services;

use App\Enums\DamageSeverity;
use App\Enums\DamageStatus;
use App\Models\Asset;
use App\Models\DamageReport;
use Carbon\CarbonInterface;

class DamageReportWorkflow
{
    public function prepareForStore(array $validated): array
    {
        $asset = Asset::query()->find($validated['asset_id']);

        $validated['location_id'] = $validated['location_id'] ?? $asset?->current_location_id;
        $validated['severity'] = $validated['severity'] ?? DamageSeverity::Medium->value;
        $validated['status'] = $validated['status'] ?? DamageStatus::Reported->value;
        $validated['reported_at'] = $validated['reported_at'] ?? now();

        if ($validated['status'] === DamageStatus::Resolved->value) {
            $validated['resolved_at'] = $validated['resolved_at'] ?? $validated['reported_at'];
        }

        return $validated;
    }

    public function prepareForUpdate(DamageReport $damageReport, array $validated): array
    {
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

    public function synchronizeStatusFromRepairUpdate(
        DamageReport $damageReport,
        ?string $statusAfter,
        CarbonInterface|string|null $loggedAt = null,
    ): array {
        if (! filled($statusAfter)) {
            return [];
        }

        if ($statusAfter === DamageStatus::Resolved->value) {
            return [
                'status' => $statusAfter,
                'resolved_at' => $damageReport->resolved_at ?? $loggedAt ?? now(),
            ];
        }

        return [
            'status' => $statusAfter,
            'resolved_at' => null,
        ];
    }
}
