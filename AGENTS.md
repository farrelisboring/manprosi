# AGENTS.md

## High-level overview

Proyek ini adalah aplikasi pelacakan aset rumah sakit sederhana yang dibangun dengan Laravel 13. Proyek ini menyediakan API backend untuk manajemen aset (CRUD), pembuatan dan pengelolaan label QR, pencatatan riwayat pemindahan aset, serta pelaporan kerusakan. Fokus milestone ini adalah pada desain skema basis data, model Eloquent, dan API; antarmuka pengguna masih berupa scaffold dasar.

## Project Snapshot

Laravel 13 app for a homework-scale hospital asset-tracking system. The database schema, Eloquent domain model layer, asset CRUD REST API, QR label REST API, asset movement history REST API, and damage report REST API are now in place, while browser-facing views are still mostly stock scaffold.

## Stack

- PHP `^8.3`
- Laravel `^13.0`
- Frontend build with Vite `^8`
- Tailwind CSS `^4` via `@tailwindcss/vite`
- PHPUnit `^12`

## Current App State

- `routes/web.php` still only defines `/` and returns the default `welcome` view.
- `routes/api.php` now exposes asset CRUD endpoints, QR label endpoints, asset movement history endpoints, and damage report CRUD endpoints.
- `resources/views/welcome.blade.php` is still the stock Laravel welcome page.
- `app/Models` now contains the hospital asset-tracking Eloquent models.
- `app/Enums` now contains PHP backed enums for role, status, source, type, severity, and geofence fields.
- `app/Http` now has the asset API controller, request validation classes, and JSON resource for asset responses.
- `app/Services/QrCodeValueGenerator.php` generates unique UUID values for QR labels.
- `resources/js/app.js` is effectively empty.
- `resources/css/app.css` is the default Tailwind v4 entry file.
- `tests/Feature/ModelLayerTest.php` verifies the model-layer relationships, casts, helpers, and query scopes.
- `tests/Feature/AssetCrudApiTest.php` verifies asset CRUD API behavior, validation, filters, JSON shape, and soft deletes.
- `tests/Feature/AssetQrLabelApiTest.php` verifies QR label generation, lookup, regeneration, deletion, validation, and conflict behavior.
- `tests/Feature/AssetMovementApiTest.php` verifies movement creation, current-location sync, map placement sync/clearing, history listing/filtering, validation, and single movement reads.
- `tests/Feature/DamageReportApiTest.php` verifies damage report CRUD behavior, filters, status resolution behavior, repair-update read context, validation, and hard-delete cascade behavior.
- The main implemented product work so far is schema design, Eloquent models, asset CRUD API endpoints, QR label API endpoints, movement history API endpoints, and damage report API endpoints.

## Data Layer

Default Laravel tables still exist for:

- users / password reset tokens / sessions
- cache
- jobs / job batches / failed jobs

Custom schema has been added for the hospital asset-tracking domain:

- `users.role` for simple app roles: `staff`, `manager`, `nurse`
- `asset_categories`
- `locations` as a hierarchical location tree
- `location_maps` for digital floor-plan metadata
- `assets` with QR/barcode/RFID fields and current placement
- `asset_movements` for official transfer history
- `asset_tracking_events` for raw/near-real-time detections
- `asset_geofences`
- `asset_alerts`
- `damage_reports`
- `repair_updates`

`DatabaseSeeder` still only seeds a single default `Test User`.

## Model Layer

Eloquent models now exist for:

- `AssetCategory`
- `Location`
- `LocationMap`
- `Asset`
- `AssetMovement`
- `AssetTrackingEvent`
- `AssetGeofence`
- `AssetAlert`
- `DamageReport`
- `RepairUpdate`

The existing `User` model now includes:

- `role` in fillable attributes
- `UserRole` enum casting
- role helpers: `isStaff()`, `isManager()`, `isNurse()`
- relationships for created/updated assets, recorded movements, acknowledged alerts, reported damage reports, and repair updates

Domain enums live in `app/Enums`:

- `UserRole`
- `AssetStatus`
- `MovementSource`
- `TrackingSource`
- `TrackingEventType`
- `GeofenceRuleType`
- `AlertType`
- `AlertStatus`
- `DamageSeverity`
- `DamageStatus`
- `RepairUpdateType`

Model implementation notes:

