<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">Database</x-slot>
            <x-slot name="description">Apply pending schema changes or repair missing columns (SEO, sponsored slots, affiliate tables).</x-slot>
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="runMigrations" color="primary" icon="heroicon-o-circle-stack">
                    Run migrations
                </x-filament::button>
                <x-filament::button wire:click="repairSeoSchema" color="warning" icon="heroicon-o-wrench">
                    Repair schema
                </x-filament::button>
                <x-filament::button wire:click="expireSponsored" color="gray" icon="heroicon-o-clock">
                    Expire sponsored
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Newsletter digest</x-slot>
            <x-slot name="description">Email active subscribers new/updated alternatives from the last 7 days. Needs SMTP (Mail settings).</x-slot>
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="sendWeeklyDigestDryRun" color="gray" icon="heroicon-o-eye">
                    Dry run
                </x-filament::button>
                <x-filament::button
                    wire:click="sendWeeklyDigest"
                    color="success"
                    icon="heroicon-o-envelope"
                    wire:confirm="Send the weekly digest to all active subscribers now?"
                >
                    Send digest now
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">GitHub metrics</x-slot>
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
            <x-slot name="heading">Export</x-slot>
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
            <x-filament::button
                wire:click="toggleMaintenance"
                color="{{ $maintenanceOn ? 'success' : 'danger' }}"
                icon="heroicon-o-wrench"
                wire:confirm="{{ $maintenanceOn ? 'Turn OFF maintenance?' : 'Turn ON maintenance mode?' }}"
            >
                {{ $maintenanceOn ? 'Disable maintenance' : 'Enable maintenance' }}
            </x-filament::button>
            @if($maintenanceOn)
                <p class="mt-2 text-sm text-warning-600 dark:text-warning-400">Maintenance is currently ON.</p>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Performance</x-slot>
            <x-filament::button wire:click="clearCaches" color="gray" icon="heroicon-o-trash">
                Clear caches
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Installer</x-slot>
            <x-filament::button
                wire:click="unlockInstaller"
                color="danger"
                icon="heroicon-o-lock-open"
                wire:confirm="Unlock the installer?"
            >
                Unlock installer
            </x-filament::button>
        </x-filament::section>
    </div>

    <x-filament::section class="mt-6">
        <x-slot name="heading">Import / restore</x-slot>
        <x-slot name="description">CSV import or paste a full JSON backup. Restore matches tools/alternatives by slug.</x-slot>
        <form class="space-y-4">
            {{ $this->form }}
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="importCsv" icon="heroicon-o-arrow-up-tray">
                    Import CSV
                </x-filament::button>
                <x-filament::button
                    wire:click="restoreJsonBackup"
                    color="warning"
                    icon="heroicon-o-arrow-path"
                    wire:confirm="Restore from the pasted JSON? Matching slugs will be updated."
                >
                    Restore JSON backup
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @if($lastOutput)
        <x-filament::section class="mt-6">
            <x-slot name="heading">Last output</x-slot>
            <pre class="text-xs whitespace-pre-wrap font-mono bg-gray-50 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto">{{ $lastOutput }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
