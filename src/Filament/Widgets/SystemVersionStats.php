<?php

declare(strict_types=1);

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SystemVersionStats extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected function getColumns(): int
    {
        $count = count($this->getConfiguredPackages());
        
        // Return appropriate column count based on number of packages
        return match (true) {
            $count <= 2 => 2,
            $count <= 3 => 3,
            $count <= 4 => 4,
            default => 4,
        };
    }

    protected function getStats(): array
    {
        $stats = [];
        $packages = $this->getConfiguredPackages();

        foreach ($packages as $package) {
            $stats[] = DependencyStat::make($package['label'])
                ->dependency($package['package']);
        }

        return $stats;
    }

    protected function getConfiguredPackages(): array
    {
        return FilamentSystemVersionsPlugin::get()->getStatsPackages();
    }
}
