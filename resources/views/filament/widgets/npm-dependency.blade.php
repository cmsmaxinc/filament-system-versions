<x-filament-widgets::widget>
    <x-filament::section :heading="$heading" :description="$description">
        @if(! $available)
            <div class="fsv-empty">
                <div class="fsv-empty-icon">
                    <x-filament::icon icon="heroicon-o-information-circle" />
                </div>
                <p class="fsv-empty-text">
                    {{ __('filament-system-versions::system-versions.widgets.npm.missing_lock') }}
                </p>
            </div>
        @else
            <div class="fsv-summary" role="group" aria-label="{{ __('filament-system-versions::system-versions.widgets.npm.summary_label') }}">
                <span><strong>{{ number_format($total) }}</strong> {{ __('filament-system-versions::system-versions.widgets.npm.instances') }}</span>
                <span><strong>{{ number_format($unique) }}</strong> {{ __('filament-system-versions::system-versions.widgets.npm.unique_versions') }}</span>
                @if($lockfileVersion !== null)
                    <span>{{ __('filament-system-versions::system-versions.widgets.npm.lockfile') }} <strong>v{{ $lockfileVersion }}</strong></span>
                @endif
            </div>

            <div class="fsv-groups">
                @foreach($groups as $group)
                    <details class="fsv-group" @if($group['open']) open @endif>
                        <summary class="fsv-group-summary">
                            <span>{{ $group['label'] }}</span>
                            <span class="fsv-count">{{ number_format($group['dependencies']->count()) }}</span>
                        </summary>
                        <div class="fsv-package-list">
                            <div class="fsv-package-row fsv-npm-row fsv-package-header" aria-hidden="true">
                                <span>{{ __('filament-system-versions::system-versions.widgets.npm.table.name') }}</span>
                                <span>{{ __('filament-system-versions::system-versions.widgets.npm.table.version') }}</span>
                            </div>
                            @foreach($group['dependencies'] as $dependency)
                                <div class="fsv-package-row fsv-npm-row">
                                    <span class="fsv-package-name">
                                        <a href="https://www.npmjs.com/package/{{ $dependency['name'] }}" target="_blank" rel="noopener noreferrer" class="fsv-link">
                                            {{ $dependency['name'] }}
                                        </a>
                                        <span class="fsv-meta">{{ $dependency['path'] }}</span>
                                    </span>
                                    <span class="fsv-version">{{ $dependency['version'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
