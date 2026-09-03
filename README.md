# Filament System Versions

![Filament System Versions](https://github.com/cmsmaxinc/filament-system-versions/raw/main/thumbnail.jpg)

This package provides a comprehensive, administrator-facing technology inventory for Filament panels. It combines runtime versions, every installed Composer package, and every resolved npm package instance in one organized page.

## Features

- 📊 **System Versions Page** - A dedicated page displaying system information
- 🔍 **Complete Composer Inventory** - Show up-to-date, outdated, and abandoned packages grouped into direct/transitive and runtime/development sections
- 📦 **Exact npm Inventory** - Read `package-lock.json` v2/v3 and retain every resolved package instance, including nested duplicate versions and optional or peer packages
- 📈 **System Stats Widget** - Display Laravel and Filament versions
- ⚙️ **Runtime Versions** - Show PHP, Laravel, Filament, Composer, Node.js, npm, environment, debug mode, and timezone
- 🧩 **Custom Technologies** - Add standalone binaries or project-specific technology versions that package managers cannot discover
- 🎨 **Customizable Navigation** - Configure navigation group, icon, label, and sort order
- 🔒 **Authorization Control** – Define who can access the page using a boolean or a closure

## Installation

You can install the package via composer:

```bash
composer require cmsmaxinc/filament-system-versions
```

## Setup

### 1. Register the Plugin

Add the plugin to your Filament panel configuration:

```php
use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugin(FilamentSystemVersionsPlugin::make());
}
```

### 2. Publish and Run Migrations

```bash
php artisan vendor:publish --tag="filament-system-versions-migrations"
php artisan migrate
```

### 3. Configuration (Optional)

Publish the config file:

```bash
php artisan vendor:publish --tag="filament-system-versions-config"
```

This is the contents of the published config file:

```php
return [
    'database' => [
        'table_name' => 'composer_versions',
    ],
    'paths' => [
        'php_path' => env('PHP_PATH', ''),
        'composer_path' => env('COMPOSER_PATH', ''),
        'node_path' => env('NODE_PATH', ''),
        'npm_path' => env('NPM_PATH', ''),
    ],
    'inventory' => [
        'composer_lock' => 'composer.lock',
        'package_json' => 'package.json',
        'package_lock' => 'package-lock.json',
        'runtime_cache_seconds' => 3600,
    ],
    'technologies' => [],
];
```

### 4. Translations (Optional)

If you want to customize the translations, you can publish the translations file:

```bash
php artisan vendor:publish --tag="filament-system-versions-translations"
```

## Usage

### Basic Usage

Once the plugin is registered, a "System Versions" page will automatically be added to your Filament panel under the "Settings" navigation group. This page displays:

- System and command-line runtime versions
- Every Composer package, with current/latest versions and update status
- Every package instance resolved in `package-lock.json`
- Logical, collapsible direct/runtime/development/transitive groups

### Customizing Navigation

You can customize the navigation appearance and behavior using fluent methods when registering the plugin:

```php
use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugin(
            FilamentSystemVersionsPlugin::make()
                ->navigationLabel('System Info')
                ->navigationGroup('Administration')
                ->navigationIcon('heroicon-o-cpu-chip') // Or use Enum
                ->navigationSort(10)
        );
}
```

Every navigation method also accepts a closure, which is evaluated lazily on each request. Use this on multilanguage sites so translations resolve in the visitor's locale:

```php
->plugin(
    FilamentSystemVersionsPlugin::make()
        ->navigationLabel(fn () => __('System Info'))
        ->navigationGroup(fn () => __('Administration'))
)
```

### Controlling Access to the Page

Access to the System Info page can be restricted through the `authorize` method provided by the plugin.

This method accepts either a simple boolean or a closure, and must resolve to true when the current user should be allowed to view the page.

```php
use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugin(
            FilamentSystemVersionsPlugin::make()
                // Example with Spatie Roles / Filament Shield
                ->authorize(fn () => auth()->user()?->hasRole('super_admin'))
                // Example with is_admin column on users table
                ->authorize(fn () => auth()->user()?->is_admin)
        );
}
```

> [!TIP]
> Exact framework and package versions are useful reconnaissance for an attacker. Consider restricting this page to administrators rather than leaving it visible to every panel user (the default is `true`, i.e. visible to everyone with panel access).

#### Available Configuration Methods

- `navigationLabel(string | Closure | null $label)` - Set the navigation menu label (default: 'System Versions')
- `navigationGroup(string | UnitEnum | Closure | null $group)` - Set the navigation group (default: 'Settings')
- `navigationIcon(string | BackedEnum | Closure | null $icon)` - Set the navigation icon (default: 'heroicon-o-document-text')
- `navigationSort(int | Closure | null $sort)` - Set the navigation sort order (default: 99999)
- `authorize(bool | Closure)` - Define whether the current user is allowed to access the page. Accepts either a `bool` (`true` or `false`) or a `Closure` that returns a boolean (default: true).
- `technologies(array | Closure)` - Add standalone or project-specific technology versions to the System details widget.

### Adding Standalone Technologies

Composer and npm packages are discovered automatically. Add tools that live outside those package managers through the plugin:

```php
->plugin(
    FilamentSystemVersionsPlugin::make()
        ->technologies(fn () => [
            [
                'label' => 'Pocketknife',
                'version' => config('services.pocketknife.version'),
                'url' => 'https://example.com/tooling',
            ],
        ])
)
```

Each item requires `label` and `version`; `url` is optional. You can also place the same array under `technologies` in the published config.

### Dependency Versions Command

> [!NOTE]  
> Make sure you run this command at least once to store the current Composer dependency snapshot.

To refresh current/latest metadata for all Composer dependencies:

```bash
php artisan dependency:versions
```

Administrators who can access the System Versions page can also select **Check now** in the page header. The action runs the same command, prevents overlapping checks, refreshes the widgets after a successful run, and keeps the previous snapshot when the check fails.

The npm inventory does not contact the registry. It is read directly from the configured `package-lock.json` on each page render, so it always reflects the exact versions committed or deployed with the application. Runtime command versions are cached for one hour by default.

#### Automatic Scheduling

Add the command to your scheduler to run it automatically:

```php
use Cmsmaxinc\FilamentSystemVersions\Commands\CheckDependencyVersions;

// In your Console Kernel or service provider
Schedule::command(CheckDependencyVersions::class)->daily();
```

### Using Individual Widgets

You can also use the widgets independently in your own pages or dashboards:

#### DependencyWidget

Displays every installed Composer package with current/latest versions, update status, abandonment status, and logical scope:

```php
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyWidget;

->widgets([
    DependencyWidget::class
])
```

#### NpmDependencyWidget

Displays every resolved npm package instance from a lockfile:

```php
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\NpmDependencyWidget;

->widgets([
    NpmDependencyWidget::class
])
```

#### SystemInfoWidget

Shows system environment information:

```php
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemInfoWidget;

->widgets([
    SystemInfoWidget::class
])
```

#### DependencyStat

Create custom stat widgets for specific dependencies:

```php
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyStat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class CustomStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            DependencyStat::make('Laravel')
                ->dependency('laravel/framework'),
            DependencyStat::make('FilamentPHP')
                ->dependency('filament/filament'),
            DependencyStat::make('Livewire')
                ->dependency('livewire/livewire'),
        ];
    }
}
```

### Adding Widgets to Blade Views

To add widgets to custom blade views:

```blade
<x-filament-panels::page>
    @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyWidget::class)
    @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\NpmDependencyWidget::class)
    @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemInfoWidget::class)
</x-filament-panels::page>
```

### Styling

The package ships its own stylesheet and registers it through Filament's asset system, so the widgets are styled out of the box — no custom theme configuration is required. If the styles look stale after updating the package, republish Filament's assets:

```bash
php artisan filament:assets
```

> [!NOTE]
> Older versions of this package required adding an `@source` line for the vendor views to your custom theme's `theme.css`. That is no longer necessary and the line can be removed.

### Contact Info

info@cmsmax.com
