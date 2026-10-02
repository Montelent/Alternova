<x-filament-panels::page>
    <x-filament::section class="mb-6">
        <x-slot name="heading">Export</x-slot>
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 leading-relaxed">
            Download backups before large imports. UTF-8 CSV works in Excel, Google Sheets, and LibreOffice.
        </p>
        <div class="flex flex-wrap gap-2">
            <x-filament::button wire:click="exportCsv" icon="heroicon-o-arrow-down-tray" color="success">
                Download alternatives CSV
            </x-filament::button>
            <x-filament::button wire:click="exportToolsCsv" icon="heroicon-o-arrow-down-tray" color="success">
                Download proprietary tools CSV
            </x-filament::button>
        </div>
    </x-filament::section>

    <form class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-2">
            <x-filament::button type="button" wire:click="importCsv" icon="heroicon-o-arrow-up-tray">
                Import alternatives CSV
            </x-filament::button>
            <x-filament::button type="button" wire:click="importToolsCsv" icon="heroicon-o-arrow-up-tray" color="gray">
                Import tools CSV
            </x-filament::button>
        </div>
    </form>

    @if($importReport)
        <x-filament::section class="mt-6">
            <x-slot name="heading">Import report</x-slot>
            <pre class="text-xs whitespace-pre-wrap font-mono text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 p-4 rounded-xl">{{ $importReport }}</pre>
        </x-filament::section>
    @endif

    <x-filament::section class="mt-8">
        <x-slot name="heading">Beginner tips</x-slot>
        <ul class="list-disc list-inside space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <li>Import <strong>tools first</strong>, then alternatives, so <code class="text-xs">proprietary_slugs</code> can link.</li>
            <li>Matching is by <code class="text-xs">slug</code> only — never deletes existing rows.</li>
            <li>For a quick sample catalog, use <strong>System → Demo content</strong> instead of CSV.</li>
            <li>Empty <code class="text-xs">is_published</code> is treated as draft (0).</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
