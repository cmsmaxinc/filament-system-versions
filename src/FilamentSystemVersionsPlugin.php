<?php

namespace Cmsmaxinc\FilamentSystemVersions;

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
        //
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
