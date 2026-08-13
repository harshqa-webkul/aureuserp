# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Aureus ERP: a Laravel 13 + FilamentPHP 5 (Livewire 4) open-source ERP. The app shell in `app/` is thin — almost all business functionality lives in first-party plugins under `plugins/webkul/*`, each an independently installable Composer package wired together by a shared plugin-manager package.

## Commands

### PHP / backend
```bash
composer install
php artisan erp:install          # fresh install: migrates, seeds, sets up Shield roles/permissions, creates admin
vendor/bin/pint --dirty          # format only changed files before finishing any PHP change
php artisan test                 # or: vendor/bin/pest
php artisan test --filter=testName
php artisan test plugins/webkul/inventories/tests/Feature   # a single plugin's suite
```
Named PHPUnit/Pest suites are declared in `phpunit.xml` (one per core plugin, e.g. `InventoryFeature`, `SaleFeature`) — CI runs `vendor/bin/pest`. Tests for a plugin live in `plugins/webkul/<plugin>/tests/`, following that plugin's own PSR-4 namespace (`Webkul\<Plugin>\Tests\`).

### Frontend assets
```bash
npm run dev      # vite dev server
npm run build
composer run dev # runs php artisan serve + queue:listen + pail + vite concurrently
```
If a frontend change isn't showing up, it's almost always a missing `npm run build`/`npm run dev`.

### Translations
```bash
php artisan translations:check --details   # CI gate (translations_check.yml); checks keys/structure only, not value quality
```
See memory `translation-value-quality-gap` / `es-locale-translation-workflow` for the value-quality gap this check misses and the recurring master-merge delta pattern.

### E2E (Playwright)
Lives in `tests/e2e-pw/` as its own npm project (separate `package.json`/`node_modules` from the root).
```bash
cd tests/e2e-pw
npx playwright test                       # full suite, 1 worker locally (fullyParallel only on CI)
npx playwright test tests/05_inventories  # a single plugin's spec folder
npx playwright test --ui
```
- `baseURL` defaults to `http://127.0.0.1:8000` (`BASE_URL` env override) — the Laravel dev server must already be running.
- Auth state is cached to `tests/e2e-pw/.state/admin-auth.json`; `setup.ts` reuses it across tests and only re-logs-in via `utils/admin.ts` if the session is stale (see memory `e2e-stale-auth-state` for why probing `/admin/dashboard` is the wrong staleness check).
- Spec folders are numbered by dependency order (`01_plugins`, `02_companies`, `03_users`, `04_sales`, `05_inventories`, `05_purchases`, `06_website`) because later suites assume earlier ones' fixtures/plugins exist.
- See memory `e2e-parallel-safe-rules` before adding new specs meant to run under `workers=4`.

## Architecture

### Plugin system
Every business module (`accounting`, `sales`, `purchases`, `inventories`, `products`, `manufacturing`, `hr`-ish plugins like `employees`/`recruitments`/`time-off`, `contacts`, `partners`, `projects`, `website`, `blogs`, `chatter`, `security`, etc.) is a standalone Composer package under `plugins/webkul/<name>/`, structured like a mini-Laravel app:
```
plugins/webkul/<name>/
  src/
    <Name>ServiceProvider.php   # extends Webkul\PluginManager\PackageServiceProvider
    <Name>Plugin.php            # implements Filament\Contracts\Plugin — registers Resources/Pages/Clusters/Widgets
    <Name>Manager.php           # optional facade-backed singleton
    Models/ Filament/ Http/ Observers/ Policies/ Enums/ Events/ Settings/ Services/
  database/{migrations,factories,seeders,settings}/
  resources/{views,lang/{en,es,ar,pt_BR,...}}/
  routes/
  tests/{Feature,Helpers}/
  composer.json   # declares its own Laravel service provider + PSR-4 autoload (Webkul\<Name>\...)
```
Plugin providers are registered centrally in `bootstrap/providers.php`; each plugin's `composer.json` is pulled into the root autoloader via `wikimedia/composer-merge-plugin` (`plugins/*/*/composer.json`, see root `composer.json`).

### Install/uninstall lifecycle (`Webkul\PluginManager`)
`plugins/webkul/plugin-manager` is the framework the other plugins build on:
- `Package` (extends Spatie's package-tools `Package`) adds ERP-specific fluent config: `hasDependencies()`, `hasSettings()`/`runsSettings()`, `hasSeeder()`/`runsSeeders()`, `hasInstallCommand()`/`hasUninstallCommand()`, `icon()`.
- A plugin's `ServiceProvider::configureCustomPackage()` declares its full migration list explicitly (not glob-discovered), its settings migrations, its seeder, its plugin dependencies (e.g. `inventories` depends on `products`), and install/uninstall behavior (uninstall typically truncates its own tables and purges Chatter logs for its models).
- `Package::isPluginInstalled($name)` gates everything at runtime — Filament panel registration, model observers, product-schema contributions all check this before doing anything, because plugins can be installed/uninstalled at runtime (not just at deploy time) via the in-app Plugin Manager UI.
- `php artisan erp:install` (`InstallERP` command in this package) drives first-time setup end-to-end.

### Cross-plugin extension points
Plugins don't modify each other's code directly; they extend shared models/schemas through registries so install order and optional plugins both work:
- `Webkul\Product\Models\Product` exposes `contributeFillable()` / `contributeCasts()` / `resolveRelationUsing()` — plugins like `inventories` add fields/relations to the shared `Product` model this way instead of subclassing it.
- `ProductSchemaRegistry` (`form`, `infolist`, `table`, `actions`, `presetView`, `eagerLoad`) lets a plugin inject its own Filament form sections/columns/actions into another plugin's resource UI (e.g. Inventory adds an "on hand"/"forecasted" column to the Product resource table).
- Model `Observer` classes are registered conditionally inside `packageBooted()`, guarded by `Package::isPluginInstalled()`.
This pattern (registry-based contribution + install-gated wiring) is the thing to look for first when a feature needs to touch another plugin's model or resource.

### Filament panels
`app/Providers/Filament/AdminPanelProvider.php` (`/admin`) and `CustomerPanelProvider.php` define the two panels. Each plugin's `<Name>Plugin.php` registers itself into the `admin` panel only when `Package::isPluginInstalled()`, discovering its own `Filament/{Resources,Pages,Clusters,Widgets}` directories. Navigation groups come from the `Webkul\Support\Enums\NavigationGroup` enum — a plugin's `navigation.group` config must match one of these exactly or the menu item silently disappears (see memory `es-locale-nav-group-matching`).

### Multi-language
Each plugin ships its own `resources/lang/{en,es,ar,pt_BR}` bundle rather than a single root translation set; `php artisan translations:check` only validates structural parity across locales, not translation quality — see the translation memories above before trusting a green check.

## Notes for this user (QA/e2e context)

Per project memory: this repo is primarily worked on here through Playwright e2e authoring/debugging against the plugin flows (inventory, purchase, sales, plugin install). When touching a plugin's Filament resource/action, check the corresponding `tests/e2e-pw/tests/0X_<plugin>/` spec and the relevant memory entries (inventory tracking/backorder/putaway/lots, purchase↔inventory, sales↔inventory, move-row matching, parallel-safe rules) before assuming current e2e behavior — several of these document non-obvious app quirks (e.g. Livewire hydration races, redirect-only confirmations with no toast) rather than test bugs.
