<x-filament-panels::page>
    @php
        $env = \App\Support\CronSettings::environmentInfo();
        $variants = \App\Support\CronSettings::commandVariants();
    @endphp

    <x-filament::section class="mb-6">
        <x-slot name="heading">Server cron (any host)</x-slot>
        <x-slot name="description">
            Paths below are detected automatically for <strong>this installation</strong> (domain, folder, and PHP binary). Paste the recommended line into your host’s cron panel.
        </x-slot>

        <div class="space-y-4 text-sm text-gray-950 dark:text-gray-100">
            <div class="grid gap-3 sm:grid-cols-2 rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Site URL</p>
                    <p class="mt-1 font-mono text-xs break-all text-gray-900 dark:text-gray-100">{{ $env['app_url'] ?: 'Set APP_URL in .env' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Install path</p>
                    <p class="mt-1 font-mono text-xs break-all text-gray-900 dark:text-gray-100">{{ $env['install_path'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">PHP binary (detected)</p>
                    <p class="mt-1 font-mono text-xs break-all text-gray-900 dark:text-gray-100">{{ $env['php_binary'] }} ({{ $env['php_version'] }})</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">App timezone</p>
                    <p class="mt-1 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $env['timezone'] }}</p>
                </div>
            </div>

            <ol class="list-decimal list-inside space-y-1.5 text-gray-800 dark:text-gray-200">
                <li>Open your host’s control panel (cPanel, hPanel, Plesk, DirectAdmin, etc.)</li>
                <li>Create a <strong class="text-gray-950 dark:text-white">Cron Job</strong></li>
                <li>Frequency: <strong class="text-gray-950 dark:text-white">Every minute</strong> (or every 5 minutes minimum)</li>
                <li>Command: copy the <strong class="text-gray-950 dark:text-white">Recommended</strong> line below</li>
            </ol>

            @foreach($variants as $variant)
                <div class="rounded-xl border border-gray-800 bg-gray-900 p-4 text-gray-100" x-data="{ copied: false }">
                    <p class="text-xs font-semibold text-gray-300 mb-1">{{ $variant['label'] }}</p>
                    <p class="text-[11px] text-gray-400 mb-3">{{ $variant['note'] }}</p>
                    <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                        <code class="flex-1 font-mono text-xs break-all select-all text-emerald-300 leading-relaxed">{{ $variant['command'] }}</code>
                        <button
                            type="button"
                            class="shrink-0 rounded-lg bg-white/15 text-white px-3 py-1.5 text-xs font-semibold hover:bg-white/25 transition"
                            x-on:click="navigator.clipboard.writeText(@js($variant['command'])); copied = true; setTimeout(() => copied = false, 2000)"
                        >
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" x-cloak>Copied</span>
                        </button>
                    </div>
                </div>
            @endforeach

            <p class="text-gray-600 dark:text-gray-400 text-xs leading-relaxed">
                If the recommended PHP path fails, open your host’s “Select PHP version” / “PHP CLI” page and put that full path in place of
                <code class="rounded bg-gray-100 dark:bg-gray-800 px-1 text-gray-900 dark:text-gray-100">php</code>.
                Paths are detected per install — nothing is hardcoded to a specific domain or host brand.
            </p>

            <p class="text-gray-800 dark:text-gray-200">
                <strong class="text-gray-950 dark:text-white">Last schedule heartbeat:</strong>
                @if($this->lastRun)
                    <span class="text-gray-900 dark:text-gray-100">{{ \Illuminate\Support\Carbon::parse($this->lastRun)->diffForHumans() }}</span>
                    <span class="text-gray-500 dark:text-gray-400">({{ $this->lastRun }})</span>
                @else
                    <span class="text-amber-700 dark:text-amber-400 font-medium">Never recorded. Cron is not running yet for this install.</span>
                @endif
            </p>
        </div>
    </x-filament::section>

    <x-filament::section class="mb-6">
        <x-slot name="heading">Scheduled jobs overview</x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-900 dark:text-gray-100">
                <thead class="text-xs uppercase text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="py-2 pr-4 font-semibold">Job</th>
                        <th class="py-2 pr-4 font-semibold">When</th>
                        <th class="py-2 pr-4 font-semibold">Status</th>
                        <th class="py-2 font-semibold">Command</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($this->jobs as $job)
                        <tr class="text-gray-900 dark:text-gray-100">
                            <td class="py-2.5 pr-4 font-medium">{{ $job['label'] }}</td>
                            <td class="py-2.5 pr-4 text-gray-700 dark:text-gray-300">{{ $job['when'] }}</td>
                            <td class="py-2.5 pr-4">
                                @if($job['enabled'])
                                    <span class="inline-flex rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:text-emerald-300">On</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:text-gray-400">Off</span>
                                @endif
                            </td>
                            <td class="py-2.5 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $job['command'] }}</td>
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
            <pre class="text-xs whitespace-pre-wrap font-mono text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 p-4 rounded-xl overflow-x-auto">{{ $lastOutput }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
