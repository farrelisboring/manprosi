<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteAssetQrLabelRequest;
use App\Http\Requests\RegenerateAssetQrLabelRequest;
use App\Http\Resources\AssetQrLabelResource;
use App\Models\Asset;
use App\Services\QrCodeValueGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AssetQrLabelController extends Controller
{
    public function store(Asset $asset, QrCodeValueGenerator $generator): AssetQrLabelResource|JsonResponse
    {
        if ($asset->qr_code_value !== null) {
            return response()->json([
                'message' => 'Asset already has a QR label.',
            ], Response::HTTP_CONFLICT);
        }

        $asset->forceFill([
            'qr_code_value' => $generator->generate(),
        ])->save();

        return AssetQrLabelResource::make($this->loadLabelContext($asset->refresh()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Asset $asset): AssetQrLabelResource
    {
        if ($asset->qr_code_value === null) {
            abort(Response::HTTP_NOT_FOUND, 'Asset does not have a QR label.');
        }

        return AssetQrLabelResource::make($this->loadLabelContext($asset));
    }

    public function update(
        RegenerateAssetQrLabelRequest $request,
        Asset $asset,
        QrCodeValueGenerator $generator,
    ): AssetQrLabelResource {
        if ($asset->qr_code_value === null) {
            abort(Response::HTTP_NOT_FOUND, 'Asset does not have a QR label.');
        }

        $asset->forceFill([
            'qr_code_value' => $generator->generate(),
        ])->save();

        return AssetQrLabelResource::make($this->loadLabelContext($asset->refresh()));
    }

    public function destroy(DeleteAssetQrLabelRequest $request, Asset $asset): Response
    {
        if ($asset->qr_code_value === null) {
            abort(Response::HTTP_NOT_FOUND, 'Asset does not have a QR label.');
        }

        $asset->forceFill([
            'qr_code_value' => null,
        ])->save();

        return response()->noContent();
    }

    public function resolve(Request $request, string $qrCodeValue): AssetQrLabelResource
    {
        $normalizedQrCodeValue = QrCodeValueGenerator::normalize($qrCodeValue);

        Validator::make([
            'qr_code_value' => $normalizedQrCodeValue,
        ], [
            'qr_code_value' => ['required', ...QrCodeValueGenerator::validationRules()],
        ])->validate();

        $asset = Asset::query()
            ->where('qr_code_value', $normalizedQrCodeValue)
            ->first();

        if (! $asset) {
            abort(Response::HTTP_NOT_FOUND, 'QR label was not found.');
        }

        return AssetQrLabelResource::make($this->loadLabelContext($asset));
    }

    private function loadLabelContext(Asset $asset): Asset
    {
        return $asset->load(['category', 'currentLocation', 'currentMap']);
    }
}
