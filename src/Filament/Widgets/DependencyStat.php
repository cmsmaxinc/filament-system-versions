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

        // Set the URL for the stat card and open in new tab
        $this->url($this->getGithubUrl())
            ->openUrlInNewTab();

        return $this;
    }

    private function getGithubUrl(): ?string
    {
        $package = $this->dependency;

        // Special case for PHP - link to official PHP website
        if ($package === 'php') {
            return 'https://www.php.net';
        }

        // For composer packages, link to Packagist
        return "https://packagist.org/packages/{$package}";
    }

    private function getDependency(): string
    {
        return $this->dependency;
    }

    public function getValue(): ?string
    {
        // Handle special cases
        if ($this->getDependency() === 'php') {
            return phpversion();
        }

        // Handle composer packages
        try {
            return InstalledVersions::getPrettyVersion($this->getDependency());
        } catch (\Exception $e) {
            return 'Not installed';
        }
    }

    public function getDescriptionColor(): string | array | null
    {
        return 'info';
    }

    public function getDescription(): string | Htmlable | null
    {
        // Don't show latest version for PHP since it's not a composer package
        if ($this->getDependency() === 'php') {
            return null;
        }

        $latest = DB::table(config('filament-system-versions.database.table_name', 'composer_versions'))
            ->where('name', $this->getDependency())
            ->first();

        return $latest?->latest_version;
    }
}
