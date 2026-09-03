<?php

use Cmsmaxinc\FilamentSystemVersions\ConfiguredCommandBuilder;

beforeEach(function () {
    config()->set('filament-system-versions.paths.php_path', '');
    config()->set('filament-system-versions.paths.composer_path', '');
    config()->set('filament-system-versions.paths.node_path', '');
    config()->set('filament-system-versions.paths.npm_path', '');
});

it('builds consistent default package manager commands', function () {
    $commands = app(ConfiguredCommandBuilder::class);

    expect($commands->composer(['--version']))->toBe('composer --version')
        ->and($commands->node(['--version']))->toBe('node --version')
        ->and($commands->npm(['--version']))->toBe('npm --version');
});

it('runs configured Composer phars through PHP and configured binaries directly', function () {
    config()->set('filament-system-versions.paths.composer_path', '/opt/tools/composer.phar');
    config()->set('filament-system-versions.paths.node_path', '/opt/node/bin/node');
    config()->set('filament-system-versions.paths.npm_path', '/opt/node/bin/npm');

    $commands = app(ConfiguredCommandBuilder::class);

    expect($commands->composer(['--version']))->toBe([
        PHP_BINARY,
        '/opt/tools/composer.phar',
        '--version',
    ])->and($commands->node(['--version']))->toBe([
        '/opt/node/bin/node',
        '--version',
    ])->and($commands->npm(['--version']))->toBe([
        '/opt/node/bin/npm',
        '--version',
    ]);
});
