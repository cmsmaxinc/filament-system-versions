<?php

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    $migration = include __DIR__ . '/../database/migrations/create_composer_versions_table.php.stub';
    $migration->up();
});

$composerOutput = json_encode([
    'installed' => [
        [
            'name' => 'vendor/package',
            'version' => '1.0.0',
            'latest' => '1.1.0',
            'latest-status' => 'semver-safe-update',
            'direct-dependency' => true,
            'description' => 'A package.',
            'abandoned' => false,
        ],
    ],
]);

it('runs composer from the application root, not the inherited working directory', function () use ($composerOutput) {
    // A queue worker's or web server's cwd is not necessarily the app root
    // (Laravel Cloud runs from `/`), and composer resolves composer.json
    // from cwd — the regression this pins was "No composer.json in current
    // directory" in production.
    Process::fake([
        '*' => Process::result($composerOutput),
    ]);

    $this->artisan('dependency:versions')->assertSuccessful();

    Process::assertRan(fn (PendingProcess $process) => $process->path === base_path());
});

it('stores the reported packages', function () use ($composerOutput) {
    Process::fake([
        '*' => Process::result($composerOutput),
    ]);

    $this->artisan('dependency:versions')->assertSuccessful();

    expect(DB::table('composer_versions')->get())
        ->toHaveCount(1)
        ->sequence(fn ($row) => $row
            ->name->toBe('vendor/package')
            ->current_version->toBe('1.0.0')
            ->latest_version->toBe('1.1.0')
            ->status->toBe('semver-safe-update'));
});
