<x-filament-panels::page>
    @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemVersionStats::class)

    <div class="fsv-grid">
        @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyWidget::class)
        @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemInfoWidget::class)
    </div>
</x-filament-panels::page>
