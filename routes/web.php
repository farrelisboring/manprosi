<?php

use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\AssetMovementController;
use App\Http\Controllers\Web\AssetQrLabelController;
use App\Http\Controllers\Web\AssetQrLabelRedirectController;
use App\Http\Controllers\Web\AssetSearchController;
use App\Http\Controllers\Web\DamageReportController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LocationAssetController;
use App\Http\Controllers\Web\RepairUpdateController;
use App\Services\QrCodeValueGenerator;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/assets/search', AssetSearchController::class)->name('web.asset-search.index');
Route::resource('damage-reports', DamageReportController::class)->names('web.damage-reports');
Route::post('/damage-reports/{damageReport}/repair-updates', [RepairUpdateController::class, 'store'])->name('web.damage-reports.repair-updates.store');
Route::resource('assets', AssetController::class)->names('web.assets');
Route::get('/locations/assets', [LocationAssetController::class, 'index'])->name('web.location-assets.index');
Route::get('/locations/assets/panel', [LocationAssetController::class, 'refresh'])->name('web.location-assets.refresh');

Route::post('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'store'])->name('web.assets.qr-label.store');
Route::patch('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'update'])->name('web.assets.qr-label.update');
Route::delete('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'destroy'])->name('web.assets.qr-label.destroy');
Route::get('/assets/{asset}/tracking', [AssetMovementController::class, 'show'])->name('web.assets.tracking.show');
Route::get('/assets/{asset}/tracking/panel', [AssetMovementController::class, 'refresh'])->name('web.assets.tracking.refresh');
Route::get('/assets/{asset}/movements/create', [AssetMovementController::class, 'create'])->name('web.assets.movements.create');
Route::post('/assets/{asset}/movements', [AssetMovementController::class, 'store'])->name('web.assets.movements.store');

Route::get('/{qrCodeValue}', AssetQrLabelRedirectController::class)
    ->where('qrCodeValue', QrCodeValueGenerator::routePattern())
    ->name('web.qr-labels.redirect');
