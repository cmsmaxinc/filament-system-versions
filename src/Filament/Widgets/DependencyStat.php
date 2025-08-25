<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Composer\InstalledVersions;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class DependencyStat extends Stat
{
    public string $dependency;

    public static function make(Htmlable | string $label, $value = null): static
    {
        return parent::make($label, null);
    }

    public function dependency(string $dependency): static
    {
        $this->dependency = $dependency;

        return $this;
    }

    private function getDependency(): string
    {
        return $this->dependency;
    }

    public function getValue(): ?string
    {
        return InstalledVersions::getPrettyVersion($this->getDependency());
    }

    public function getDescriptionColor(): string | array | null
    {
        return 'info';
    }

    public function getDescription(): string | Htmlable | null
    {
        $latest = DB::table(config('filament-system-versions.database.table_name', 'composer_versions'))
            ->where('name', $this->getDependency())
            ->first();

        return $latest?->latest_version;
    }
}
