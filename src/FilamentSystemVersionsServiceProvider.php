<?php

namespace Cmsmaxinc\FilamentSystemVersions;

use Cmsmaxinc\FilamentSystemVersions\Commands\CheckDependencyVersions;
use Cmsmaxinc\FilamentSystemVersions\Testing\TestsFilamentSystemVersions;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentSystemVersionsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-system-versions';

    public static string $viewNamespace = 'filament-system-versions';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('cmsmaxinc/filament-system-versions');
            });

        if (file_exists($package->basePath('/../config/filament-system-versions.php'))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-system-versions/{$file->getFilename()}"),
                ], 'filament-system-versions-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentSystemVersions);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'cmsmaxinc/filament-system-versions';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-system-versions', __DIR__ . '/../resources/dist/components/filament-system-versions.js'),
            //            Css::make('filament-system-versions-styles', __DIR__ . '/../resources/dist/filament-system-versions.css'),
            //            Js::make('filament-system-versions-scripts', __DIR__ . '/../resources/dist/filament-system-versions.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            CheckDependencyVersions::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_composer_versions_table',
        ];
    }
}
