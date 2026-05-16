<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Services\QrCodeValueGenerator;
use Illuminate\Http\RedirectResponse;

class AssetQrLabelRedirectController extends Controller
{
    public function __invoke(string $qrCodeValue): RedirectResponse
    {
        $asset = Asset::query()
            ->where('qr_code_value', QrCodeValueGenerator::normalize($qrCodeValue))
            ->firstOrFail();

        return redirect()->route('web.assets.show', $asset);
    }
}
