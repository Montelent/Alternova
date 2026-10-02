<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit" color="primary">
                Save affiliate IDs
            </x-filament::button>
            <x-filament::button tag="a" color="gray"
                href="{{ \App\Filament\Resources\AffiliateClickResource::getUrl('index') }}">
                View click log →
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
