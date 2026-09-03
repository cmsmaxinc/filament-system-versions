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
            'node_modules/dev-optional-helper' => ['name' => 'dev-optional-helper', 'version' => '4.5.0', 'devOptional' => true],
        ],
    ], JSON_THROW_ON_ERROR));

    $inventory = app(ProjectDependencyInventory::class)->npm();
    $dependencies = collect($inventory['dependencies'])->keyBy('path');

    expect($inventory['available'])->toBeTrue()
        ->and($inventory['lockfile_version'])->toBe(3)
        ->and($dependencies)->toHaveCount(6)
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
        ->and($dependencies['node_modules/dev-optional-helper']['scope'])->toBe('optional')
        ->and($dependencies['node_modules/peer-helper']['scope'])->toBe('peer');
});

it('resolves workspace links before filtering versionless package entries', function () {
    file_put_contents(config('filament-system-versions.inventory.package_json'), json_encode([
        'dependencies' => [
            'workspace-package' => 'workspace:*',
            'z-workspace-package' => 'workspace:*',
        ],
    ], JSON_THROW_ON_ERROR));
    file_put_contents(config('filament-system-versions.inventory.package_lock'), json_encode([
        'lockfileVersion' => 3,
        'packages' => [
            '' => ['name' => 'application'],
            'node_modules/workspace-package' => ['resolved' => 'packages/workspace-package', 'link' => true],
            'node_modules/z-workspace-package' => ['resolved' => 'packages/z-workspace-package', 'link' => true],
            'packages/workspace-package' => ['version' => '1.2.3'],
            'packages/z-workspace-package' => ['name' => 'z-workspace-package', 'version' => '2.3.4'],
        ],
    ], JSON_THROW_ON_ERROR));

    expect(app(ProjectDependencyInventory::class)->npm()['dependencies'])->toBe([
        [
            'name' => 'workspace-package',
            'version' => '1.2.3',
            'path' => 'node_modules/workspace-package',
            'direct' => true,
            'scope' => 'runtime',
        ],
        [
            'name' => 'z-workspace-package',
            'version' => '2.3.4',
            'path' => 'node_modules/z-workspace-package',
            'direct' => true,
            'scope' => 'runtime',
        ],
    ]);
});

it('classifies npm aliases by their installed dependency key', function () {
    file_put_contents(config('filament-system-versions.inventory.package_json'), json_encode([
        'dependencies' => ['underscore' => 'npm:lodash@^4.17.21'],
    ], JSON_THROW_ON_ERROR));
    file_put_contents(config('filament-system-versions.inventory.package_lock'), json_encode([
        'lockfileVersion' => 3,
        'packages' => [
            '' => ['name' => 'application'],
            'node_modules/underscore' => ['name' => 'lodash', 'version' => '4.17.21'],
        ],
    ], JSON_THROW_ON_ERROR));

    expect(app(ProjectDependencyInventory::class)->npm()['dependencies'])->toBe([
        [
            'name' => 'lodash',
            'version' => '4.17.21',
            'path' => 'node_modules/underscore',
            'direct' => true,
            'scope' => 'runtime',
        ],
    ]);
});

it('classifies root peer dependencies as direct peers', function () {
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

    expect(app(ProjectDependencyInventory::class)->npm()['dependencies'])->toBe([
        [
            'name' => 'react',
            'version' => '19.1.0',
            'path' => 'node_modules/react',
            'direct' => true,
            'scope' => 'peer',
        ],
    ]);
});

it('degrades safely when lockfiles are missing or malformed', function () {
    file_put_contents(config('filament-system-versions.inventory.composer_lock'), '{bad json');
    file_put_contents(config('filament-system-versions.inventory.package_lock'), '{bad json');

    $inventory = app(ProjectDependencyInventory::class);

    expect($inventory->composerScopes())->toBe([])
        ->and($inventory->npm())->toMatchArray([
            'available' => false,
            'unavailable_reason' => 'invalid',
            'lockfile_version' => null,
            'dependencies' => [],
        ]);
});

it('distinguishes an unsupported npm v1 lockfile from a missing file', function () {
    file_put_contents(config('filament-system-versions.inventory.package_lock'), json_encode([
        'lockfileVersion' => 1,
        'dependencies' => [],
    ], JSON_THROW_ON_ERROR));

    $inventory = app(ProjectDependencyInventory::class);

    expect($inventory->npm()['unavailable_reason'])->toBe('unsupported');

    unlink(config('filament-system-versions.inventory.package_lock'));

    expect($inventory->npm()['unavailable_reason'])->toBe('missing');
});
