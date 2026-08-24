<?php

namespace Cmsmaxinc\FilamentSystemVersions;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class DependencyVersionRefresher
{
    public const string LOCK_KEY = 'filament-system-versions:dependency-refresh';

    public const int LOCK_SECONDS = 300;

    public function __construct(private readonly Kernel $artisan) {}

    public function refresh(): DependencyVersionRefreshResult
    {
        $lock = null;
        $acquired = false;

        try {
            $lock = Cache::lock(self::LOCK_KEY, self::LOCK_SECONDS);
            $acquired = $lock->get();

            if (! $acquired) {
                return DependencyVersionRefreshResult::AlreadyRunning;
            }

            $exitCode = $this->artisan->call('dependency:versions');

            if ($exitCode !== Command::SUCCESS) {
                report(new RuntimeException("The dependency:versions command exited with code {$exitCode}."));

                return DependencyVersionRefreshResult::Failed;
            }

            return DependencyVersionRefreshResult::Refreshed;
        } catch (Throwable $exception) {
            report($exception);

            return DependencyVersionRefreshResult::Failed;
        } finally {
            if ($acquired && $lock instanceof Lock) {
                $lock->release();
            }
        }
    }
}
