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
                <p class="text-sm text-amber-800 dark:text-amber-200 font-medium">Run migrations for per-user API toggles.</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">System Tools → Run migrations</p>
            @endif
        </x-filament::section>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-2">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Save API settings
            </x-filament::button>
            <x-filament::button tag="a" href="{{ url('/api-docs') }}" target="_blank" color="gray" icon="heroicon-o-book-open">
                Open API docs
            </x-filament::button>
            <x-filament::button tag="a" href="{{ url('/api/alternatives?per_page=1') }}" target="_blank" color="gray" icon="heroicon-o-beaker">
                Test /api/alternatives
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">How to test activate / deactivate</x-slot>
        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-800 dark:text-gray-200 leading-relaxed">
            <li><strong>Global off:</strong> disable “Enable public API”, save, open <code class="text-xs">/api/alternatives</code> — expect HTTP 503 and code <code class="text-xs">api_disabled</code>.</li>
            <li><strong>Global on:</strong> enable again, save — expect JSON list (or 401 if “Require API key” is on).</li>
            <li><strong>One user:</strong> System → Users → Disable API for that user. Requests with their key return 403 <code class="text-xs">api_user_disabled</code>.</li>
            <li><strong>One key:</strong> Engagement → API keys → Revoke — expect 401 <code class="text-xs">api_key_invalid</code>. Reactivate restores it (if user API is still allowed).</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
