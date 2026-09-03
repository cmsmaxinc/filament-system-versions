<?php

use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemInfoWidget;
use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\MessageBag;
use Livewire\Livewire;

class RenderableSystemInfoWidget extends SystemInfoWidget
{
    public function getErrorBag(): MessageBag
    {
        return new MessageBag;
    }
}

it('renders as a standalone widget when the plugin is not registered', function () {
    Filament::setCurrentPanel(Panel::make()->id('standalone'));
    config()->set('filament-system-versions.technologies', [
        ['label' => 'Configured tool', 'version' => null],
    ]);
    Process::fake();

    Livewire::test(RenderableSystemInfoWidget::class)
        ->assertSee('Configured tool')
        ->assertSee('Not available');

    Process::assertNothingRan();
});

it('renders technologies supplied lazily by a registered plugin', function () {
    $plugin = FilamentSystemVersionsPlugin::make()
        ->technologies(fn () => collect([
            ['label' => 'Standalone tool', 'version' => '1.2.3'],
        ]));
    Filament::setCurrentPanel(
        Panel::make()->id('registered')->plugin($plugin)
    );

    Livewire::test(RenderableSystemInfoWidget::class)
        ->assertSee('Standalone tool')
        ->assertSee('1.2.3');
});
