<?php

namespace Cmsmaxinc\FilamentSystemVersions\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class CheckComposerVersions extends Command
{
    public $signature = 'composer:outdated';

    public $description = 'Check for outdated composer packages';

    public function handle(): void
    {
        // TODO: Grab all packages including up-to-date ones
        $result = Process::run('composer outdated -D --format=json');

        if ($result->failed()) {
            throw new RuntimeException('Composer outdated failed: ' . $result->errorOutput());
        }

        $results = json_decode($result->output(), flags: JSON_THROW_ON_ERROR);

        // Truncate the table before inserting new data to make sure that the table is always up-to-date
        DB::table(config('system-versions.database.table_name', 'composer_versions'))->truncate();

        foreach ($results->installed as $package) {
            $this->info("{$package->name} is outdated. Current version: {$package->version}. Latest version: {$package->latest}");

            DB::table(config('system-versions.database.table_name', 'composer_versions'))->insert([
                'name' => $package->name,
                'current_version' => $package->version,
                'latest_version' => $package->latest,
                'status' => $package->{'latest-status'},
                'description' => $package->description,
                'abandoned' => $package->abandoned,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
