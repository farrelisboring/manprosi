<?php

use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\AssetQrLabelController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::resource('assets', AssetController::class)->names('web.assets');

Route::post('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'store'])->name('web.assets.qr-label.store');
Route::patch('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'update'])->name('web.assets.qr-label.update');
Route::delete('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'destroy'])->name('web.assets.qr-label.destroy');
