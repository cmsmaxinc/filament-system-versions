<?php

declare(strict_types=1);

namespace Cmsmaxinc\FilamentSystemVersions\Filament\Pages;

use BackedEnum;
use Cmsmaxinc\FilamentSystemVersions\DependencyVersionRefresher;
use Cmsmaxinc\FilamentSystemVersions\DependencyVersionRefreshResult;
use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SystemVersions extends Page
{
    public const string DEPENDENCY_VERSIONS_REFRESHED_EVENT = 'dependency-versions-refreshed';

    protected string $view = 'filament-system-versions::filament.pages.system-versions';

    public static function canAccess(): bool
    {
        return FilamentSystemVersionsPlugin::get()->isAuthorized();
    }

    public static function getNavigationLabel(): string
    {
        return FilamentSystemVersionsPlugin::get()->getNavigationLabel();
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return FilamentSystemVersionsPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return FilamentSystemVersionsPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentSystemVersionsPlugin::get()->getNavigationSort();
    }

    /** @return array<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('checkNow')
                ->label(__('filament-system-versions::system-versions.actions.check_now.label'))
                ->icon('heroicon-o-arrow-path')
                ->authorize(fn (): bool => static::canAccess())
                ->requiresConfirmation()
                ->modalHeading(__('filament-system-versions::system-versions.actions.check_now.modal_heading'))
                ->modalDescription(__('filament-system-versions::system-versions.actions.check_now.modal_description'))
                ->modalSubmitActionLabel(__('filament-system-versions::system-versions.actions.check_now.modal_submit'))
                ->action(function (DependencyVersionRefresher $refresher): void {
                    $this->refreshDependencyVersions($refresher);
                }),
        ];
    }

    protected function refreshDependencyVersions(DependencyVersionRefresher $refresher): void
    {
        abort_unless(static::canAccess(), 403);

        match ($refresher->refresh()) {
            DependencyVersionRefreshResult::Refreshed => $this->notifyRefreshSucceeded(),
            DependencyVersionRefreshResult::AlreadyRunning => $this->notifyRefreshAlreadyRunning(),
            DependencyVersionRefreshResult::Failed => $this->notifyRefreshFailed(),
        };
    }

    private function notifyRefreshSucceeded(): void
    {
        $this->dispatch(self::DEPENDENCY_VERSIONS_REFRESHED_EVENT);

        Notification::make()
            ->title(__('filament-system-versions::system-versions.actions.check_now.success_title'))
            ->body(__('filament-system-versions::system-versions.actions.check_now.success_body'))
            ->success()
            ->send();
    }

    private function notifyRefreshAlreadyRunning(): void
    {
        Notification::make()
            ->title(__('filament-system-versions::system-versions.actions.check_now.already_running_title'))
            ->body(__('filament-system-versions::system-versions.actions.check_now.already_running_body'))
            ->warning()
            ->send();
    }

    private function notifyRefreshFailed(): void
    {
        Notification::make()
            ->title(__('filament-system-versions::system-versions.actions.check_now.failure_title'))
            ->body(__('filament-system-versions::system-versions.actions.check_now.failure_body'))
            ->danger()
            ->send();
    }
}
