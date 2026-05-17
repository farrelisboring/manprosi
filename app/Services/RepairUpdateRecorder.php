<?php

namespace App\Services;

use App\Models\DamageReport;
use App\Models\RepairUpdate;
use Illuminate\Support\Facades\DB;

class RepairUpdateRecorder
{
    public function __construct(
        private readonly DamageReportWorkflow $damageReportWorkflow,
    ) {}

    public function record(DamageReport $damageReport, array $attributes): RepairUpdate
    {
        return DB::transaction(function () use ($damageReport, $attributes): RepairUpdate {
            $repairUpdate = $damageReport->repairUpdates()->create($attributes);

            $statusAttributes = $this->damageReportWorkflow->synchronizeStatusFromRepairUpdate(
                $damageReport,
                $attributes['status_after'] ?? null,
                $attributes['logged_at'] ?? null,
            );

            if ($statusAttributes !== []) {
                $damageReport->update($statusAttributes);
            }

            return $repairUpdate->refresh();
        });
    }
}