- Laravel 13-style `#[Fillable]` attributes are used instead of legacy `$fillable`.
- `AssetCategory`, `Location`, and `Asset` use soft deletes.
- String status/type columns are cast to PHP backed enums.
- Location hierarchy uses `parent` / `children`.
- Asset placement uses `currentLocation`, `currentMap`, `position_x`, and `position_y`.
- Movement history uses `fromLocation`, `toLocation`, and `movedByUser`.
- Alert, damage, and repair models keep nullable user/location/geofence/tracking-event relationships explicit.
- Models provide lightweight query scopes for search, status filters, room/location filters, active records, and recent-first ordering.

## Asset CRUD API

Asset CRUD endpoints are available under `/api/assets`:

- `GET /api/assets`
- `POST /api/assets`
- `GET /api/assets/{asset}`
- `PUT/PATCH /api/assets/{asset}`
- `DELETE /api/assets/{asset}`

Implementation notes:

- Controller: `App\Http\Controllers\Api\AssetController`
- Requests: `StoreAssetRequest` and `UpdateAssetRequest`
- Resource: `AssetResource`
- Responses are JSON API resources, not raw Eloquent models.
- Listing supports `search`, `category_id`, `current_location_id`, and `status` query filters.
- Create/update validation follows the existing migrations: required `asset_code`, `name`, and `category_id`; unique asset code/barcode/QR/RFID values; nullable location/map/coordinate fields; enum-backed status validation.
- Delete uses the existing `Asset` soft delete behavior and returns `204 No Content`.
- Authentication/authorization is intentionally not enforced on these endpoints yet.

## QR Label API

QR label endpoints are available under `/api/assets/{asset}/qr-label` and `/api/qr-labels/{qrCodeValue}`:

- `POST /api/assets/{asset}/qr-label`
- `GET /api/assets/{asset}/qr-label`
- `PATCH /api/assets/{asset}/qr-label`
- `DELETE /api/assets/{asset}/qr-label`
- `GET /api/qr-labels/{qrCodeValue}`

Implementation notes:

- Controller: `App\Http\Controllers\Api\AssetQrLabelController`
- Resource: `AssetQrLabelResource`
- Requests: `RegenerateAssetQrLabelRequest` and `DeleteAssetQrLabelRequest`
- Service: `QrCodeValueGenerator`
- QR values are UUID strings stored in the existing unique nullable `assets.qr_code_value` column.
- The backend returns only JSON UUID/context payloads; QR image rendering and print layout are left to the frontend.
- QR label responses include the UUID plus asset, category, current location, current map, label-state flags, and `updated_at`.
- Generating a QR label for an asset that already has one returns `409 Conflict`.
- Regeneration/deletion require confirmation fields because printed labels may become stale.
- Authentication/authorization is intentionally not enforced on these endpoints yet.

## Asset Movement History API

Movement history endpoints are available under `/api/assets/{asset}/movements` and `/api/asset-movements/{assetMovement}`:

- `GET /api/assets/{asset}/movements`
- `POST /api/assets/{asset}/movements`
- `GET /api/asset-movements/{assetMovement}`

Implementation notes:

- Controller: `App\Http\Controllers\Api\AssetMovementController`
- Request: `StoreAssetMovementRequest`
- Resource: `AssetMovementResource`
- Movement records are append-only for this milestone; there are no update/delete movement endpoints.
- Creating a movement writes an `asset_movements` row and updates `assets.current_location_id` in one database transaction.
- `from_location_id` is derived from the asset's current location before the move.
- If map placement is supplied, the asset's `current_map_id`, `position_x`, and `position_y` are updated; otherwise stale map placement is cleared.
- Movement history listing is paginated, newest-first, and supports `movement_source`, `from_location_id`, `to_location_id`, `date_from`, and `date_to` filters.
- Authentication/authorization is intentionally not enforced on these endpoints yet.

## Damage Report API

Damage report CRUD endpoints are available under `/api/damage-reports`:

- `GET /api/damage-reports`
- `POST /api/damage-reports`
- `GET /api/damage-reports/{damageReport}`
- `PUT/PATCH /api/damage-reports/{damageReport}`
- `DELETE /api/damage-reports/{damageReport}`

Implementation notes:

