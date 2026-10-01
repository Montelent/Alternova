<x-filament-panels::page>
    <x-filament::section class="mb-6">
        <x-slot name="heading">Hostinger cron (required once)</x-slot>
        <x-slot name="description">
            Laravel does not start jobs by itself. Hostinger must call <code class="text-xs">php artisan schedule:run</code> often (every minute is best). Alternova then decides which jobs are due from the settings below.
        </x-slot>

        <div class="space-y-3 text-sm">
            <ol class="list-decimal list-inside space-y-1 text-gray-600 dark:text-gray-300">
                <li>Hostinger hPanel → <strong>Advanced</strong> → <strong>Cron Jobs</strong></li>
                <li>Common setting: <strong>Every minute</strong> (or every 5 / 15 minutes if that is all they allow)</li>
                <li>Command: paste the line below</li>
            </ol>

            <div class="rounded-lg bg-gray-950 text-gray-100 p-4 font-mono text-xs break-all select-all" x-data="{ copied: false }">
                <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                    <code class="flex-1">{{ $this->cronCommand }}</code>
                    <button
                        type="button"
                        class="shrink-0 rounded-md bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20"
                        x-on:click="navigator.clipboard.writeText(@js($this->cronCommand)); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak>Copied</span>
                    </button>
                </div>
            </div>

            <p class="text-gray-500 dark:text-gray-400">
                If PHP is not on the PATH, use the full path Hostinger shows for PHP, for example:
                <code class="text-xs">/usr/bin/php /home/…/public_html/artisan schedule:run</code>
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                <strong>Last schedule heartbeat:</strong>
                @if($this->lastRun)
                    {{ \Illuminate\Support\Carbon::parse($this->lastRun)->diffForHumans() }}
                    <span class="text-gray-400">({{ $this->lastRun }})</span>
                @else
                    <span class="text-warning-600 dark:text-warning-400">Never recorded. Cron may not be set up yet. Click “Run schedule:run once” after saving, or wait for Hostinger.</span>
                @endif
            </p>
        </div>
    </x-filament::section>

    <x-filament::section class="mb-6">
        <x-slot name="heading">Scheduled jobs overview</x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-gray-500 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="py-2 pr-4">Job</th>
                        <th class="py-2 pr-4">When</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2">Command</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($this->jobs as $job)
                        <tr>
                            <td class="py-2.5 pr-4 font-medium">{{ $job['label'] }}</td>
                            <td class="py-2.5 pr-4 text-gray-600 dark:text-gray-400">{{ $job['when'] }}</td>
                            <td class="py-2.5 pr-4">
                                @if($job['enabled'])
                                    <span class="text-success-600 dark:text-success-400 font-semibold">On</span>
                                @else
                                    <span class="text-gray-400">Off</span>
                                @endif
                            </td>
                            <td class="py-2.5 font-mono text-xs text-gray-500">{{ $job['command'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-2">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Save cron settings
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="runScheduleOnce" icon="heroicon-o-play">
                Run schedule:run once
            </x-filament::button>
            <x-filament::button type="button" color="success" wire:click="runMetricsNow" icon="heroicon-o-arrow-path">
                Sync metrics now
            </x-filament::button>
            <x-filament::button type="button" color="warning" wire:click="runLinksNow" icon="heroicon-o-link">
                Check links now
            </x-filament::button>
        </div>
    </form>

    @if($lastOutput)
        <x-filament::section class="mt-6">
            <x-slot name="heading">Last output</x-slot>
            <pre class="text-xs whitespace-pre-wrap font-mono bg-gray-50 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto">{{ $lastOutput }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
