<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-start">
            <x-filament::button type="submit">
                Save ad settings
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">How to go live</x-slot>
        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <li>Get approved in Google AdSense and create ad units (Display / In-article).</li>
            <li>Paste your <strong>ca-pub-…</strong> client ID and each unit’s <strong>slot ID</strong> above.</li>
            <li>Update <code class="text-xs">/ads.txt</code> with the line AdSense gives you.</li>
            <li>Turn on <strong>Enable live ads</strong> and save.</li>
            <li>Leave placeholders off for real visitors.</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