- Controller: `App\Http\Controllers\Api\DamageReportController`
- Requests: `StoreDamageReportRequest` and `UpdateDamageReportRequest`
- Resource: `DamageReportResource`
- Listing is paginated, newest-first by `reported_at`, and supports `asset_id`, `status`, `severity`, `location_id`, `reported_by_user_id`, `date_from`, and `date_to` filters.
- Creating a report requires `asset_id`, `title`, and `description`; location defaults from the asset's current location when omitted.
- `severity` defaults to `medium`; `status` defaults to `reported`.
- Updating status to `resolved` auto-fills `resolved_at` when omitted; changing away from `resolved` clears `resolved_at` unless explicitly supplied.
- Repair updates are exposed as read-only context in responses. Full repair update CRUD is intentionally left for a future manager follow-up workflow.
- Delete is a hard delete for this milestone; related `repair_updates` cascade-delete according to the current schema.
- Authentication/authorization is intentionally not enforced on these endpoints yet.

## Current Domain Shape

The schema is aimed at a homework-scale hospital asset management system that supports:

- asset CRUD and categorization
- room/area placement plus digital map coordinates
- QR code / barcode / RFID identification
- movement history and basic tracking events
- geofence alerts
- damage reporting and repair follow-up history

Implementation choices worth knowing:

- SQLite-friendly schema choices were used because the app defaults to SQLite.
- `assets`, `asset_categories`, and `locations` use soft deletes.
- Current asset state lives on `assets`, while history is split into dedicated log tables.

## Notable Laravel 13 Details

- The `User` model uses Laravel 13-style PHP attributes like `#[Fillable]` and `#[Hidden]` instead of the older `$fillable` / `$hidden` properties.
- The welcome page conditionally shows login/register links if those routes exist, but no auth scaffolding appears to be installed yet.
- Sanctum has been added to the dependency/migration/config layer, but asset CRUD endpoints currently remain unauthenticated for the deadline milestone.
- Tests configure the compiled Blade view path to `sys_get_temp_dir()` in `tests/TestCase.php` because the local environment had trouble writing compiled test views/log output in the default path.

## Tooling / Workflow Hints

- `composer setup` installs PHP + Node deps, creates `.env` if needed, generates an app key, runs migrations, and builds frontend assets.
- `composer dev` runs the normal local stack: Laravel server, queue listener, log tailing with Pail, and Vite.
- Vendor dependencies are already present, so `composer install` has likely been run at least once.
- `php artisan migrate` succeeds against the current schema.
- `php artisan test` currently passes: 30 tests, 394 assertions.

## Likely Direction

This now looks like a backend-first starting point for a hospital asset-tracking app. A future agent can safely assume:

- the database schema, Eloquent domain model layer, first asset CRUD API, QR label API, movement history API, and damage report API exist
- policies, seed data, richer workflows, and UI are mostly not built yet
- custom API routing has started with asset CRUD, but web routing is still stock
- there is no frontend architecture yet beyond Vite + Tailwind
- tests cover model behavior, asset API behavior, QR label API behavior, movement history API behavior, and damage report API behavior, but there are no UI tests yet
- the next likely layer is supporting data and workflows: factories, seeders, policies/auth, category/location APIs, repair update CRUD, tracking-event ingestion, printable-label UI, and reporting/tracking UI

## Good First Read Files

- `composer.json`
- `package.json`
- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers/Api/AssetController.php`
- `app/Http/Controllers/Api/AssetMovementController.php`
- `app/Http/Controllers/Api/AssetQrLabelController.php`
- `app/Http/Controllers/Api/DamageReportController.php`
- `app/Http/Requests/StoreAssetRequest.php`
- `app/Http/Requests/StoreAssetMovementRequest.php`
- `app/Http/Requests/StoreDamageReportRequest.php`
- `app/Http/Resources/AssetResource.php`
- `app/Http/Resources/AssetMovementResource.php`
- `app/Http/Resources/AssetQrLabelResource.php`
- `app/Http/Resources/DamageReportResource.php`
- `app/Services/QrCodeValueGenerator.php`
- `app/Models/User.php`
- `app/Models/Asset.php`
- `app/Models/Location.php`
- `app/Enums/AssetStatus.php`
- `tests/Feature/ModelLayerTest.php`
- `tests/Feature/AssetCrudApiTest.php`
- `tests/Feature/AssetMovementApiTest.php`
- `tests/Feature/AssetQrLabelApiTest.php`
- `tests/Feature/DamageReportApiTest.php`
- `database/migrations/2026_05_04_000007_create_assets_table.php`
- `database/migrations/2026_05_04_000008_create_asset_movements_table.php`
- `database/migrations/2026_05_04_000009_create_asset_tracking_events_table.php`
- `database/migrations/2026_05_04_000012_create_damage_reports_table.php`
- `database/seeders/DatabaseSeeder.php`
