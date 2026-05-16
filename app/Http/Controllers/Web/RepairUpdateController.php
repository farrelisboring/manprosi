<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRepairUpdateRequest;
use App\Models\DamageReport;
use App\Services\RepairUpdateRecorder;
use Illuminate\Http\RedirectResponse;

class RepairUpdateController extends Controller
{
    public function store(
        StoreRepairUpdateRequest $request,
        DamageReport $damageReport,
        RepairUpdateRecorder $recorder,
    ): RedirectResponse {
        $recorder->record($damageReport, $request->validatedWithDefaults());

        return redirect()
            ->route('web.damage-reports.show', $damageReport)
            ->with('status_message', 'Repair update logged successfully.')
            ->with('status_type', 'success');
    }
}
