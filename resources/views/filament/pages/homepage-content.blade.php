<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit" color="primary">
                Save homepage content
            </x-filament::button>
            <x-filament::button tag="a" href="{{ url('/') }}" target="_blank" color="gray">
                Preview homepage
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">Tips for beginners</x-slot>
        <ul class="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <li><strong>FAQ:</strong> use <em>Add FAQ item</em> — type a question and answer. Drag the handle to reorder. Delete with the trash icon.</li>
            <li>Rewrite the hero so your niche is clear (e.g. DevOps tools, education software).</li>
            <li>Empty text fields fall back to safe defaults shipped with the script.</li>
            <li>The long-form guide helps AdSense and SEO; turn it off only if you replace it with CMS pages.</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
