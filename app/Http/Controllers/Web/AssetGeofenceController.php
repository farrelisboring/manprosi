<?php

namespace App\Http\Controllers\Web;

use App\Enums\AlertType;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAlert;
use Illuminate\Contracts\View\View;

class AssetGeofenceController extends Controller
{
    public function show(Asset $asset): View
    {
        $asset = $asset->load(['category']);

        return view('assets.geofence.show', [
            'asset' => $asset,
            'alerts' => AssetAlert::query()
                ->whereBelongsTo($asset)
                ->where('alert_type', AlertType::GeofenceBreach->value)
                ->with(['geofence', 'location'])
                ->recentFirst()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }
}
