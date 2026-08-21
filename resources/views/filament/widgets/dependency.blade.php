<x-filament-widgets::widget>
    <x-filament::section
            :heading="$heading"
            :description="$description"
    >
        @if($dependencies->isNotEmpty())
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
                        </span>
                        <span class="fsv-value">
                            <x-filament::badge color="warning">{{ $dependency->current_version }} &rarr; {{ $dependency->latest_version }}</x-filament::badge>
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
