<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Cmsmaxinc\FilamentSystemVersions\Filament\Pages\SystemVersions;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;

class DependencyWidget extends Widget
{
    protected string $view = 'filament-system-versions::filament.widgets.dependency';

    public function getCardHeading(): string
    {
        return __('filament-system-versions::system-versions.widgets.dependency.heading');
    }

    public function getDescription(): string
    {
        return __('filament-system-versions::system-versions.widgets.dependency.description');
    }

    #[On(SystemVersions::DEPENDENCY_VERSIONS_REFRESHED_EVENT)]
    public function refreshDependencyVersions(): void {}

    protected function getViewData(): array
    {
        $table = config('filament-system-versions.database.table_name', 'composer_versions');

        $missingTable = ! Schema::hasTable($table);
        $hasData = ! $missingTable && DB::table($table)->exists();

        $dependencies = collect();

        if ($hasData) {
            $dependencies = DB::table($table)
                ->when(
                    config('filament-system-versions.widgets.dependency.show_direct_only', true),
                    fn ($query) => $query->where('direct_dependency', true),
                )
                ->where(fn ($query) => $query
                    ->where('status', '!=', 'up-to-date')
                    ->orWhere('abandoned', true))
                ->orderBy('name')
                ->get()
                ->map(function ($dependency) {
                    // Composer's latest-status: "update-possible" means the constraint blocks a
                    // (usually major) update, "semver-safe-update" is a compatible upgrade.
                    $dependency->badge_color = $dependency->status === 'update-possible' ? 'danger' : 'warning';

                    return $dependency;
                });
        }

        return [
            'dependencies' => $dependencies,
            'missingTable' => $missingTable,
            'hasData' => $hasData,
            'heading' => $this->getCardHeading(),
            'description' => $this->getDescription(),
        ];
    }
}
