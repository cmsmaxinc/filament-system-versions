# Filament System Versions

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
php artisan vendor:publish --tag="filament-system-versions-config"
```

This is the contents of the published config file:

```php
return [
    'database' => [
        'table_name' => 'composer_versions',
    ],
];

```

## Usage

### Widgets

#### Oudated Composer Dependencies
This widget will display all outdated composer dependencies with the current version and the latest version available.

```php
->widgets([
    ComposerWidget::make(),
])
```

### Stats
Stats widget will display the current versions such as PHP, Laravel.

```php
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // You add the latest version of the system by calling the `latest()` method on the stat.
        return [
            PhpVersionStat::make()
                ->latest(),
            FilamentVersionStat::make()
                ->latest(),
            LaravelVersionStat::make()
                ->latest(),
        ];
    }
}
```

### Command

To run the command to check for outdated composer dependencies, you can run the following command:

```bash
php artisan composer:outdated
```

But obviously, you don't want to run this command manually every time you want to check for outdated dependencies. So, you can use the command in your scheduler to run this command automatically.

```php
Schedule::call(CheckComposerVersions::class)->daily();
```