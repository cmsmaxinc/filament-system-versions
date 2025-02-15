<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Contracts\Support\Htmlable;

class PhpVersionStat extends Stat
{
    public ?string $latestVersion = null;

    public ?string $currentVersion = null;

    public bool $withLatestVersion = false;

    public static function make(Htmlable | string $label = 'PHP Version', $value = null): static
    {
        return parent::make($label, phpversion());
    }

    public function latest(bool $latest = true): static
    {
        if ($latest) {
            $response = file_get_contents('https://www.php.net/releases/?json&version=8');
            $data = json_decode($response, true);

            $this->latestVersion = $data['version'];
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
