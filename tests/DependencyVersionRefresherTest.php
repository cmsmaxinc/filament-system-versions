<?php

use Cmsmaxinc\FilamentSystemVersions\DependencyVersionRefresher;
use Cmsmaxinc\FilamentSystemVersions\DependencyVersionRefreshResult;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config()->set('cache.default', 'array');
    Cache::clear();
});

it('runs the existing dependency versions command', function () {
    $artisan = Mockery::mock(Kernel::class);
    $artisan->shouldReceive('call')
        ->once()
        ->with('dependency:versions')
        ->andReturn(Command::SUCCESS);
    app()->instance(Kernel::class, $artisan);

    expect(app(DependencyVersionRefresher::class)->refresh())
        ->toBe(DependencyVersionRefreshResult::Refreshed);
});

it('returns a failure result when the command fails', function () {
    $artisan = Mockery::mock(Kernel::class);
    $artisan->shouldReceive('call')
        ->once()
        ->with('dependency:versions')
        ->andReturn(Command::FAILURE);
    app()->instance(Kernel::class, $artisan);

    expect(app(DependencyVersionRefresher::class)->refresh())
        ->toBe(DependencyVersionRefreshResult::Failed);
});

it('prevents overlapping dependency checks', function () {
    $lock = Cache::lock(
        DependencyVersionRefresher::LOCK_KEY,
        DependencyVersionRefresher::LOCK_SECONDS,
    );

    expect($lock->get())->toBeTrue();

    try {
        $artisan = Mockery::mock(Kernel::class);
        $artisan->shouldReceive('call')->never();
        app()->instance(Kernel::class, $artisan);

        expect(app(DependencyVersionRefresher::class)->refresh())
            ->toBe(DependencyVersionRefreshResult::AlreadyRunning);
    } finally {
        $lock->release();
    }
});
