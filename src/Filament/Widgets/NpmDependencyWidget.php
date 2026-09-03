<?php

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Widgets;

use Cmsmaxinc\FilamentSystemVersions\ProjectDependencyInventory;
use Filament\Widgets\Widget;

class NpmDependencyWidget extends Widget
{
    protected string $view = 'filament-system-versions::filament.widgets.npm-dependency';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $inventory = app(ProjectDependencyInventory::class)->npm();
        $dependencies = collect($inventory['dependencies']);
        $groups = collect([
            ['key' => 'direct-runtime', 'direct' => true, 'scope' => 'runtime', 'open' => true],
            ['key' => 'direct-development', 'direct' => true, 'scope' => 'development', 'open' => true],
            ['key' => 'direct-optional', 'direct' => true, 'scope' => 'optional', 'open' => true],
            ['key' => 'transitive-runtime', 'direct' => false, 'scope' => 'runtime', 'open' => false],
            ['key' => 'transitive-development', 'direct' => false, 'scope' => 'development', 'open' => false],
            ['key' => 'transitive-optional', 'direct' => false, 'scope' => 'optional', 'open' => false],
            ['key' => 'transitive-peer', 'direct' => false, 'scope' => 'peer', 'open' => false],
        ])->map(function (array $group) use ($dependencies): array {
            $group['label'] = __("filament-system-versions::system-versions.groups.{$group['key']}");
            $group['dependencies'] = $dependencies
                ->where('direct', $group['direct'])
                ->where('scope', $group['scope'])
                ->values();

            return $group;
        })->filter(fn (array $group): bool => $group['dependencies']->isNotEmpty())->values();

        return [
            'available' => $inventory['available'],
            'unavailableReason' => $inventory['unavailable_reason'],
            'lockfileVersion' => $inventory['lockfile_version'],
            'total' => $dependencies->count(),
            'unique' => $dependencies->unique(fn (array $dependency): string => $dependency['name'] . '@' . $dependency['version'])->count(),
            'groups' => $groups,
            'heading' => __('filament-system-versions::system-versions.widgets.npm.heading'),
            'description' => __('filament-system-versions::system-versions.widgets.npm.description'),
        ];
    }
}
