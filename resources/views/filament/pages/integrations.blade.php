<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-2">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Save integrations
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="testGithub" icon="heroicon-o-signal">
                Test GitHub token
            </x-filament::button>
        </div>
    </form>

    @if($lastOutput)
        <x-filament::section class="mt-6">
            <x-slot name="heading">Last test output</x-slot>
            <pre class="text-xs whitespace-pre-wrap font-mono text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 p-4 rounded-xl overflow-x-auto">{{ $lastOutput }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
