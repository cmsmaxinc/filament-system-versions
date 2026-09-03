<x-filament-widgets::widget>
    <x-filament::section
            :heading="$heading"
            :description="$description"
    >
        <div class="fsv-list">
            @foreach($details as $detail)
                <div class="fsv-row">
                    <span class="fsv-label">{{ $detail['label'] }}</span>
                    <span class="fsv-value">
                        @if(filled($detail['url'] ?? null))
                            <a href="{{ $detail['url'] }}" target="_blank" rel="noopener noreferrer" class="fsv-link">
                                {{ $detail['value'] }}
                            </a>
                        @else
                            {{ $detail['value'] }}
                        @endif
                    </span>
                </div>
            @endforeach
            <div class="fsv-row">
                <span class="fsv-label">{{ __('filament-system-versions::system-versions.widgets.system.details.debug') }}</span>
                <span class="fsv-value">
                    <x-filament::badge :color="$debugColor">
                        {{ $debug
                            ? __('filament-system-versions::system-versions.widgets.system.details.debug_enabled')
                            : __('filament-system-versions::system-versions.widgets.system.details.debug_disabled') }}
                    </x-filament::badge>
                </span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
