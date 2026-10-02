<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit" color="primary">
                Save ad settings
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">How to use (any network)</x-slot>
        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <li>Turn on <strong>Show layout placeholders</strong> first to see where each placement sits on the site.</li>
            <li>From AdSense, Media.net, Ezoic, PropellerAds, or any network, copy the unit’s HTML/JS.</li>
            <li>Paste it into the matching placement’s <strong>Ad code</strong> box (priority over AdSense slot IDs).</li>
            <li>Optional: put site-wide loaders in <strong>Global head scripts</strong>.</li>
            <li>For AdSense-only: set Publisher ID + numeric slot IDs and leave HTML boxes empty.</li>
            <li>Enable <strong>Live ads</strong>, turn placeholders off, and save.</li>
            <li>Update <code class="text-xs">/ads.txt</code> if your network requires it.</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
