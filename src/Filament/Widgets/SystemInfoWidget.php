<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class SystemInfoWidget extends Widget
{
    protected static string $view = 'filament-system-versions::filament.widgets.system';

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
        return collect([
            'Environment' => app()->environment(),
            'PHP Version' => phpversion(),
            'Laravel Version' => app()->version(),
        ]);
    }

    protected function getViewData(): array
    {
        return [
            'details' => $this->getDetails(),
            'heading' => $this->getCardHeading(),
            'description' => $this->getDescription(),
        ];
    }
}
