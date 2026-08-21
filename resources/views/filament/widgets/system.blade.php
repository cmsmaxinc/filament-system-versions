<x-filament-widgets::widget>
    <x-filament::section
            :heading="$heading"
            :description="$description"
    >
        <div class="fsv-list">
            @foreach($details as $key => $value)
                <div class="fsv-row">
                    <span class="fsv-label">{{ $key }}</span>
                    <span class="fsv-value">{{ $value }}</span>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
