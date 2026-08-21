<x-filament-widgets::widget>
    <x-filament::section
            :heading="$heading"
            :description="$description"
    >
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
        @elseif($dependencies->isNotEmpty())
            <div class="fsv-list">
                <div class="fsv-row">
                    <span class="fsv-col-label">
                        {{ __('filament-system-versions::system-versions.widgets.dependency.table.name') }}
                    </span>
                    <span class="fsv-col-label">
                        {{ __('filament-system-versions::system-versions.widgets.dependency.table.version') }}
                    </span>
                </div>
                @foreach($dependencies as $dependency)
                    <div class="fsv-row">
                        <span class="fsv-label">
                            <a href="https://packagist.org/packages/{{ $dependency->name }}" target="_blank" class="fsv-link">
                                {{ $dependency->name }}
                            </a>
                            @if($dependency->abandoned)
                                <x-filament::badge color="danger">
                                    {{ __('filament-system-versions::system-versions.widgets.dependency.abandoned') }}
                                </x-filament::badge>
                            @endif
                        </span>
                        <span class="fsv-value">
                            @if($dependency->status !== 'up-to-date')
                                <x-filament::badge :color="$dependency->badge_color">
                                    {{ $dependency->current_version }} &rarr; {{ $dependency->latest_version }}
                                </x-filament::badge>
                            @else
                                {{ $dependency->current_version }}
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="fsv-empty">
                <div class="fsv-empty-icon">
                    <x-filament::icon icon="heroicon-o-check-circle" />
                </div>

                <p class="fsv-empty-text">
                    {{ __('filament-system-versions::system-versions.widgets.dependency.empty') }}
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
