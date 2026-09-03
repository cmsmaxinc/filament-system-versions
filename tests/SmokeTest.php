<?php

use Cmsmaxinc\FilamentSystemVersions\Filament\Pages\SystemVersions;
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyStat;
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyWidget;
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\NpmDependencyWidget;
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemInfoWidget;
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemVersionStats;
use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;

// Loading the classes verifies their signatures link against the installed Filament version —
// a mismatched override (e.g. a navigation getter) is a fatal error at autoload time.
it('declares signatures compatible with the installed Filament version', function (string $class) {
    expect(class_exists($class))->toBeTrue();
})->with([
    SystemVersions::class,
    DependencyStat::class,
    DependencyWidget::class,
    NpmDependencyWidget::class,
    SystemInfoWidget::class,
    SystemVersionStats::class,
    FilamentSystemVersionsPlugin::class,
]);

it('evaluates navigation closures lazily', function () {
    $plugin = FilamentSystemVersionsPlugin::make()
        ->navigationLabel(fn () => 'Translated label')
        ->navigationGroup(fn () => 'Translated group')
        ->navigationSort(fn () => 5);

    expect($plugin->getNavigationLabel())->toBe('Translated label')
        ->and($plugin->getNavigationGroup())->toBe('Translated group')
        ->and($plugin->getNavigationSort())->toBe(5);
});

it('evaluates custom technologies lazily', function () {
    $plugin = FilamentSystemVersionsPlugin::make()
        ->technologies(fn () => [
            ['label' => 'Standalone tool', 'version' => '1.2.3'],
        ]);

    expect($plugin->getTechnologies())->toBe([
        ['label' => 'Standalone tool', 'version' => '1.2.3'],
    ]);
});

it('falls back to translated navigation defaults', function () {
    $plugin = FilamentSystemVersionsPlugin::make();

    expect($plugin->getNavigationLabel())->toBe('System Versions')
        ->and($plugin->getNavigationGroup())->toBe('Settings')
        ->and($plugin->getNavigationSort())->toBe(99999);
});
