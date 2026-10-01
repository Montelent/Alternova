@php
    $count = $this->count();
    $cards = $this->cards();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="text-gray-950 dark:text-white">Needs attention</span>
            @if($count > 0)
                <span class="ml-2 inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-950 px-2 py-0.5 text-xs font-bold text-amber-900 dark:text-amber-200">
                    {{ $count }}
                </span>
            @endif
        </x-slot>
        <x-slot name="description">
            @if($count === 0)
                <span class="text-gray-600 dark:text-gray-400">Nothing urgent. Your catalog looks clean.</span>
            @else
                <span class="text-gray-600 dark:text-gray-400">Items that block a polished public site. Open the full queue for one-click fixes.</span>
            @endif
        </x-slot>

        @if($count > 0)
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 mb-4">
                @foreach($cards as $card)
                    <a href="{{ $card['href'] }}"
                        class="rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30 px-3 py-2 text-sm hover:border-amber-400 transition">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $card['label'] }}</span>
                        <span class="ml-1 font-bold text-amber-800 dark:text-amber-300">{{ $card['count'] }}</span>
                    </a>
                @endforeach
            </div>
            <x-filament::button tag="a" href="{{ \App\Filament\Pages\NeedsAttentionPage::getUrl() }}" color="warning" size="sm">
                Open needs attention
            </x-filament::button>
        @else
            <x-filament::button tag="a" href="{{ \App\Filament\Pages\GettingStartedPage::getUrl() }}" color="gray" size="sm">
                Getting started checklist
            </x-filament::button>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
