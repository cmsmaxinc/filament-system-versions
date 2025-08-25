<x-filament-panels::page>
    <div class="w-full p-1">
        @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemVersionStats::class)
    </div>
    <div class="w-full flex flex-row">
        <div class="lg:w-1/2 w-full p-1">
            @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyWidget::class)
        </div>
        <div class="lg:w-1/2 w-full p-1">
            @livewire(\Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemInfoWidget::class)
        </div>
    </div>
</x-filament-panels::page>