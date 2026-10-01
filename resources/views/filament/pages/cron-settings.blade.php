<x-filament-panels::page>
    @php
        $env = \App\Support\CronSettings::environmentInfo();
        $variants = \App\Support\CronSettings::commandVariants();
    @endphp

    <x-filament::section class="mb-6">
        <x-slot name="heading">Server cron (any host)</x-slot>
        <x-slot name="description">
            Paths below are detected automatically for <strong>this installation</strong> (domain, folder, and PHP binary). Buyers only paste the recommended line into their host’s cron panel.
        </x-slot>

        <div class="space-y-4 text-sm">
            <div class="grid gap-2 sm:grid-cols-2 rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/40">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Site URL</p>
                    <p class="font-mono text-xs break-all">{{ $env['app_url'] ?: 'Set APP_URL in .env' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Install path</p>
                    <p class="font-mono text-xs break-all">{{ $env['install_path'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">PHP binary (detected)</p>
                    <p class="font-mono text-xs break-all">{{ $env['php_binary'] }} ({{ $env['php_version'] }})</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">App timezone</p>
                    <p class="font-mono text-xs">{{ $env['timezone'] }}</p>
                </div>
            </div>

            <ol class="list-decimal list-inside space-y-1 text-gray-600 dark:text-gray-300">
                <li>Open your host’s control panel (cPanel, hPanel, Plesk, DirectAdmin, etc.)</li>
                <li>Create a <strong>Cron Job</strong></li>
                <li>Frequency: <strong>Every minute</strong> (or every 5 minutes minimum)</li>
                <li>Command: copy the <strong>Recommended</strong> line below</li>
            </ol>

            @foreach($variants as $i => $variant)
                <div class="rounded-lg bg-gray-950 text-gray-100 p-4" x-data="{ copied: false }">
                    <p class="text-xs font-semibold text-gray-400 mb-1">{{ $variant['label'] }}</p>
                    <p class="text-[11px] text-gray-500 mb-2">{{ $variant['note'] }}</p>
                    <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                        <code class="flex-1 font-mono text-xs break-all select-all">{{ $variant['command'] }}</code>
                        <button
                            type="button"
                            class="shrink-0 rounded-md bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20"
                            x-on:click="navigator.clipboard.writeText(@js($variant['command'])); copied = true; setTimeout(() => copied = false, 2000)"
                        >
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" x-cloak>Copied</span>
                        </button>
                    </div>
                </div>
            @endforeach

            <p class="text-gray-500 dark:text-gray-400 text-xs">
                If the recommended PHP path fails, open your host’s “Select PHP version” / “PHP CLI” page and put that full path in place of <code class="text-[10px]">php</code>.
                Nothing in Alternova is hardcoded to a specific domain or hosting brand.
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                <strong>Last schedule heartbeat:</strong>
                @if($this->lastRun)
                    {{ \Illuminate\Support\Carbon::parse($this->lastRun)->diffForHumans() }}
                    <span class="text-gray-400">({{ $this->lastRun }})</span>
                @else
                    <span class="text-warning-600 dark:text-warning-400">Never recorded. Cron is not running yet for this install.</span>
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
