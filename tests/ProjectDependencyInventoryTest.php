<?php

use Cmsmaxinc\FilamentSystemVersions\ProjectDependencyInventory;

beforeEach(function () {
    $this->inventoryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fsv-' . bin2hex(random_bytes(6));
    mkdir($this->inventoryDirectory, 0777, true);

    config()->set('filament-system-versions.inventory.composer_lock', $this->inventoryDirectory . DIRECTORY_SEPARATOR . 'composer.lock');
    config()->set('filament-system-versions.inventory.package_json', $this->inventoryDirectory . DIRECTORY_SEPARATOR . 'package.json');
    config()->set('filament-system-versions.inventory.package_lock', $this->inventoryDirectory . DIRECTORY_SEPARATOR . 'package-lock.json');
});

afterEach(function () {
    foreach (glob($this->inventoryDirectory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
        unlink($file);
    }

    rmdir($this->inventoryDirectory);
});

it('classifies Composer packages from both lockfile scopes', function () {
    file_put_contents(config('filament-system-versions.inventory.composer_lock'), json_encode([
        'packages' => [['name' => 'vendor/runtime', 'version' => '1.0.0']],
        'packages-dev' => [['name' => 'vendor/testing', 'version' => '2.0.0']],
    ], JSON_THROW_ON_ERROR));

    expect(app(ProjectDependencyInventory::class)->composerScopes())->toBe([
        'vendor/runtime' => 'runtime',
        'vendor/testing' => 'development',
    ]);
});

it('keeps every npm package instance and classifies its relationship and scope', function () {
    file_put_contents(config('filament-system-versions.inventory.package_json'), json_encode([
        'dependencies' => ['react' => '^19.0.0'],
        'devDependencies' => ['vite' => '^7.0.0'],
        'optionalDependencies' => ['fsevents' => '^2.3.0'],
    ], JSON_THROW_ON_ERROR));
    file_put_contents(config('filament-system-versions.inventory.package_lock'), json_encode([
        'lockfileVersion' => 3,
        'packages' => [
            '' => ['name' => 'application'],
            'node_modules/react' => ['name' => 'react', 'version' => '19.1.0'],
            'node_modules/vite' => ['name' => 'vite', 'version' => '7.1.0', 'dev' => true],
            'node_modules/fsevents' => ['name' => 'fsevents', 'version' => '2.3.3', 'optional' => true],
            'node_modules/plugin/node_modules/react' => ['name' => 'react', 'version' => '18.3.1'],
            'node_modules/peer-helper' => ['name' => 'peer-helper', 'version' => '1.2.0', 'peer' => true],
        ],
    ], JSON_THROW_ON_ERROR));

    $inventory = app(ProjectDependencyInventory::class)->npm();
    $dependencies = collect($inventory['dependencies'])->keyBy('path');

    expect($inventory['available'])->toBeTrue()
        ->and($inventory['lockfile_version'])->toBe(3)
        ->and($dependencies)->toHaveCount(5)
        ->and($dependencies['node_modules/react'])->toMatchArray([
            'name' => 'react',
            'version' => '19.1.0',
            'direct' => true,
            'scope' => 'runtime',
        ])
        ->and($dependencies['node_modules/plugin/node_modules/react'])->toMatchArray([
            'name' => 'react',
            'version' => '18.3.1',
            'direct' => false,
            'scope' => 'runtime',
        ])
        ->and($dependencies['node_modules/vite']['scope'])->toBe('development')
        ->and($dependencies['node_modules/fsevents']['scope'])->toBe('optional')
        ->and($dependencies['node_modules/peer-helper']['scope'])->toBe('peer');
});

it('degrades safely when lockfiles are missing or malformed', function () {
    file_put_contents(config('filament-system-versions.inventory.composer_lock'), '{bad json');
    file_put_contents(config('filament-system-versions.inventory.package_lock'), '{bad json');

    $inventory = app(ProjectDependencyInventory::class);

    expect($inventory->composerScopes())->toBe([])
        ->and($inventory->npm())->toMatchArray([
            'available' => false,
            'lockfile_version' => null,
            'dependencies' => [],
        ]);
});
