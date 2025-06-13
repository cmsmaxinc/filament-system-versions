# Filament System Versions

![Filament System Versions](https://github.com/cmsmaxinc/filament-system-versions/raw/main/thumbnail.jpg)

This package offers a set of widgets to showcase the current system versions, including Composer dependencies.

## Installation

You can install the package via composer:

```bash
composer require cmsmaxinc/filament-system-versions
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-system-versions-migrations"
php artisan migrate
```

### Custom Theme

You will need to [create a custom theme](https://filamentphp.com/docs/3.x/panels/themes#creating-a-custom-theme) for the styles to be applied correctly.


Make sure you add the following to your `tailwind.config.js file.

```bash
'./vendor/cmsmaxinc/filament-system-versions/resources/**/*.blade.php',
```

### Translations
If you want to customize the translations, you can publish the translations file.

```bash
php artisan vendor:publish --tag="filament-system-versions-translations"
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="system-versions-config"
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
];
```

## Usage

### Command

> [!NOTE]  
> Make sure you run this command atleast once to store the current composer dependencies.

To run the command to check for outdated composer dependencies, you can run the following command:

```bash
php artisan dependency:versions
```

But obviously, you don't want to run this command manually every time you want to check for outdated dependencies. So, you can use the command in your scheduler to run this command automatically.

```php
Schedule::command(CheckDependencyVersions::class)->daily();
```

### DependencyWidget
This widget will display all outdated composer dependencies with the current version and the latest version available.

```php
->widgets([
    DependencyWidget::make(),
])
```

### DependencyStat
Stat widget will display the installed version of the dependencies and the latest version available.

```php
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            DependencyStat::make('Laravel')
                ->dependency('laravel/framework'),
            DependencyStat::make('FilamentPHP')
                ->dependency('filament/filament'),
        ];
    }
}
```

### Adding the widget to a blade view
To add the system versions widget to an existing blade view:

```blade
<x-filament-panels::page>
    @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyWidget::class)
</x-filament-panels::page>
```

### Contact Info
info@cmsmax.com
