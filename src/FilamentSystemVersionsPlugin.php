<?php

namespace Cmsmaxinc\FilamentSystemVersions;

use Closure;
use Cmsmaxinc\FilamentSystemVersions\Filament\Pages\SystemVersions;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use UnitEnum;

class FilamentSystemVersionsPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool | Closure $authorizeUsing = true;

    protected string | UnitEnum | Closure | null $navigationGroup = null;

    protected string | \BackedEnum | Closure | null $navigationIcon = null;

    protected string | Closure | null $navigationLabel = null;

    protected int | Closure | null $navigationSort = null;

    protected array $statsPackages = [];

    public function getId(): string
    {
        return 'filament-system-versions';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            SystemVersions::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function authorize(bool | Closure $callback = true): static
    {
        $this->authorizeUsing = $callback;

        return $this;
    }

    public function isAuthorized(): bool
    {
        return $this->evaluate($this->authorizeUsing);
    }

    public function navigationGroup(string | UnitEnum | Closure | null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): string | UnitEnum | null
    {
        return $this->evaluate($this->navigationGroup) ?? __('filament-system-versions::system-versions.navigation.group');
    }

    public function navigationIcon(string | \BackedEnum | Closure | null $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): string | \BackedEnum | null
    {
        return $this->evaluate($this->navigationIcon) ?? 'heroicon-o-document-text';
    }

    public function navigationLabel(string | Closure | null $label): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function getNavigationLabel(): string
    {
        return $this->evaluate($this->navigationLabel) ?? __('filament-system-versions::system-versions.navigation.label');
    }

    public function navigationSort(int | Closure | null $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->evaluate($this->navigationSort) ?? 99999;
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function statsPackages(array $packages): static
    {
        $this->statsPackages = $packages;

        return $this;
    }

    public function getStatsPackages(): array
    {
        // Default packages if none specified
        if (empty($this->statsPackages)) {
            return [
                ['label' => 'Laravel', 'package' => 'laravel/framework'],
                ['label' => 'FilamentPHP', 'package' => 'filament/filament'],
            ];
        }

        return $this->statsPackages;
    }
}
