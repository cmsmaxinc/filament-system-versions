<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class ComposerWidget extends Widget
{
    protected static string $view = 'filament-system-versions::filament.widgets.system-versions';

    public function getCardHeading(): string
    {
        return __('filament-system-versions::system-versions.widget.heading');
    }

    public function getDescription(): string
    {
        return __('filament-system-versions::system-versions.widget.description');
    }

    protected function getViewData(): array
    {
        return [
            'packages' => DB::table('composer_versions')->get(),
            'heading' => $this->getCardHeading(),
            'description' => $this->getDescription(),
        ];
    }
}
