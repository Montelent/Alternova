<x-filament-panels::page>
    @php
        $previewName = \App\Support\WhiteLabelSettings::siteName();
        $previewLogo = \App\Support\WhiteLabelSettings::logoUrl();
        $previewColor = \App\Support\WhiteLabelSettings::primary();
        $previewLetter = \App\Support\WhiteLabelSettings::letter();
    @endphp

    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 p-4 sm:p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Live preview (saved values)</p>
        <div class="flex items-center gap-3">
            @if($previewLogo)
                <img src="{{ $previewLogo }}" alt="" class="h-10 w-10 rounded-lg object-contain bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
            @else
                <span class="flex h-10 w-10 items-center justify-center rounded-lg text-white text-sm font-bold" style="background: {{ $previewColor }}">{{ $previewLetter }}</span>
            @endif
            <div>
                <p class="font-bold text-gray-950 dark:text-white">{{ $previewName }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ \App\Support\WhiteLabelSettings::tagline() }}</p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-2">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Save branding
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="importLogoFromUrl" icon="heroicon-o-arrow-down-tray">
                Import logo URL to server
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="importFaviconFromUrl" icon="heroicon-o-arrow-down-tray">
                Import favicon URL to server
            </x-filament::button>
            <x-filament::button tag="a" href="{{ url('/') }}" target="_blank" color="gray">
                Preview site
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">Beginner guide</x-slot>
        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
            <li>Change <strong>Site name</strong> to your product name (what customers should see).</li>
            <li><strong>Logo:</strong> click the upload box and pick a file from your computer, <em>or</em> paste an image URL in “Logo URL”.</li>
            <li>Optional: click <strong>Import logo URL to server</strong> so the image is stored under <code class="text-xs">/uploads/branding/</code> (survives if the remote host deletes it).</li>
            <li>Set <strong>Primary color</strong> as a hex code starting with # (use a color picker online if needed).</li>
            <li>Click <strong>Save branding</strong>, then hard-refresh the public site and admin panel.</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
