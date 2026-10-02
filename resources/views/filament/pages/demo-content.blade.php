<x-filament-panels::page>
    @php($c = $this->counts)

    <x-filament::section class="mb-6">
        <x-slot name="heading">Current catalog size</x-slot>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-gray-600 dark:text-gray-400">Proprietary tools</p>
                <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $c['tools'] }}</p>
                <p class="text-xs text-gray-500">{{ $c['tools_published'] }} published</p>
            </div>
            <div>
                <p class="text-gray-600 dark:text-gray-400">Alternatives</p>
                <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $c['alts'] }}</p>
                <p class="text-xs text-gray-500">{{ $c['alts_published'] }} published</p>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section class="mb-6">
        <x-slot name="heading">One-click demo data</x-slot>
        <x-slot name="description">
            Creates sample proprietary tools (Notion, Slack, Figma) and open-source alternatives if those slugs do not exist yet. Safe to run more than once — existing rows are skipped, never deleted.
        </x-slot>

        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1 mb-4">
            <li>Notion → AppFlowy, Outline</li>
            <li>Slack → Rocket.Chat, Mattermost</li>
            <li>Figma → Penpot</li>
        </ul>

        <x-filament::button wire:click="seedDemo" icon="heroicon-o-sparkles" color="primary"
            wire:confirm="Seed demo tools and alternatives? Existing matching slugs will be left alone.">
            Seed demo catalog
        </x-filament::button>

        @if($lastReport)
            <p class="mt-4 text-sm text-gray-800 dark:text-gray-200 font-medium">{{ $lastReport }}</p>
        @endif
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">After seeding</x-slot>
        <ol class="list-decimal list-inside text-sm text-gray-700 dark:text-gray-300 space-y-2">
            <li>Open the public homepage and <a class="text-primary-600 underline" href="{{ url('/alternatives') }}" target="_blank">/alternatives</a>.</li>
            <li>Optional: System → Cron settings → Sync metrics now (needs network access to GitHub).</li>
            <li>Replace or unpublish samples when you add your real catalog.</li>
            <li>For bulk real data, use <strong>Content → Catalog CSV</strong>.</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
