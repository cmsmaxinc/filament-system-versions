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
        $result = Process::run([
            config('filament-system-versions.paths.php_path', 'php'),
            config('filament-system-versions.paths.composer_path', 'composer'),
            'show',
            '--latest',
            '--format=json',
        ]);

        if ($result->failed()) {
            throw new RuntimeException('Composer outdated failed: ' . $result->errorOutput());
        }

        $output = $this->cleanJsonOutput($result->output());

        try {
            $results = json_decode($output, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException | TypeError $e) {
            // Get a sample of the output (first 1000 chars) to avoid huge log entries
            $sampleOutput = substr($output, 0, 1000) . (strlen($output) > 1000 ? '...(truncated)' : '');

            $errorMessage = 'JSON decode failed: ' . $e->getMessage();
            logger()->error($errorMessage, [
                'output_sample' => $sampleOutput,
                'output_length' => strlen($output),
                'original_output_length' => strlen($result->output()),
                'exception' => $e,
            ]);

            // Report to error tracking (Nightwatch) and continue gracefully
            report(new \Exception($errorMessage . ' See logs for details.', 0, $e));

            // Log the error but don't re-throw - just return gracefully
            $this->error('Failed to parse composer output. Check logs for details.');

            return;
        }

        // Validate that we have the expected structure
        if (! isset($results->installed) || ! is_array($results->installed)) {
            $this->error('Invalid composer output structure. Expected "installed" array.');

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

    /**
     * Clean the composer output to ensure valid JSON
     */
    private function cleanJsonOutput(string $output): string
    {
        // Remove BOM if present
        $output = preg_replace('/^\xEF\xBB\xBF/', '', $output);

        // Remove any non-JSON content before the opening brace
        $jsonStart = strpos($output, '{');
        if ($jsonStart !== false && $jsonStart > 0) {
            $output = substr($output, $jsonStart);
        }

        // Remove any trailing non-JSON content after the last closing brace
        $jsonEnd = strrpos($output, '}');
        if ($jsonEnd !== false) {
            $output = substr($output, 0, $jsonEnd + 1);
        }

        // Trim whitespace
        return trim($output);
    }
}
