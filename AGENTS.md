# AGENTS.md

## Project Snapshot

Laravel 13 app that is still light on application code, but no longer just a stock scaffold. The main custom work so far is in the database layer for a hospital asset-tracking system.

## Stack

- PHP `^8.3`
- Laravel `^13.0`
- Frontend build with Vite `^8`
- Tailwind CSS `^4` via `@tailwindcss/vite`
- PHPUnit `^12`

## Current App State

- `routes/web.php` still only defines `/` and returns the default `welcome` view.
- `resources/views/welcome.blade.php` is still the stock Laravel welcome page.
- `app/` is still nearly untouched: only the base controller, `User` model, and empty `AppServiceProvider` are present.
- `resources/js/app.js` is effectively empty.
- `resources/css/app.css` is the default Tailwind v4 entry file.
- `tests/` still only contains the default example feature/unit tests.
- The main implemented product work so far is schema design and migrations, not routes/controllers/views yet.

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
- The `User` model has not yet been updated to reflect the new `role` column in code; only the migration exists so far.

## Tooling / Workflow Hints

- `composer setup` installs PHP + Node deps, creates `.env` if needed, generates an app key, runs migrations, and builds frontend assets.
- `composer dev` runs the normal local stack: Laravel server, queue listener, log tailing with Pail, and Vite.
- Vendor dependencies are already present, so `composer install` has likely been run at least once.
- `php artisan migrate` succeeds against the current schema.
- `php artisan test` currently passes, but only the default bootstrap-level tests exist.

## Likely Direction

This now looks like a backend-first starting point for a hospital asset-tracking app. A future agent can safely assume:

- the database/domain model exists, but Eloquent models, policies, seed data, and workflows are mostly not built yet
- there is no custom routing structure yet
- there is no frontend architecture yet beyond Vite + Tailwind
- tests currently verify framework bootstrapping more than business behavior
- the next likely layer is app code on top of the new schema: models, relations, factories, seeders, CRUD flows, and reporting/tracking UI

## Good First Read Files

- `composer.json`
- `package.json`
- `routes/web.php`
- `app/Models/User.php`
- `database/migrations/2026_05_04_000007_create_assets_table.php`
- `database/migrations/2026_05_04_000008_create_asset_movements_table.php`
- `database/migrations/2026_05_04_000009_create_asset_tracking_events_table.php`
- `database/migrations/2026_05_04_000012_create_damage_reports_table.php`
- `database/seeders/DatabaseSeeder.php`
