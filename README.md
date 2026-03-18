# Filament System Versions

![Filament System Versions](https://github.com/cmsmaxinc/filament-system-versions/raw/main/thumbnail.jpg)

This package provides a comprehensive system information page and widgets for Filament panels, showcasing current system versions, PHP information, and Composer dependencies.

## Features

- 📊 **System Versions Page** - A dedicated page displaying system information
- 🔍 **Dependency Monitoring** - Track outdated Composer dependencies
- 📈 **System Stats Widget** - Display Laravel and Filament versions
- ⚙️ **System Info Widget** - Show environment, PHP version, and Laravel version
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
    'widgets' => [
        'dependency' => [
            'show_direct_only' => true,
        ],
    ],
    'paths' => [
        'php_path' => env('PHP_PATH', ''),
        'composer_path' => env('COMPOSER_PATH', ''),
    ],
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

- System version statistics (Laravel & Filament versions)
- Outdated dependency information
- System environment details

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

#### Available Configuration Methods

- `navigationLabel(string $label)` - Set the navigation menu label (default: 'System Versions')
- `navigationGroup(string $group)` - Set the navigation group (default: 'Settings')
- `navigationIcon(string $icon)` - Set the navigation icon (default: 'heroicon-o-document-text')
- `navigationSort(int $sort)` - Set the navigation sort order (default: 99999)
- `authorize(bool | Closure)` - Define whether the current user is allowed to access the page. Accepts either a `bool` (`true` or `false`) or a `Closure` that returns a boolean (default: true).

### Dependency Versions Command

> [!NOTE]  
> Make sure you run this command at least once to store the current composer dependencies.

To check for outdated composer dependencies:

```bash
php artisan dependency:versions
```

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

Displays all outdated composer dependencies with current and latest versions:

```php
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyWidget;

->widgets([
    DependencyWidget::class
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
    @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemInfoWidget::class)
</x-filament-panels::page>
```

### Custom Theme Support

If you're using a custom theme, add the following to your `theme.css` file to ensure proper styling:

```css
@source '../../../../vendor/cmsmaxinc/filament-system-versions/resources/**/*.blade.php';
```

### Contact Info

info@cmsmax.com
