<x-filament-panels::page>
    @php
        $env = \App\Support\CronSettings::environmentInfo();
        $variants = \App\Support\CronSettings::commandVariants();
        $recommended = $variants[0]['command'] ?? '';
    @endphp

    {{-- ========== WHAT CRON IS ========== --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">What this page is for (read first)</x-slot>
        <div class="space-y-3 text-sm leading-relaxed text-gray-950 dark:text-gray-100">
            <p>
                Laravel does <strong class="font-semibold text-gray-950 dark:text-white">not</strong> run background jobs by itself on shared hosting.
                Your server must call one command every minute:
            </p>
            <p class="rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-950 px-3 py-2 font-mono text-xs text-gray-900 dark:text-gray-100 break-all">
                schedule:run
            </p>
            <p>
                That single call checks the toggles below (metrics, links, digests, etc.) and only runs the jobs that are due.
                You do <strong class="font-semibold text-gray-950 dark:text-white">not</strong> create a separate cron line for each job.
            </p>
            <ol class="list-decimal list-inside space-y-1.5 text-gray-900 dark:text-gray-100">
                <li>Copy the <strong class="font-semibold">Recommended</strong> command in the next section.</li>
                <li>Add it in your host’s cron panel (every minute).</li>
                <li>Turn on the jobs you want in the form at the bottom of this page and click <strong class="font-semibold">Save</strong>.</li>
                <li>Confirm “Last schedule heartbeat” updates within a few minutes.</li>
            </ol>
        </div>
    </x-filament::section>

    {{-- ========== DETECTED ENVIRONMENT ========== --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">This installation (auto-detected)</x-slot>
        <x-slot name="description">
            These values come from your live server. They change per domain and host — nothing is hardcoded.
        </x-slot>

        <div class="grid gap-3 sm:grid-cols-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-950 p-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Site URL</p>
                <p class="mt-1 font-mono text-xs break-all text-gray-950 dark:text-gray-50">{{ $env['app_url'] ?: 'Set APP_URL in .env' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Install path</p>
                <p class="mt-1 font-mono text-xs break-all text-gray-950 dark:text-gray-50">{{ $env['install_path'] }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">PHP binary (detected)</p>
                <p class="mt-1 font-mono text-xs break-all text-gray-950 dark:text-gray-50">{{ $env['php_binary'] }} ({{ $env['php_version'] }})</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">App timezone</p>
                <p class="mt-1 font-mono text-xs text-gray-950 dark:text-gray-50">{{ $env['timezone'] }}</p>
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Job times below use this timezone (from .env APP_TIMEZONE / config).</p>
            </div>
        </div>

        <p class="mt-4 text-sm text-gray-950 dark:text-gray-100">
            <strong class="font-semibold text-gray-950 dark:text-white">Last schedule heartbeat:</strong>
            @if($this->lastRun)
                <span class="text-emerald-800 dark:text-emerald-300 font-medium">{{ \Illuminate\Support\Carbon::parse($this->lastRun)->diffForHumans() }}</span>
                <span class="text-gray-600 dark:text-gray-400">({{ $this->lastRun }})</span>
            @else
                <span class="text-amber-800 dark:text-amber-300 font-semibold">Never recorded — server cron is not calling schedule:run yet.</span>
            @endif
        </p>
    </x-filament::section>

    {{-- ========== COPY COMMAND ========== --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">1. Copy your cron command</x-slot>
        <x-slot name="description">
            Use <strong>every minute</strong> in the host panel. Laravel decides which jobs actually run.
        </x-slot>

        <div class="space-y-4">
            @foreach($variants as $i => $variant)
                <div
                    class="rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-950 p-4"
                    x-data="{ copied: false }"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <p class="text-sm font-semibold text-gray-950 dark:text-gray-50">
                            {{ $variant['label'] }}
                            @if($i === 0)
                                <span class="ml-2 inline-flex rounded-full bg-emerald-100 dark:bg-emerald-900/60 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-900 dark:text-emerald-200">Start here</span>
                            @endif
                        </p>
                    </div>
                    <p class="text-xs text-gray-700 dark:text-gray-300 mb-3 leading-relaxed">{{ $variant['note'] }}</p>
                    <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                        <code class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-black/40 px-3 py-2.5 font-mono text-xs break-all select-all text-gray-950 dark:text-emerald-300 leading-relaxed">{{ $variant['command'] }}</code>
                        <button
                            type="button"
                            class="shrink-0 rounded-lg bg-primary-600 hover:bg-primary-500 text-white px-4 py-2 text-xs font-semibold transition"
                            x-on:click="navigator.clipboard.writeText(@js($variant['command'])); copied = true; setTimeout(() => copied = false, 2000)"
                        >
                            <span x-show="!copied">Copy command</span>
                            <span x-show="copied" x-cloak>Copied</span>
                        </button>
                    </div>
                </div>
            @endforeach

            <div class="rounded-xl border border-amber-300 dark:border-amber-700/60 bg-amber-50 dark:bg-amber-950/40 p-4 text-sm text-amber-950 dark:text-amber-100 leading-relaxed">
                <p class="font-semibold text-amber-950 dark:text-amber-50 mb-1">If the command fails</p>
                <p>
                    Open your host’s <strong>Select PHP version</strong> or <strong>PHP CLI</strong> page and copy the full path to PHP
                    (example: <code class="text-xs bg-amber-100 dark:bg-black/30 px-1 rounded">/opt/alt/php83/usr/bin/php</code>).
                    Replace the PHP part of the command with that path. Keep the path to <code class="text-xs bg-amber-100 dark:bg-black/30 px-1 rounded">artisan</code> unchanged.
                </p>
            </div>
        </div>
    </x-filament::section>

    {{-- ========== HOST GUIDES ========== --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">2. Add it on your host (step by step)</x-slot>
        <x-slot name="description">Follow the section that matches your hosting panel. Screens differ slightly by brand, but the fields are the same idea.</x-slot>

        <div class="space-y-6 text-sm leading-relaxed text-gray-950 dark:text-gray-100" x-data="{ tab: 'hostinger' }">
            <div class="flex flex-wrap gap-2 border-b border-gray-300 dark:border-gray-600 pb-3">
                @foreach([
                    'hostinger' => 'Hostinger (hPanel)',
                    'cpanel' => 'cPanel',
                    'plesk' => 'Plesk',
                    'ssh' => 'VPS / SSH',
                    'external' => 'No server cron (external)',
                ] as $key => $label)
                    <button
                        type="button"
                        @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}'
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                    >{{ $label }}</button>
                @endforeach
            </div>

            <div x-show="tab === 'hostinger'" class="space-y-3">
                <h3 class="text-base font-bold text-gray-950 dark:text-white">Hostinger (hPanel)</h3>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Log in to <strong class="font-semibold">hPanel</strong> for this domain.</li>
                    <li>Open <strong class="font-semibold">Advanced → Cron Jobs</strong> (sometimes under “Advanced” only).</li>
                    <li>Click <strong class="font-semibold">Create Cron Job</strong> / <strong class="font-semibold">Add Cron Job</strong>.</li>
                    <li>
                        <strong class="font-semibold">Common settings / Schedule:</strong> choose
                        <code class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-950 dark:text-gray-100 px-1 rounded">Every minute</code>
                        (or type <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded text-gray-950 dark:text-gray-100">* * * * *</code>).
                    </li>
                    <li>
                        <strong class="font-semibold">Command:</strong> paste the Recommended command from above.
                        Do not wrap it in extra quotes unless Hostinger’s form already shows quotes for you.
                    </li>
                    <li>Save. Wait 2–3 minutes, then refresh this admin page and check the heartbeat.</li>
                </ol>
                <p class="text-xs text-gray-700 dark:text-gray-300">
                    Hostinger often needs the full PHP path from “PHP Configuration” / “Select PHP version”.
                    If the job status shows errors, switch to that path in the command.
                </p>
            </div>

            <div x-show="tab === 'cpanel'" x-cloak class="space-y-3">
                <h3 class="text-base font-bold text-gray-950 dark:text-white">cPanel</h3>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Log in to cPanel.</li>
                    <li>Search for <strong class="font-semibold">Cron Jobs</strong> and open it.</li>
                    <li>Under <strong class="font-semibold">Add New Cron Job</strong>, set Common Settings to <strong class="font-semibold">Once Per Minute (* * * * *)</strong>.</li>
                    <li>In the Command box, paste the Recommended command.</li>
                    <li>Click <strong class="font-semibold">Add New Cron Job</strong>.</li>
                    <li>Scroll to Current Cron Jobs and confirm the line is listed.</li>
                </ol>
                <p class="text-xs text-gray-700 dark:text-gray-300">
                    Optional: set “Cron Email” to your address temporarily so cPanel emails you when the job fails, then clear it once it works.
                </p>
            </div>

            <div x-show="tab === 'plesk'" x-cloak class="space-y-3">
                <h3 class="text-base font-bold text-gray-950 dark:text-white">Plesk</h3>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Open Plesk → your domain → <strong class="font-semibold">Scheduled Tasks</strong> (or Tools &amp; Settings → Scheduled Tasks).</li>
                    <li>Add Task → Task type: <strong class="font-semibold">Run a PHP script</strong> or <strong class="font-semibold">Run a command</strong>.</li>
                    <li>If “command”: paste the Recommended line. If “PHP script”: point to <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded text-gray-950 dark:text-gray-100">artisan</code> and add arguments <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded text-gray-950 dark:text-gray-100">schedule:run</code> (panel-dependent).</li>
                    <li>Schedule: every minute (or the closest option).</li>
                    <li>Save and verify the heartbeat on this page.</li>
                </ol>
            </div>

            <div x-show="tab === 'ssh'" x-cloak class="space-y-3">
                <h3 class="text-base font-bold text-gray-950 dark:text-white">VPS / dedicated (SSH crontab)</h3>
                <ol class="list-decimal list-inside space-y-2">
                    <li>SSH into the server as the user that owns the website files.</li>
                    <li>Run <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded text-gray-950 dark:text-gray-100">crontab -e</code>.</li>
                    <li>Add this line at the bottom (one line, no line break):</li>
                </ol>
                <pre class="mt-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-950 p-3 font-mono text-xs text-gray-950 dark:text-gray-100 overflow-x-auto whitespace-pre-wrap">* * * * * {{ $recommended }}</pre>
                <ol class="list-decimal list-inside space-y-2 mt-2" start="4">
                    <li>Save and exit the editor.</li>
                    <li>Confirm with <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded text-gray-950 dark:text-gray-100">crontab -l</code>.</li>
                </ol>
            </div>

            <div x-show="tab === 'external'" x-cloak class="space-y-3">
                <h3 class="text-base font-bold text-gray-950 dark:text-white">When the host has no cron (or blocks CLI)</h3>
                <p>
                    Some cheap plans block cron. You can trigger the scheduler over HTTPS with an external ping service
                    (cron-job.org, EasyCron, UptimeRobot “keyword” monitors are not ideal — prefer a real cron HTTP job).
                </p>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Create a secret URL route on your app that runs <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded text-gray-950 dark:text-gray-100">Artisan::call('schedule:run')</code> (or use a package / custom route protected by a long random token).</li>
                    <li>In the external service, schedule a GET/POST every minute to that URL with the token.</li>
                    <li>Prefer real server cron when available — it is more reliable and does not depend on outbound HTTP.</li>
                </ol>
                <p class="text-xs text-gray-700 dark:text-gray-300">
                    This script is designed for standard server cron. External HTTP triggers are a last resort and must be secured with a secret token.
                </p>
            </div>
        </div>
    </x-filament::section>

    {{-- ========== VERIFY ========== --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">3. Verify it works</x-slot>
        <div class="space-y-3 text-sm text-gray-950 dark:text-gray-100 leading-relaxed">
            <ol class="list-decimal list-inside space-y-2">
                <li>Click <strong class="font-semibold">Run schedule:run once</strong> below. You should see output in “Last output” without a fatal error.</li>
                <li>Wait 2–5 minutes after adding the host cron job.</li>
                <li>Refresh this page. <strong class="font-semibold">Last schedule heartbeat</strong> should show a recent time (for example “2 minutes ago”).</li>
                <li>If it stays on “Never recorded”, the host is not executing the command — fix the PHP path or file permissions on <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded text-gray-950 dark:text-gray-100">artisan</code> and <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded text-gray-950 dark:text-gray-100">storage/</code>.</li>
            </ol>
            <div class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-950 p-4">
                <p class="font-semibold text-gray-950 dark:text-white mb-2">Common errors</p>
                <ul class="list-disc list-inside space-y-1.5 text-gray-800 dark:text-gray-200">
                    <li><strong class="font-semibold text-gray-950 dark:text-white">php: command not found</strong> — use the full PHP path from the host panel.</li>
                    <li><strong class="font-semibold text-gray-950 dark:text-white">Could not open input file: artisan</strong> — install path is wrong; re-copy the Recommended command from this page after deploy.</li>
                    <li><strong class="font-semibold text-gray-950 dark:text-white">Permission denied</strong> — ensure the web user can read the app and write to <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">storage</code> and <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">bootstrap/cache</code>.</li>
                    <li><strong class="font-semibold text-gray-950 dark:text-white">Heartbeat never updates</strong> — cron may be set to hourly; change to every minute.</li>
                </ul>
            </div>
        </div>
    </x-filament::section>

    {{-- ========== JOB TABLE ========== --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">Scheduled jobs overview</x-slot>
        <x-slot name="description">These only run after server cron calls schedule:run and the toggle is On.</x-slot>
        <div class="overflow-x-auto rounded-xl border border-gray-300 dark:border-gray-600">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600">
                    <tr>
                        <th class="py-2.5 px-3 font-semibold">Job</th>
                        <th class="py-2.5 px-3 font-semibold">When</th>
                        <th class="py-2.5 px-3 font-semibold">Status</th>
                        <th class="py-2.5 px-3 font-semibold">Artisan command</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-950">
                    @foreach($this->jobs as $job)
                        <tr class="text-gray-950 dark:text-gray-100">
                            <td class="py-2.5 px-3 font-medium">{{ $job['label'] }}</td>
                            <td class="py-2.5 px-3 text-gray-700 dark:text-gray-300">{{ $job['when'] }}</td>
                            <td class="py-2.5 px-3">
                                @if($job['enabled'])
                                    <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-900/50 px-2 py-0.5 text-xs font-semibold text-emerald-900 dark:text-emerald-200">On</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-200 dark:bg-gray-800 px-2 py-0.5 text-xs font-semibold text-gray-800 dark:text-gray-300">Off</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $job['command'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- ========== TOGGLES ========== --}}
    <x-filament::section class="mb-6">
        <x-slot name="heading">4. Turn jobs on and set times</x-slot>
        <x-slot name="description">
            Times use the app timezone shown above. Save after changes. Manual buttons below do not replace server cron.
        </x-slot>

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
    </x-filament::section>

    @if($lastOutput)
        <x-filament::section class="mt-2">
            <x-slot name="heading">Last output</x-slot>
            <pre class="text-xs whitespace-pre-wrap font-mono text-gray-950 dark:text-gray-100 bg-gray-50 dark:bg-gray-950 border border-gray-300 dark:border-gray-600 p-4 rounded-xl overflow-x-auto">{{ $lastOutput }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
