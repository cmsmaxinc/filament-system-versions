# CLAUDE.md

Guidance for Claude Code when working in this repository.

## What this is

`cmsmaxinc/filament-system-versions` — a Filament plugin (published on Packagist) that adds a "System Versions" page and widgets to a Filament panel: environment/PHP/Laravel info, version stats, and a list of outdated Composer dependencies. Supports Filament `^4.0 | ^5.0`, PHP `^8.2`. Built on `spatie/laravel-package-tools`.

## Commands

```bash
composer test          # Pest test suite (via Orchestra Testbench)
composer format        # Laravel Pint
```

There is no Laravel app here — it's a package. To see changes rendered, point a consuming app's `composer.json` at this repo as a `path` repository and register the plugin in a panel.

## Architecture

- `src/FilamentSystemVersionsServiceProvider.php` — package wiring: views (`filament-system-versions::` namespace), translations, config, migration stub, Livewire component registration, and CSS asset registration via `FilamentAsset`.
- `src/FilamentSystemVersionsPlugin.php` — the Filament plugin class consumers register on a panel. Fluent config (`navigationLabel()`, `navigationGroup()`, `authorize()`, `statsPackages()`, …). Note: `boot()` pushes navigation settings onto the `SystemVersions` page via reflection, because the page's navigation properties are static.
- `src/Filament/Pages/SystemVersions.php` — the panel page; access gated by `FilamentSystemVersionsPlugin::get()->isAuthorized()`.
- `src/Filament/Widgets/` — `SystemInfoWidget` (environment/PHP/Laravel), `DependencyWidget` (outdated packages), `SystemVersionStats` + `DependencyStat` (stats overview). `SystemVersionStats` is `$isDiscovered = false` — only rendered where explicitly used.
- `src/Commands/CheckDependencyVersions.php` — `php artisan dependency:versions` runs `composer show --latest --format=json` and stores results in the `composer_versions` table (name configurable via config). The dependency widgets read from that table, so the command must run at least once before they show data.
- `resources/views/filament/` — Blade views for the page and widgets.
- `resources/lang/*/system-versions.php` — translations; keys referenced as `filament-system-versions::system-versions.*`.

## Styling — important constraint

**Do not use Tailwind utility classes in this package's Blade views.** Consuming apps compile their own Filament theme and purge any class not found in their content sources, so utilities written here (e.g. `flex justify-between`) silently disappear in production apps and the layout collapses. This bug shipped once already.

Instead, the package ships a hand-written plain stylesheet at `resources/css/filament-system-versions.css`, registered in the service provider's `getAssets()` via `Css::make(...)` and loaded by Filament on every panel page. Conventions in that file:

- All classes are prefixed `fsv-` (e.g. `fsv-row`, `fsv-label`, `fsv-value`, `fsv-grid`).
- Keep values theme-agnostic so light/dark both work without variants: muted text via `opacity`, hairlines/backgrounds via low-alpha `rgba(128,128,128, …)`. Don't hardcode light-only or dark-only colors, and don't rely on Filament's internal `fi-*` classes (they change between major versions).
- Filament core Blade components (`<x-filament::section>`, `<x-filament::badge>`, `<x-filament::icon>`) are safe to use — their styles ship with Filament itself.

Consumers get the stylesheet through `php artisan filament:assets` (normally run automatically by `filament:upgrade` on composer dump-autoload).

The `package.json` Tailwind build scripts (`build:styles` → `resources/dist/`) are legacy scaffolding from the plugin skeleton and are not part of the current styling pipeline; `resources/dist/` is empty on purpose.

## Conventions

- Follows Laravel Pint (run `composer format` before committing; CI also runs it — see "Fix styling" commits from the bot).
- Config file: `config/filament-system-versions.php` (table name, `show_direct_only`, php/composer binary paths).
- The migration ships as a stub (`database/migrations/create_composer_versions_table.php.stub`) published via the install command.
