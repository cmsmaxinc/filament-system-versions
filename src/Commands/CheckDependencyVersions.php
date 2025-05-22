<?php

namespace Cmsmaxinc\FilamentSystemVersions\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use TypeError;

class CheckDependencyVersions extends Command
{
    public $signature = 'dependency:versions';

    public $description = 'Check the versions of all dependencies.';

    public function handle(): void
    {
        // TODO: Grab all packages including up-to-date ones
        $result = Process::run('composer show --latest --format=json');

        if ($result->failed()) {
            throw new RuntimeException('Composer outdated failed: ' . $result->errorOutput());
        }

        try {
            $results = json_decode($result->output(), flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException | TypeError $e) {
            // Get a sample of the output (first 1000 chars) to avoid huge log entries
            $output = $result->output();
            $sampleOutput = substr($output, 0, 1000) . (strlen($output) > 1000 ? '...(truncated)' : '');

            $errorMessage = 'JSON decode failed: ' . $e->getMessage();
            logger()->error($errorMessage, [
                'output_sample' => $sampleOutput,
                'output_length' => strlen($output),
                'exception' => $e,
            ]);

            // Report the exception with context for Nightwatch or similar error tracker
            report(new \Exception($errorMessage . ' See logs for details.', 0, $e));

            return;
        }

        // Truncate the table before inserting new data to make sure that the table is always up-to-date
        DB::table(config('system-versions.database.table_name', 'composer_versions'))->truncate();

        foreach ($results->installed as $package) {
            if ($package->version != $package->latest) {
                $this->info("{$package->name} is outdated. Current version: {$package->version}. Latest version: {$package->latest}");
            }

            DB::table(config('system-versions.database.table_name', 'composer_versions'))->insert([
                'name' => $package->name,
                'current_version' => $package->version,
                'latest_version' => $package->latest,
                'status' => $package->{'latest-status'},
                'direct_dependency' => $package->{'direct-dependency'},
                'description' => $package->description,
                'abandoned' => is_bool($package->abandoned) ? $package->abandoned : true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
