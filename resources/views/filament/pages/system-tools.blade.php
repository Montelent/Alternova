<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">Database</x-slot>
            <x-slot name="description">Apply pending schema changes. If SEO save fails with “Unknown column focus_keyword”, use Repair SEO schema.</x-slot>
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="runMigrations" color="primary" icon="heroicon-o-circle-stack">
                    Run migrations
                </x-filament::button>
                <x-filament::button wire:click="repairSeoSchema" color="warning" icon="heroicon-o-wrench">
                    Repair SEO schema
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">GitHub metrics</x-slot>
            <x-slot name="description">Full sync or limited batch (same as cron).</x-slot>
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="syncAllMetrics" color="success" icon="heroicon-o-arrow-path">
                    Sync all metrics
                </x-filament::button>
                <x-filament::button wire:click="runScheduledSync" color="gray" icon="heroicon-o-clock">
                    Sync batch (25)
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Link health</x-slot>
            <x-slot name="description">HTTP-check repo and website URLs.</x-slot>
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="checkAllLinks" color="warning" icon="heroicon-o-link">
                    Check all links
                </x-filament::button>
                <x-filament::button wire:click="runScheduledLinkCheck" color="gray" icon="heroicon-o-clock">
                    Check batch (40)
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Demo catalog</x-slot>
            <x-slot name="description">Seed starter tools (Notion, Slack, Analytics, etc.).</x-slot>
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
            <x-slot name="heading">Export / backup</x-slot>
            <x-slot name="description">CSV catalog or full JSON backup (tools, alternatives, settings, redirects).</x-slot>
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="exportCatalog" color="gray" icon="heroicon-o-arrow-down-tray">
                    Download CSV
                </x-filament::button>
                <x-filament::button wire:click="exportJsonBackup" color="primary" icon="heroicon-o-archive-box-arrow-down">
                    Download JSON backup
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Maintenance mode</x-slot>
            <x-slot name="description">Public site shows a 503 page. Admin panel stays available.</x-slot>
            <x-filament::button
                wire:click="toggleMaintenance"
                color="{{ $maintenanceOn ? 'success' : 'danger' }}"
                icon="heroicon-o-wrench"
                wire:confirm="{{ $maintenanceOn ? 'Turn OFF maintenance and restore the public site?' : 'Turn ON maintenance mode for visitors?' }}"
            >
                {{ $maintenanceOn ? 'Disable maintenance' : 'Enable maintenance' }}
            </x-filament::button>
            @if($maintenanceOn)
                <p class="mt-2 text-sm text-warning-600 dark:text-warning-400">Maintenance is currently ON.</p>
            @endif
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
        <x-slot name="description">Paste CSV rows. Creates proprietary tools as needed.</x-slot>
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
