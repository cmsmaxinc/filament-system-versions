<?php

namespace Cmsmaxinc\FilamentSystemVersions\Commands;

use Illuminate\Console\Command;

class FilamentSystemVersionsCommand extends Command
{
    public $signature = 'filament-system-versions';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
