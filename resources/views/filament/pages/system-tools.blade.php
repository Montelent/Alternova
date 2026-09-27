<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">System Tools</x-slot>
            <x-slot name="description">
                Run common maintenance commands from the admin panel. Migrations are the primary tool; use with care on production.
            </x-slot>

            <div class="prose prose-sm dark:prose-invert max-w-none">
                <ul>
                    <li><strong>Run Migrations</strong> — executes <code>php artisan migrate --force</code> (pending only).</li>
                    <li><strong>Migration Status</strong> — shows which migrations have run.</li>
                    <li><strong>Clear Caches</strong> — config, route, view, and application cache.</li>
                    <li><strong>Optimize</strong> — caches config, routes, and views for production.</li>
                </ul>
                <p class="text-sm text-gray-500">
                    Installer lock status:
                    @if(\App\Support\Installer::isInstalled())
                        <span class="text-green-600 font-medium">Locked (installed)</span>
                    @else
                        <span class="text-amber-600 font-medium">Not locked — installer still accessible</span>
                    @endif
                </p>
            </div>
        </x-filament::section>

        @if($lastOutput !== null)
            <x-filament::section>
                <x-slot name="heading">
                    Last command output
                    @if($lastSuccess === true)
                        <span class="text-green-600 text-sm font-normal">(success)</span>
                    @elseif($lastSuccess === false)
                        <span class="text-red-600 text-sm font-normal">(failed)</span>
                    @endif
                </x-slot>

                <pre class="bg-gray-900 text-gray-100 text-xs rounded-xl p-4 overflow-x-auto max-h-96 whitespace-pre-wrap">{{ $lastOutput }}</pre>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
