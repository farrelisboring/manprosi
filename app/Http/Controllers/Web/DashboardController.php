<?php

namespace App\Http\Controllers\Web;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $statusCounts = Asset::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('dashboard.index', [
            'totalAssets' => Asset::count(),
            'statusCounts' => [
                AssetStatus::Available->value => (int) ($statusCounts[AssetStatus::Available->value] ?? 0),
                AssetStatus::InUse->value => (int) ($statusCounts[AssetStatus::InUse->value] ?? 0),
                AssetStatus::Maintenance->value => (int) ($statusCounts[AssetStatus::Maintenance->value] ?? 0),
            ],
            'recentAssets' => Asset::query()
                ->with(['category', 'currentLocation'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
