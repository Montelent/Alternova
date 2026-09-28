<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">Database</x-slot>
            <x-slot name="description">Apply pending schema changes (safe to re-run).</x-slot>
            <x-filament::button wire:click="runMigrations" color="primary" icon="heroicon-o-circle-stack">
                Run migrations
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">GitHub metrics</x-slot>
            <x-slot name="description">Refresh stars, forks, issues, and health scores for all alternatives with a GitHub repo.</x-slot>
            <x-filament::button wire:click="syncAllMetrics" color="success" icon="heroicon-o-arrow-path">
                Sync all metrics
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Performance</x-slot>
            <x-slot name="description">Clear config, routes, views, and app cache after deploys.</x-slot>
            <x-filament::button wire:click="clearCaches" color="gray" icon="heroicon-o-trash">
                Clear caches
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Installer</x-slot>
            <x-slot name="description">Allow /install again (does not drop the database).</x-slot>
            <x-filament::button
                wire:click="unlockInstaller"
                color="warning"
                icon="heroicon-o-lock-open"
                wire:confirm="Unlock the installer? Anyone who can reach /install can re-run setup."
            >
                Unlock installer
            </x-filament::button>
        </x-filament::section>
    </div>

    @if($lastOutput)
        <x-filament::section class="mt-6">
            <x-slot name="heading">Last output</x-slot>
            <pre class="text-xs whitespace-pre-wrap font-mono bg-gray-50 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto">{{ $lastOutput }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
