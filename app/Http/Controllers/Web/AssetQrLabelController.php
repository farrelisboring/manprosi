<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteAssetQrLabelRequest;
use App\Http\Requests\RegenerateAssetQrLabelRequest;
use App\Models\Asset;
use App\Services\QrCodeValueGenerator;
use Illuminate\Http\RedirectResponse;

class AssetQrLabelController extends Controller
{
    public function store(Asset $asset, QrCodeValueGenerator $generator): RedirectResponse
    {
        if ($asset->qr_code_value !== null) {
            return $this->redirectToAsset($asset, 'This asset already has a QR label.', 'error');
        }

        $asset->forceFill([
            'qr_code_value' => $generator->generate(),
        ])->save();

        return $this->redirectToAsset($asset->refresh(), 'QR label generated successfully.');
    }

    public function update(RegenerateAssetQrLabelRequest $request, Asset $asset, QrCodeValueGenerator $generator): RedirectResponse
    {
        if ($asset->qr_code_value === null) {
            return $this->redirectToAsset($asset, 'This asset does not have a QR label yet.', 'error');
        }

        $asset->forceFill([
            'qr_code_value' => $generator->generate(),
        ])->save();

        return $this->redirectToAsset($asset->refresh(), 'QR label regenerated successfully.');
    }

    public function destroy(DeleteAssetQrLabelRequest $request, Asset $asset): RedirectResponse
    {
        if ($asset->qr_code_value === null) {
            return $this->redirectToAsset($asset, 'This asset does not have a QR label to remove.', 'error');
        }

        $asset->forceFill([
            'qr_code_value' => null,
        ])->save();

        return $this->redirectToAsset($asset->refresh(), 'QR label deleted successfully.');
    }

    private function redirectToAsset(Asset $asset, string $message, string $type = 'success'): RedirectResponse
    {
        return redirect()
            ->route('web.assets.show', $asset)
            ->with('status_message', $message)
            ->with('status_type', $type);
    }
}
