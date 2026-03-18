<?php

declare(strict_types=1);

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Pages;

use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;
use Filament\Pages\Page;
use UnitEnum;

class SystemVersions extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament-system-versions::filament.pages.system-versions';

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'System Versions';

    protected static ?int $navigationSort = 99999;


    public static function canAccess(): bool
    {
        return FilamentSystemVersionsPlugin::get()->isAuthorized();
    }
}
