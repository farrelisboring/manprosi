<?php

use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\AssetMovementController;
use App\Http\Controllers\Web\AssetQrLabelController;
use App\Http\Controllers\Web\AssetQrLabelRedirectController;
use App\Http\Controllers\Web\AssetSearchController;
use App\Http\Controllers\Web\DamageReportController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\LocationAssetController;
use App\Http\Controllers\Web\LocationMapController;
use App\Http\Controllers\Web\RepairUpdateController;
use App\Http\Controllers\Web\AuthController;
use App\Services\QrCodeValueGenerator;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    
    // --- FITUR BERSAMA (Bisa diakses Staff, Manajer, & Perawat) ---
    // Karena semua role punya User Story: mencari aset, melihat denah, melapor kerusakan, dll.
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/assets/search', AssetSearchController::class)->name('web.asset-search.index');
    Route::get('/locations/assets', [LocationAssetController::class, 'index'])->name('web.location-assets.index');
    Route::get('/locations/assets/panel', [LocationAssetController::class, 'refresh'])->name('web.location-assets.refresh');
    Route::get('/assets/{asset}/tracking', [AssetMovementController::class, 'show'])->name('web.assets.tracking.show');
    Route::get('/assets/{asset}/tracking/panel', [AssetMovementController::class, 'refresh'])->name('web.assets.tracking.refresh');
    Route::resource('damage-reports', DamageReportController::class)->names('web.damage-reports');
    
    // Staff & Manajer sama-sama bisa Kelola Data Aset (CRUD)
    Route::resource('assets', AssetController::class)->names('web.assets'); 
    Route::resource('location-maps', LocationMapController::class)->names('web.location-maps');
    Route::resource('locations', LocationController::class)->names('web.locations');


    // --- 🛡️ HAK AKSES KHUSUS STAFF ---
    Route::middleware('role:staff')->group(function () {
        // Staf membuat label QR Code/Barcode
        Route::post('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'store'])->name('web.assets.qr-label.store');
        Route::patch('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'update'])->name('web.assets.qr-label.update');
        Route::delete('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'destroy'])->name('web.assets.qr-label.destroy');
    });

    // --- 🛡️ HAK AKSES KHUSUS MANAJER ---
    Route::middleware('role:manager')->group(function () {
        // Manajer memperbarui hasil perbaikan aset
        Route::post('/damage-reports/{damageReport}/repair-updates', [RepairUpdateController::class, 'store'])->name('web.damage-reports.repair-updates.store');
    });

    // --- 🛡️ HAK AKSES KHUSUS PERAWAT ---
    Route::middleware('role:nurse')->group(function () {
        Route::get('/assets/{asset}/movements/create', [AssetMovementController::class, 'create'])->name('web.assets.movements.create');
        Route::post('/assets/{asset}/movements', [AssetMovementController::class, 'store'])->name('web.assets.movements.store');
    });

    Route::get('/{qrCodeValue}', AssetQrLabelRedirectController::class)
        ->where('qrCodeValue', QrCodeValueGenerator::routePattern())
        ->name('web.qr-labels.redirect');
});