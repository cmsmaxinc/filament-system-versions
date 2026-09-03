<?php

use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\NpmDependencyWidget;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\MessageBag;
use Livewire\Livewire;

class RenderableNpmDependencyWidget extends NpmDependencyWidget
{
    public function getErrorBag(): MessageBag
    {
        return new MessageBag;
    }
}

beforeEach(function () {
    $this->inventoryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fsv-widget-' . bin2hex(random_bytes(6));
    mkdir($this->inventoryDirectory, 0777, true);

    config()->set('filament-system-versions.inventory.package_json', $this->inventoryDirectory . DIRECTORY_SEPARATOR . 'package.json');
    config()->set('filament-system-versions.inventory.package_lock', $this->inventoryDirectory . DIRECTORY_SEPARATOR . 'package-lock.json');
    Filament::setCurrentPanel(Panel::make()->id('test'));
});

afterEach(function () {
    foreach (glob($this->inventoryDirectory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
        unlink($file);
    }

    rmdir($this->inventoryDirectory);
});

it('renders npm groups and every nested package instance', function () {
    file_put_contents(config('filament-system-versions.inventory.package_json'), json_encode([
        'dependencies' => ['react' => '^19.0.0'],
    ], JSON_THROW_ON_ERROR));
    file_put_contents(config('filament-system-versions.inventory.package_lock'), json_encode([
        'lockfileVersion' => 3,
        'packages' => [
            '' => ['name' => 'application'],
            'node_modules/react' => ['name' => 'react', 'version' => '19.1.0'],
            'node_modules/plugin/node_modules/react' => ['name' => 'react', 'version' => '18.3.1'],
            'node_modules/dev-optional' => ['name' => 'dev-optional', 'version' => '1.0.0', 'devOptional' => true],
        ],
    ], JSON_THROW_ON_ERROR));

    Livewire::test(RenderableNpmDependencyWidget::class)
        ->assertSee('npm packages')
        ->assertSee('Direct runtime packages')
        ->assertSee('Transitive runtime packages')
        ->assertSee('Transitive optional packages')
        ->assertSee('19.1.0')
        ->assertSee('18.3.1');
});

it('renders a specific message for an unsupported lockfile', function () {
    file_put_contents(config('filament-system-versions.inventory.package_lock'), json_encode([
        'lockfileVersion' => 1,
        'dependencies' => [],
    ], JSON_THROW_ON_ERROR));

    Livewire::test(RenderableNpmDependencyWidget::class)
        ->assertSee('This package-lock.json version is unsupported');
});

it('renders root peer dependencies in a direct peer group', function () {
    file_put_contents(config('filament-system-versions.inventory.package_json'), json_encode([
        'peerDependencies' => ['react' => '^19.0.0'],
    ], JSON_THROW_ON_ERROR));
    file_put_contents(config('filament-system-versions.inventory.package_lock'), json_encode([
        'lockfileVersion' => 3,
        'packages' => [
            '' => ['name' => 'application'],
            'node_modules/react' => ['name' => 'react', 'version' => '19.1.0', 'peer' => true],
        ],
    ], JSON_THROW_ON_ERROR));

    Livewire::test(RenderableNpmDependencyWidget::class)
        ->assertSee('Direct peer packages')
        ->assertDontSee('Transitive peer packages')
        ->assertSee('react')
        ->assertSee('19.1.0');
});
