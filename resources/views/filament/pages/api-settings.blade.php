<x-filament-panels::page>
    @php($stats = $this->stats)

    <div class="grid gap-4 sm:grid-cols-3 mb-6">
        <x-filament::section>
            <x-slot name="heading">Active keys</x-slot>
            <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $stats['keys_active'] }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-300">of {{ $stats['keys_total'] }} total</p>
        </x-filament::section>
        <x-filament::section>
            <x-slot name="heading">Global API</x-slot>
            <p class="text-2xl font-bold {{ \App\Support\ApiSettings::isGloballyEnabled() ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                {{ \App\Support\ApiSettings::isGloballyEnabled() ? 'On' : 'Off' }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ \App\Support\ApiSettings::requireKey() ? 'Keys required' : 'Keys optional for public reads' }}
            </p>
        </x-filament::section>
        <x-filament::section>
            <x-slot name="heading">Users with API off</x-slot>
            @if($stats['has_user_column'])
                <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $stats['users_api_off'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Edit under System → Users</p>
            @else
                <p class="text-sm text-amber-800 dark:text-amber-200 font-medium">Run migrations to enable per-user API toggles.</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">System Tools → Run migrations</p>
            @endif
        </x-filament::section>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" icon="heroicon-o-check">
            Save API settings
        </x-filament::button>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">How to use</x-slot>
        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-800 dark:text-gray-200 leading-relaxed">
            <li><strong>Turn API off for everyone:</strong> disable “Enable public API” above and save.</li>
            <li><strong>Turn API off for one person:</strong> System → Users → edit user → turn off “Allow API access”. Their keys stop working immediately (keys stay in the list until you revoke them).</li>
            <li><strong>Revoke one key only:</strong> Engagement → API keys → Revoke (or Reactivate later).</li>
            <li><strong>Force every request to use a key:</strong> enable “Require API key for all endpoints”.</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
