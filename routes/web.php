<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\AssetGeofenceController;
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
use App\Services\QrCodeValueGenerator;
use Illuminate\Support\Facades\Route;


Route::get('/debug-manifest', function () {
    $path = public_path('build/manifest.json');

    return response()->json([
        'exists' => file_exists($path),
        'path' => $path,
        'md5' => file_exists($path) ? md5_file($path) : null,
        'manifest' => file_exists($path)
            ? json_decode(file_get_contents($path), true)
            : null,
    ]);
});


Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:'.UserRole::Staff->value.','.UserRole::Manager->value.','.UserRole::Nurse->value])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/damage-reports', [DamageReportController::class, 'index'])->name('web.damage-reports.index');
    Route::get('/assets', [AssetController::class, 'index'])->name('web.assets.index');
    Route::get('/location-maps', [LocationMapController::class, 'index'])->name('web.location-maps.index');
});

Route::middleware(['auth', 'role:'.UserRole::Staff->value.','.UserRole::Nurse->value])->group(function (): void {
    Route::get('/assets/search', AssetSearchController::class)->name('web.asset-search.index');
    Route::get('/damage-reports/create', [DamageReportController::class, 'create'])->name('web.damage-reports.create');
    Route::post('/damage-reports', [DamageReportController::class, 'store'])->name('web.damage-reports.store');
    Route::get('/locations/assets', [LocationAssetController::class, 'index'])->name('web.location-assets.index');
    Route::get('/locations/assets/panel', [LocationAssetController::class, 'refresh'])->name('web.location-assets.refresh');
});

Route::middleware(['auth', 'role:'.UserRole::Manager->value])->group(function (): void {
    Route::get('/damage-reports/{damageReport}/edit', [DamageReportController::class, 'edit'])->name('web.damage-reports.edit');
    Route::match(['put', 'patch'], '/damage-reports/{damageReport}', [DamageReportController::class, 'update'])->name('web.damage-reports.update');
    Route::delete('/damage-reports/{damageReport}', [DamageReportController::class, 'destroy'])->name('web.damage-reports.destroy');
    Route::post('/damage-reports/{damageReport}/repair-updates', [RepairUpdateController::class, 'store'])->name('web.damage-reports.repair-updates.store');

    Route::get('/locations', [LocationController::class, 'index'])->name('web.locations.index');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('web.locations.create');
    Route::post('/locations', [LocationController::class, 'store'])->name('web.locations.store');
    Route::get('/locations/{location}/edit', [LocationController::class, 'edit'])->name('web.locations.edit');
    Route::match(['put', 'patch'], '/locations/{location}', [LocationController::class, 'update'])->name('web.locations.update');
    Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('web.locations.destroy');

    Route::get('/location-maps/create', [LocationMapController::class, 'create'])->name('web.location-maps.create');
    Route::post('/location-maps', [LocationMapController::class, 'store'])->name('web.location-maps.store');
    Route::get('/location-maps/{locationMap}/edit', [LocationMapController::class, 'edit'])->name('web.location-maps.edit');
    Route::match(['put', 'patch'], '/location-maps/{locationMap}', [LocationMapController::class, 'update'])->name('web.location-maps.update');
    Route::delete('/location-maps/{locationMap}', [LocationMapController::class, 'destroy'])->name('web.location-maps.destroy');
});

Route::middleware(['auth', 'role:'.UserRole::Staff->value.','.UserRole::Manager->value])->group(function (): void {
    Route::get('/assets/create', [AssetController::class, 'create'])->name('web.assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->name('web.assets.store');
    Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->name('web.assets.edit');
    Route::match(['put', 'patch'], '/assets/{asset}', [AssetController::class, 'update'])->name('web.assets.update');
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('web.assets.destroy');
});

Route::middleware(['auth', 'role:'.UserRole::Staff->value])->group(function (): void {
    Route::post('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'store'])->name('web.assets.qr-label.store');
    Route::patch('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'update'])->name('web.assets.qr-label.update');
    Route::delete('/assets/{asset}/qr-label', [AssetQrLabelController::class, 'destroy'])->name('web.assets.qr-label.destroy');
    Route::get('/assets/{asset}/tracking', [AssetMovementController::class, 'show'])->name('web.assets.tracking.show');
    Route::get('/assets/{asset}/tracking/panel', [AssetMovementController::class, 'refresh'])->name('web.assets.tracking.refresh');
    Route::get('/assets/{asset}/geofence', [AssetGeofenceController::class, 'show'])->name('web.assets.geofence.show');
    Route::get('/{qrCodeValue}', AssetQrLabelRedirectController::class)
        ->where('qrCodeValue', QrCodeValueGenerator::routePattern())
        ->name('web.qr-labels.redirect');
});

Route::middleware(['auth', 'role:'.UserRole::Nurse->value])->group(function (): void {
    Route::get('/assets/{asset}/movements/create', [AssetMovementController::class, 'create'])->name('web.assets.movements.create');
    Route::post('/assets/{asset}/movements', [AssetMovementController::class, 'store'])->name('web.assets.movements.store');
});

Route::middleware(['auth', 'role:'.UserRole::Staff->value.','.UserRole::Manager->value.','.UserRole::Nurse->value])->group(function (): void {
    Route::get('/damage-reports/{damageReport}', [DamageReportController::class, 'show'])->name('web.damage-reports.show');
    Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('web.assets.show');
    Route::get('/location-maps/{locationMap}', [LocationMapController::class, 'show'])->name('web.location-maps.show');
    Route::get('/locations/{location}/denah', [LocationController::class, 'denah'])->name('web.locations.denah');
    Route::get('/locations/{location}', [LocationController::class, 'show'])->name('web.locations.show');
});
