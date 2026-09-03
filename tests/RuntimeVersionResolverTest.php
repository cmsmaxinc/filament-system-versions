<?php

use Cmsmaxinc\FilamentSystemVersions\RuntimeVersionResolver;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    Cache::flush();
    config()->set('filament-system-versions.inventory.runtime_cache_seconds', 0);
    config()->set('filament-system-versions.paths.php_path', '');
    config()->set('filament-system-versions.paths.composer_path', '');
    config()->set('filament-system-versions.paths.node_path', '');
    config()->set('filament-system-versions.paths.npm_path', '');
});

it('reports Composer Node and npm versions from the configured project root', function () {
    Process::fake([
        '*composer*' => Process::result('Composer version 2.8.11 2025-01-01 00:00:00'),
        '*node*' => Process::result('v22.14.0'),
        '*npm*' => Process::result('11.1.0'),
    ]);

    expect(app(RuntimeVersionResolver::class)->versions())->toBe([
        'composer' => '2.8.11',
        'node' => '22.14.0',
        'npm' => '11.1.0',
    ]);

    Process::assertRan(fn (PendingProcess $process): bool => $process->path === base_path());
});

it('returns unavailable values instead of failing the page when binaries cannot run', function () {
    Process::fake([
        '*' => Process::result(errorOutput: 'not found', exitCode: 1),
    ]);

    expect(app(RuntimeVersionResolver::class)->versions())->toBe([
        'composer' => null,
        'node' => null,
        'npm' => null,
    ]);
});

it('caches unavailable binaries so a missing tool does not repeatedly slow the page', function () {
    config()->set('filament-system-versions.inventory.runtime_cache_seconds', 3600);
    Process::fake([
        '*' => Process::result(errorOutput: 'not found', exitCode: 1),
    ]);

    app(RuntimeVersionResolver::class)->versions();
    app(RuntimeVersionResolver::class)->versions();

    Process::assertRanTimes(fn (PendingProcess $process): bool => $process->path === base_path(), 3);
});
