<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Composer\InstalledVersions;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Contracts\Support\Htmlable;

class DependencyStat extends Stat
{
    public static function make(Htmlable | string $label = 'PHP Version', $dependency = null): static
    {
        $installed = InstalledVersions::getPrettyVersion($dependency);

        return parent::make($label, $installed);
    }
}
