<?php

namespace Cmsmaxinc\FilamentSystemVersions;

use JsonException;

class ProjectDependencyInventory
{
    /**
     * @return array<string, 'runtime'|'development'>
     */
    public function composerScopes(): array
    {
        $lock = $this->readJson($this->configuredPath('composer_lock', 'composer.lock'));

        if ($lock === null) {
            return [];
        }

        $scopes = [];

        foreach ($lock['packages'] ?? [] as $package) {
            if (is_array($package) && is_string($package['name'] ?? null)) {
                $scopes[$package['name']] = 'runtime';
            }
        }

        foreach ($lock['packages-dev'] ?? [] as $package) {
            if (is_array($package) && is_string($package['name'] ?? null)) {
                $scopes[$package['name']] = 'development';
            }
        }

        return $scopes;
    }

    /**
     * @return array{
     *     available: bool,
     *     unavailable_reason: 'missing'|'invalid'|'unsupported'|null,
     *     lockfile_version: int|null,
     *     dependencies: array<int, array{
     *         name: string,
     *         version: string,
     *         path: string,
     *         direct: bool,
     *         scope: 'runtime'|'development'|'optional'|'peer'
     *     }>
     * }
     */
    public function npm(): array
    {
        $lockPath = $this->configuredPath('package_lock', 'package-lock.json');

        if (! is_file($lockPath) || ! is_readable($lockPath)) {
            return $this->unavailableNpmInventory('missing');
        }

        $lock = $this->readJson($lockPath);

        if ($lock === null) {
            return $this->unavailableNpmInventory('invalid');
        }

        if (! is_array($lock['packages'] ?? null)) {
            return $this->unavailableNpmInventory(
                ($lock['lockfileVersion'] ?? null) === 1 ? 'unsupported' : 'invalid'
            );
        }

        $manifest = $this->readJson($this->configuredPath('package_json', 'package.json'));
        $manifest ??= is_array($lock['packages'][''] ?? null) ? $lock['packages'][''] : [];

        $directScopes = $this->npmDirectScopes($manifest);
        $linkedTargets = $this->npmLinkedTargets($lock['packages']);
        $dependencies = [];

        foreach ($lock['packages'] as $path => $package) {
            if ($path === '' || ! is_string($path) || ! is_array($package) || isset($linkedTargets[$path])) {
                continue;
            }

            $installedName = $this->npmPackageNameFromPath($path);
            $package = $this->resolveLinkedNpmPackage($package, $lock['packages']);
            $name = is_string($package['name'] ?? null)
                ? $package['name']
                : $installedName;
            $version = $package['version'] ?? null;

            if ($name === null || ! is_string($version) || $version === '') {
                continue;
            }

            $direct = $installedName !== null
                && $this->isRootNpmPackagePath($path)
                && isset($directScopes[$installedName]);
            $scope = $direct
                ? $directScopes[$installedName]
                : $this->npmTransitiveScope($package);

            $dependencies[] = [
                'name' => $name,
                'version' => $version,
                'path' => $path,
                'direct' => $direct,
                'scope' => $scope,
            ];
        }

        usort($dependencies, fn (array $left, array $right): int => [
            $left['direct'] ? 0 : 1,
            $this->npmScopeOrder($left['scope']),
            strtolower($left['name']),
            $left['path'],
        ] <=> [
            $right['direct'] ? 0 : 1,
            $this->npmScopeOrder($right['scope']),
            strtolower($right['name']),
            $right['path'],
        ]);

        return [
            'available' => true,
            'unavailable_reason' => null,
            'lockfile_version' => is_int($lock['lockfileVersion'] ?? null) ? $lock['lockfileVersion'] : null,
            'dependencies' => $dependencies,
        ];
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return array<string, 'runtime'|'development'|'optional'|'peer'>
     */
    private function npmDirectScopes(array $manifest): array
    {
        $scopes = [];

        foreach ([
            'dependencies' => 'runtime',
            'devDependencies' => 'development',
            'optionalDependencies' => 'optional',
            'peerDependencies' => 'peer',
        ] as $section => $scope) {
            foreach ($manifest[$section] ?? [] as $name => $constraint) {
                if (is_string($name)) {
                    $scopes[$name] = $scope;
                }
            }
        }

        return $scopes;
    }

    /**
     * @param  array<string, mixed>  $packages
     * @return array<string, true>
     */
    private function npmLinkedTargets(array $packages): array
    {
        $targets = [];

        foreach ($packages as $package) {
            if (is_array($package) && ($package['link'] ?? false) === true && is_string($package['resolved'] ?? null)) {
                $targets[$package['resolved']] = true;
            }
        }

        return $targets;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $packages
     * @return array<string, mixed>
     */
    private function resolveLinkedNpmPackage(array $package, array $packages): array
    {
        if (($package['link'] ?? false) !== true || ! is_string($package['resolved'] ?? null)) {
            return $package;
        }

        $target = $packages[$package['resolved']] ?? null;

        if (! is_array($target)) {
            return $package;
        }

        return array_replace($package, $target);
    }

    /**
     * @param  array<string, mixed>  $package
     * @return 'runtime'|'development'|'optional'|'peer'
     */
    private function npmTransitiveScope(array $package): string
    {
        if (($package['optional'] ?? false) === true || ($package['devOptional'] ?? false) === true) {
            return 'optional';
        }

        if (($package['peer'] ?? false) === true) {
            return 'peer';
        }

        if (($package['dev'] ?? false) === true) {
            return 'development';
        }

        return 'runtime';
    }

    /**
     * @param  'missing'|'invalid'|'unsupported'  $reason
     * @return array{
     *     available: false,
     *     unavailable_reason: 'missing'|'invalid'|'unsupported',
     *     lockfile_version: null,
     *     dependencies: array{}
     * }
     */
    private function unavailableNpmInventory(string $reason): array
    {
        return [
            'available' => false,
            'unavailable_reason' => $reason,
            'lockfile_version' => null,
            'dependencies' => [],
        ];
    }

    private function npmScopeOrder(string $scope): int
    {
        return match ($scope) {
            'runtime' => 0,
            'development' => 1,
            'optional' => 2,
            'peer' => 3,
            default => 4,
        };
    }

    private function npmPackageNameFromPath(string $path): ?string
    {
        $position = strrpos($path, 'node_modules/');

        if ($position === false) {
            return null;
        }

        $name = substr($path, $position + strlen('node_modules/'));

        return $name !== '' ? $name : null;
    }

    private function isRootNpmPackagePath(string $path): bool
    {
        if (! str_starts_with($path, 'node_modules/')) {
            return false;
        }

        $remainder = substr($path, strlen('node_modules/'));

        if (str_starts_with($remainder, '@')) {
            $parts = explode('/', $remainder);

            return count($parts) === 2;
        }

        return ! str_contains($remainder, '/');
    }

    private function configuredPath(string $key, string $default): string
    {
        $path = (string) config("filament-system-versions.inventory.{$key}", $default);

        if ($path === '') {
            return base_path($default);
        }

        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readJson(string $path): ?array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        try {
            $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
