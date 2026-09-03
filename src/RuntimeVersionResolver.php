<?php

namespace Cmsmaxinc\FilamentSystemVersions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Throwable;

class RuntimeVersionResolver
{
    private const UNAVAILABLE = '__filament_system_versions_unavailable__';

    /**
     * @return array{composer: string|null, node: string|null, npm: string|null}
     */
    public function versions(): array
    {
        return [
            'composer' => $this->resolve('composer', $this->composerCommand(), '/Composer(?:\s+version)?\s+([^\s]+)/i'),
            'node' => $this->resolve('node', $this->command('node_path', 'node', '--version'), '/v?([^\s]+)/i'),
            'npm' => $this->resolve('npm', $this->command('npm_path', 'npm', '--version'), '/([^\s]+)/'),
        ];
    }

    /** @return string|array<int, string> */
    private function composerCommand(): string | array
    {
        $configuredComposer = (string) config('filament-system-versions.paths.composer_path');
        $php = config('filament-system-versions.paths.php_path');

        if (is_string($php) && $php !== '' && $configuredComposer !== '') {
            return [$php, $configuredComposer, '--version', '--no-ansi'];
        }

        if ($configuredComposer !== '') {
            return [$configuredComposer, '--version', '--no-ansi'];
        }

        return 'composer --version --no-ansi';
    }

    /** @return string|array<int, string> */
    private function command(string $configKey, string $default, string $arguments): string | array
    {
        $binary = (string) config("filament-system-versions.paths.{$configKey}");
        $binary = $binary !== '' ? $binary : $default;

        if ($binary !== $default) {
            return [$binary, $arguments];
        }

        return "{$default} {$arguments}";
    }

    /** @param string|array<int, string> $command */
    private function resolve(string $name, string | array $command, string $pattern): ?string
    {
        $seconds = max(0, (int) config('filament-system-versions.inventory.runtime_cache_seconds', 3600));
        $resolver = fn (): ?string => $this->run($command, $pattern);

        if ($seconds === 0) {
            return $resolver();
        }

        $value = Cache::remember(
            'filament-system-versions.runtime.' . hash('sha256', $name . '|' . json_encode($command)),
            $seconds,
            fn (): string => $resolver() ?? self::UNAVAILABLE,
        );

        return $value === self::UNAVAILABLE ? null : $value;
    }

    /** @param string|array<int, string> $command */
    private function run(string | array $command, string $pattern): ?string
    {
        try {
            $result = Process::path(base_path())->timeout(5)->run($command);
        } catch (Throwable) {
            return null;
        }

        if (! $result->successful()) {
            return null;
        }

        $output = trim($result->output());

        if (preg_match($pattern, $output, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }
}
