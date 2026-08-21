<?php

declare(strict_types=1);

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Pages;

use BackedEnum;
use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SystemVersions extends Page
{
    protected string $view = 'filament-system-versions::filament.pages.system-versions';

    public static function canAccess(): bool
    {
        return FilamentSystemVersionsPlugin::get()->isAuthorized();
    }

    public static function getNavigationLabel(): string
    {
        return FilamentSystemVersionsPlugin::get()->getNavigationLabel();
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return FilamentSystemVersionsPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return FilamentSystemVersionsPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentSystemVersionsPlugin::get()->getNavigationSort();
    }
}
