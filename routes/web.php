<?php

use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\AssetMovementController;
use App\Http\Controllers\Web\AssetQrLabelController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::resource('assets', AssetController::class)->names('web.assets');

Route::post('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'store'])->name('web.assets.qr-label.store');
Route::patch('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'update'])->name('web.assets.qr-label.update');
Route::delete('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'destroy'])->name('web.assets.qr-label.destroy');
Route::get('/assets/{asset}/tracking', [AssetMovementController::class, 'show'])->name('web.assets.tracking.show');
Route::get('/assets/{asset}/tracking/panel', [AssetMovementController::class, 'refresh'])->name('web.assets.tracking.refresh');
Route::get('/assets/{asset}/movements/create', [AssetMovementController::class, 'create'])->name('web.assets.movements.create');
Route::post('/assets/{asset}/movements', [AssetMovementController::class, 'store'])->name('web.assets.movements.store');
