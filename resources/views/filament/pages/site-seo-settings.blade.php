<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Save SEO settings
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">How this maps to Rank Math / Yoast</x-slot>
        <div class="prose prose-sm dark:prose-invert max-w-none text-sm text-gray-600 dark:text-gray-300">
            <ul>
                <li><strong>General</strong> — site name, separator, robots, sitewide noindex (staging).</li>
                <li><strong>Title templates</strong> — automatic titles for alternatives, tools, finder, domains, compare.</li>
                <li><strong>Social</strong> — default OG image, Twitter card, Facebook App ID.</li>
                <li><strong>Webmaster</strong> — Google / Bing / Yandex / Pinterest verification meta tags.</li>
                <li><strong>Schema</strong> — Organization name, logo, sameAs profiles for Knowledge Graph.</li>
                <li><strong>Per-page overrides</strong> — still edit <em>Meta title / Meta description</em> on each Alternative or Proprietary tool (wins over templates).</li>
            </ul>
            <p class="mt-3">Tokens you can use in templates: <code>%sitename%</code> <code>%sep%</code> <code>%tagline%</code> <code>%title%</code> <code>%prop%</code> <code>%license%</code> <code>%language%</code> <code>%health%</code> <code>%excerpt%</code> <code>%page%</code></p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
