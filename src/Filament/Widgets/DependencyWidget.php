<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class DependencyWidget extends Widget
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
        $dependencies = DB::table(config('system-versions.database.table_name', 'composer_versions'))
            ->where('direct_dependency', config('system-versions.widgets.dependency.show_direct_only', true))
            ->where('status', '!=', 'up-to-date')
            ->get();

        return [
            'dependencies' => $dependencies,
            'heading' => $this->getCardHeading(),
            'description' => $this->getDescription(),
        ];
    }
}
