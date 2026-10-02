<x-filament-panels::page>
    <x-filament::section class="mb-6">
        <x-slot name="heading">Export</x-slot>
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 leading-relaxed">
            Download all alternatives as CSV. Safe to re-import later. Proprietary links use tool <strong>slugs</strong> (comma-separated).
        </p>
        <x-filament::button wire:click="exportCsv" icon="heroicon-o-arrow-down-tray" color="success">
            Download alternatives CSV
        </x-filament::button>
    </x-filament::section>

    <form wire:submit="importCsv" class="space-y-6">
        {{ $this->form }}
        <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray">
            Import CSV
        </x-filament::button>
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
            <li>Export first so you have a backup before a large import.</li>
            <li>Use UTF-8 CSV (Excel: “CSV UTF-8”).</li>
            <li>Unknown <code class="text-xs">proprietary_slugs</code> are ignored; create those tools first if needed.</li>
            <li>Import never deletes rows — it only creates or updates by <code class="text-xs">slug</code>.</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
