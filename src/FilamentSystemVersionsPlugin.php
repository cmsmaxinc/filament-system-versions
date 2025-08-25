<?php

namespace Cmsmaxinc\FilamentSystemVersions;

use Cmsmaxinc\FilamentSystemVersions\Filament\Pages\SystemVersions;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentSystemVersionsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-system-versions';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            SystemVersions::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
