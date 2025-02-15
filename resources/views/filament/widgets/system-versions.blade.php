<x-filament-widgets::widget>
    <x-filament::section
            :heading="$heading"
            :description="$description"
    >
        @if($packages->isNotEmpty())
            <table class="w-100 text-sm">
                <thead>
                <tr class="text-sm">
                    <th class="text-left font-semibold p-2">
                        {{ __('filament-system-versions::system-versions.widget.table.name') }}
                    </th>
                    <th class="text-left font-semibold p-2">
                        {{ __('filament-system-versions::system-versions.widget.table.current') }}
                    </th>
                    <th class="text-left font-semibold p-2">
                        {{ __('filament-system-versions::system-versions.widget.table.latest') }}
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($packages as $package)
                    <tr class="border-b border-t flex-inline">
                        <td class="p-2">
                            <a href="https://packagist.org/packages/{{ $package->name }}" target="_blank" class="hover:text-primary-600 hover:underline">
                                {{ $package->name }}
                            </a>
                        </td>
                        <td class="p-2">
                            <x-filament::badge color="gray" class="inline-flex whitespace-nowrap flex-none">
                                {{ $package->current_version }}
                            </x-filament::badge>
                        </td>
                        <td class="p-2">
                            <x-filament::badge color="warning" class="inline-flex whitespace-nowrap flex-none">
                                {{ $package->latest_version }}
                            </x-filament::badge>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                    <x-filament::icon
                            icon="heroicon-o-check-circle"
                            class="fi-ta-empty-state-icon h-6 w-6 text-primary-600 dark:text-primary-600"
                    />
                </div>

                <p class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('filament-system-versions::system-versions.widget.empty') }}
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
