<x-filament-widgets::widget>
    <x-filament::section :heading="$heading" :description="$description">
        @if($missingTable || ! $hasData)
            <div class="fsv-empty">
                <div class="fsv-empty-icon">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" />
                </div>
                <p class="fsv-empty-text">
                    {{ $missingTable
                        ? __('filament-system-versions::system-versions.widgets.dependency.missing_table')
                        : __('filament-system-versions::system-versions.widgets.dependency.no_data') }}
                </p>
            </div>
        @else
            <div class="fsv-summary" role="group" aria-label="{{ __('filament-system-versions::system-versions.widgets.dependency.summary_label') }}">
                <span><strong>{{ number_format($total) }}</strong> {{ __('filament-system-versions::system-versions.widgets.dependency.total') }}</span>
                <span><strong>{{ number_format($updates) }}</strong> {{ __('filament-system-versions::system-versions.widgets.dependency.updates') }}</span>
                <span><strong>{{ number_format($abandoned) }}</strong> {{ __('filament-system-versions::system-versions.widgets.dependency.abandoned') }}</span>
            </div>

            <div class="fsv-groups">
                @foreach($groups as $group)
                    <details class="fsv-group" @if($group['open']) open @endif>
                        <summary class="fsv-group-summary">
                            <span>{{ $group['label'] }}</span>
                            <span class="fsv-count">{{ number_format($group['dependencies']->count()) }}</span>
                        </summary>
                        <div class="fsv-package-list">
                            <div class="fsv-package-row fsv-package-header" aria-hidden="true">
                                <span>{{ __('filament-system-versions::system-versions.widgets.dependency.table.name') }}</span>
                                <span>{{ __('filament-system-versions::system-versions.widgets.dependency.table.version') }}</span>
                                <span>{{ __('filament-system-versions::system-versions.widgets.dependency.table.status') }}</span>
                            </div>
                            @foreach($group['dependencies'] as $dependency)
                                <div class="fsv-package-row">
                                    <span class="fsv-package-name">
                                        <a href="https://packagist.org/packages/{{ $dependency->name }}" target="_blank" rel="noopener noreferrer" class="fsv-link">
                                            {{ $dependency->name }}
                                        </a>
                                        @if($dependency->abandoned)
                                            <x-filament::badge color="danger">
                                                {{ __('filament-system-versions::system-versions.widgets.dependency.abandoned') }}
                                            </x-filament::badge>
                                        @endif
                                    </span>
                                    <span class="fsv-version">
                                        {{ $dependency->current_version }}
                                        @if($dependency->status !== 'up-to-date')
                                            <span aria-hidden="true">&rarr;</span>
                                            <span class="fsv-visually-hidden">{{ __('filament-system-versions::system-versions.widgets.dependency.to') }}</span>
                                            {{ $dependency->latest_version }}
                                        @endif
                                    </span>
                                    <span class="fsv-package-status">
                                        <x-filament::badge :color="$dependency->badge_color">
                                            {{ $dependency->status_label }}
                                        </x-filament::badge>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
