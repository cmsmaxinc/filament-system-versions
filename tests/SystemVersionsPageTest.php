<?php

use Cmsmaxinc\FilamentSystemVersions\DependencyVersionRefresher;
use Cmsmaxinc\FilamentSystemVersions\DependencyVersionRefreshResult;
use Cmsmaxinc\FilamentSystemVersions\Filament\Pages\SystemVersions;
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\DependencyWidget;
use Cmsmaxinc\FilamentSystemVersions\Filament\Widgets\SystemVersionStats;
use Cmsmaxinc\FilamentSystemVersions\FilamentSystemVersionsPlugin;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Panel;
use Livewire\Attributes\On;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestSystemVersionsPage extends SystemVersions
{
    /** @return array<Action> */
    public function headerActions(): array
    {
        return $this->getHeaderActions();
    }

    public function runDependencyRefresh(DependencyVersionRefresher $refresher): void
    {
        $this->refreshDependencyVersions($refresher);
    }
}

beforeEach(function () {
    session()->forget('filament.notifications');

    $this->systemVersionsPlugin = FilamentSystemVersionsPlugin::make();
    $this->panel = Panel::make()
        ->id('test')
        ->plugin($this->systemVersionsPlugin);

    Filament::setCurrentPanel($this->panel);
});

it('provides an authorized check now action', function () {
    $this->systemVersionsPlugin->authorize(true);

    $action = (new TestSystemVersionsPage)->headerActions()[0];

    expect($action)->toBeInstanceOf(Action::class)
        ->and($action->getName())->toBe('checkNow')
        ->and($action->getLabel())->toBe('Check now')
        ->and($action->isAuthorized())->toBeTrue();
});

it('notifies the administrator and refreshes the widgets after success', function () {
    $this->systemVersionsPlugin->authorize(true);

    $refresher = Mockery::mock(DependencyVersionRefresher::class);
    $refresher->shouldReceive('refresh')
        ->once()
        ->andReturn(DependencyVersionRefreshResult::Refreshed);

    (new TestSystemVersionsPage)->runDependencyRefresh($refresher);

    Notification::assertNotified('Dependency versions updated');
});

it('shows a safe notification when the check fails', function () {
    $this->systemVersionsPlugin->authorize(true);

    $refresher = Mockery::mock(DependencyVersionRefresher::class);
    $refresher->shouldReceive('refresh')
        ->once()
        ->andReturn(DependencyVersionRefreshResult::Failed);

    (new TestSystemVersionsPage)->runDependencyRefresh($refresher);

    Notification::assertNotified('Unable to check dependency versions');
});

it('denies the page and action to unauthorized users', function () {
    $this->systemVersionsPlugin->authorize(false);

    $page = new TestSystemVersionsPage;
    $action = $page->headerActions()[0];
    $refresher = Mockery::mock(DependencyVersionRefresher::class);
    $refresher->shouldReceive('refresh')->never();

    expect(SystemVersions::canAccess())->toBeFalse()
        ->and($action->isAuthorized())->toBeFalse()
        ->and(fn () => $page->runDependencyRefresh($refresher))
        ->toThrow(HttpException::class);
});

it('refreshes dependency widgets after a successful check', function (string $widget) {
    $attributes = (new ReflectionMethod($widget, 'refreshDependencyVersions'))
        ->getAttributes(On::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->newInstance()->event)
        ->toBe(SystemVersions::DEPENDENCY_VERSIONS_REFRESHED_EVENT);
})->with([
    'dependency widget' => [DependencyWidget::class],
    'system version stats' => [SystemVersionStats::class],
]);
