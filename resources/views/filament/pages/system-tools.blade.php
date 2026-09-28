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
            <x-slot name="description">Refresh stars, forks, issues, and health scores.</x-slot>
            <x-filament::button wire:click="syncAllMetrics" color="success" icon="heroicon-o-arrow-path">
                Sync all metrics
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Link health</x-slot>
            <x-slot name="description">HTTP-check repo and website URLs.</x-slot>
            <x-filament::button wire:click="checkAllLinks" color="warning" icon="heroicon-o-link">
                Check all links
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Demo catalog</x-slot>
            <x-slot name="description">Seed Notion, Slack, Analytics alternatives (AppFlowy, Mattermost, Plausible, etc.). Skips if you already have 5+ alternatives.</x-slot>
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="seedDemoData" color="info" icon="heroicon-o-sparkles">
                    Seed demo data
                </x-filament::button>
                <x-filament::button wire:click="seedDemoDataForce" color="gray" icon="heroicon-o-arrow-path"
                    wire:confirm="Add demo rows even if the catalog is not empty?">
                    Force seed
                </x-filament::button>
            </div>
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
                color="danger"
                icon="heroicon-o-lock-open"
                wire:confirm="Unlock the installer? Anyone who can reach /install can re-run setup."
            >
                Unlock installer
            </x-filament::button>
        </x-filament::section>
    </div>

    <x-filament::section class="mt-6">
        <x-slot name="heading">Import alternatives (CSV)</x-slot>
        <x-slot name="description">Paste CSV rows. Creates proprietary tools as needed. Sample file: samples/alternatives-import.csv</x-slot>
        <form wire:submit="importCsv" class="space-y-4">
            {{ $this->form }}
            <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray">
                Import CSV
            </x-filament::button>
        </form>
    </x-filament::section>

    @if($lastOutput)
        <x-filament::section class="mt-6">
            <x-slot name="heading">Last output</x-slot>
            <pre class="text-xs whitespace-pre-wrap font-mono bg-gray-50 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto">{{ $lastOutput }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
