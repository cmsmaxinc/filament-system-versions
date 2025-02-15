<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Composer\InstalledVersions;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Contracts\Support\Htmlable;

class LaravelVersionStat extends Stat
{
    public ?string $latestVersion = null;

    public ?string $currentVersion = null;

    public bool $withLatestVersion = false;

    public static function make(Htmlable | string $label = 'Laravel', $value = null): static
    {
        $version = InstalledVersions::getPrettyVersion('laravel/framework');

        return parent::make($label, $version);
    }

    public function latest(bool $latest = true): static
    {
        if ($latest) {
            $this->latestVersion = 'TODO';
        }

        return $this;
    }

    public function getDescriptionColor(): string | array | null
    {
        return 'warning';
    }

    public function getWithLatestVersion(): ?string
    {
        return $this->latestVersion;
    }

    public function getLatestVersion(): ?string
    {
        return $this->latestVersion;
    }

    public function getDescription(): string | Htmlable | null
    {
        return $this->getLatestVersion() ?: null;
    }
}
