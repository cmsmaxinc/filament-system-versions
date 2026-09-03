<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;
use Cmsmaxinc\FilamentSystemVersions\RuntimeVersionResolver;
use Composer\InstalledVersions;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class SystemInfoWidget extends Widget
{
    protected string $view = 'filament-system-versions::filament.widgets.system';

    protected int | string | array $columnSpan = 'full';

    public function getCardHeading(): string
    {
        return __('filament-system-versions::system-versions.widgets.system.heading');
    }

    public function getDescription(): string
    {
        return __('filament-system-versions::system-versions.widgets.system.description');
    }

    protected function getDetails(): Collection
    {
        $runtime = app(RuntimeVersionResolver::class)->versions();
        $notAvailable = __('filament-system-versions::system-versions.not_available');

        return collect([
            ['label' => __('filament-system-versions::system-versions.widgets.system.details.environment'), 'value' => app()->environment()],
            ['label' => 'PHP', 'value' => phpversion()],
            ['label' => 'Laravel', 'value' => app()->version()],
            ['label' => 'Filament', 'value' => InstalledVersions::getPrettyVersion('filament/filament') ?: $notAvailable],
            ['label' => 'Composer', 'value' => $runtime['composer'] ?? $notAvailable],
            ['label' => 'Node.js', 'value' => $runtime['node'] ?? $notAvailable],
            ['label' => 'npm', 'value' => $runtime['npm'] ?? $notAvailable],
            ['label' => __('filament-system-versions::system-versions.widgets.system.details.timezone'), 'value' => config('app.timezone')],
        ])->concat(collect(FilamentSystemVersionsPlugin::get()->getTechnologies())
            ->filter(fn (mixed $technology): bool => is_array($technology)
                && is_string($technology['label'] ?? null)
                && is_string($technology['version'] ?? null))
            ->map(fn (array $technology): array => [
                'label' => $technology['label'],
                'value' => $technology['version'],
                'url' => is_string($technology['url'] ?? null) ? $technology['url'] : null,
            ]));
    }

    protected function getViewData(): array
    {
        $debug = (bool) config('app.debug');

        return [
            'details' => $this->getDetails(),
            'debug' => $debug,
            'debugColor' => match (true) {
                $debug && app()->isProduction() => 'danger',
                $debug => 'warning',
                default => 'success',
            },
            'heading' => $this->getCardHeading(),
            'description' => $this->getDescription(),
        ];
    }
}
