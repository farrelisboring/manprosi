<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AssetMovementController;
use App\Http\Controllers\Api\AssetQrLabelController;
use App\Http\Controllers\Api\DamageReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('assets', AssetController::class);
Route::apiResource('damage-reports', DamageReportController::class);

Route::get('/assets/{asset}/movements', [AssetMovementController::class, 'index']);
Route::post('/assets/{asset}/movements', [AssetMovementController::class, 'store']);
Route::get('/asset-movements/{assetMovement}', [AssetMovementController::class, 'show']);

Route::post('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'store']);
Route::get('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'show']);
Route::patch('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'update']);
Route::delete('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'destroy']);
Route::get('/qr-labels/{qrCodeValue}', [AssetQrLabelController::class, 'resolve']);
