<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AssetQrLabelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('assets', AssetController::class);

Route::post('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'store']);
Route::get('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'show']);
Route::patch('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'update']);
Route::delete('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'destroy']);
Route::get('/qr-labels/{qrCodeValue}', [AssetQrLabelController::class, 'resolve']);
