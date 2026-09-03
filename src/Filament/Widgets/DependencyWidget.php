<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Cmsmaxinc\FilamentSystemVersions\Filament\Pages\SystemVersions;
use Cmsmaxinc\FilamentSystemVersions\ProjectDependencyInventory;
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
            $scopes = app(ProjectDependencyInventory::class)->composerScopes();

            $dependencies = DB::table($table)
                ->orderBy('name')
                ->get()
                ->map(function ($dependency) use ($scopes) {
                    // Composer's latest-status: "update-possible" means the constraint blocks a
                    // (usually major) update, "semver-safe-update" is a compatible upgrade.
                    $dependency->badge_color = match ($dependency->status) {
                        'up-to-date' => 'success',
                        'update-possible' => 'danger',
                        default => 'warning',
                    };
                    $dependency->scope = $scopes[$dependency->name] ?? 'unknown';
                    $dependency->status_label = __("filament-system-versions::system-versions.statuses.{$dependency->status}");

                    return $dependency;
                });
        }

        $groups = collect([
            ['key' => 'direct-runtime', 'direct' => true, 'scope' => 'runtime', 'open' => true],
            ['key' => 'direct-development', 'direct' => true, 'scope' => 'development', 'open' => true],
            ['key' => 'transitive-runtime', 'direct' => false, 'scope' => 'runtime', 'open' => false],
            ['key' => 'transitive-development', 'direct' => false, 'scope' => 'development', 'open' => false],
            ['key' => 'unclassified', 'direct' => null, 'scope' => 'unknown', 'open' => false],
        ])->map(function (array $group) use ($dependencies): array {
            $items = $dependencies->where('scope', $group['scope']);

            if ($group['direct'] !== null) {
                $items = $items->filter(fn ($dependency): bool => (bool) $dependency->direct_dependency === $group['direct']);
            }

            $group['label'] = __("filament-system-versions::system-versions.groups.{$group['key']}");
            $group['dependencies'] = $items->values();

            return $group;
        })->filter(fn (array $group): bool => $group['dependencies']->isNotEmpty())->values();

        return [
            'dependencies' => $dependencies,
            'groups' => $groups,
            'total' => $dependencies->count(),
            'updates' => $dependencies->where('status', '!=', 'up-to-date')->count(),
            'abandoned' => $dependencies->where('abandoned', true)->count(),
            'missingTable' => $missingTable,
            'hasData' => $hasData,
            'heading' => $this->getCardHeading(),
            'description' => $this->getDescription(),
        ];
    }
}
