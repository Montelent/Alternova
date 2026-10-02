<x-filament-panels::page>
    @php
        $env = \App\Support\CronSettings::environmentInfo();
        $variants = \App\Support\CronSettings::commandVariants();
        $recommended = $variants[0]['command'] ?? '';
    @endphp

    {{-- Force readable contrast inside Filament sections (light + dark) --}}
    <style>
        .cron-page {
            --cron-fg: #0f172a;
            --cron-fg-muted: #334155;
            --cron-bg: #ffffff;
            --cron-bg-soft: #f8fafc;
            --cron-border: #cbd5e1;
            --cron-code-bg: #0f172a;
            --cron-code-fg: #6ee7b7;
            --cron-warn-bg: #fffbeb;
            --cron-warn-fg: #78350f;
            --cron-warn-border: #fcd34d;
            --cron-ok-bg: #ecfdf5;
            --cron-ok-fg: #065f46;
            --cron-tab: #e2e8f0;
            --cron-tab-active-bg: #4f46e5;
            --cron-tab-active-fg: #ffffff;
        }
        .dark .cron-page,
        html.dark .cron-page,
        .fi-body.dark .cron-page {
            --cron-fg: #f8fafc;
            --cron-fg-muted: #cbd5e1;
            --cron-bg: #0f172a;
            --cron-bg-soft: #1e293b;
            --cron-border: #475569;
            --cron-code-bg: #020617;
            --cron-code-fg: #6ee7b7;
            --cron-warn-bg: #422006;
            --cron-warn-fg: #fde68a;
            --cron-warn-border: #a16207;
            --cron-ok-bg: #064e3b;
            --cron-ok-fg: #a7f3d0;
            --cron-tab: #334155;
            --cron-tab-active-bg: #6366f1;
            --cron-tab-active-fg: #ffffff;
        }
        .cron-page .cron-card {
            background: var(--cron-bg-soft);
            border: 1px solid var(--cron-border);
            border-radius: 0.75rem;
            padding: 1rem;
            color: var(--cron-fg);
        }
        .cron-page .cron-title {
            color: var(--cron-fg);
            font-weight: 700;
            font-size: 0.95rem;
        }
        .cron-page .cron-text {
            color: var(--cron-fg);
            font-size: 0.875rem;
            line-height: 1.6;
        }
        .cron-page .cron-muted {
            color: var(--cron-fg-muted);
            font-size: 0.8125rem;
            line-height: 1.55;
        }
        .cron-page .cron-label {
            color: var(--cron-fg-muted);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .cron-page .cron-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            color: var(--cron-fg);
            word-break: break-all;
        }
        .cron-page .cron-code-box {
            background: var(--cron-code-bg);
            color: var(--cron-code-fg);
            border: 1px solid var(--cron-border);
            border-radius: 0.5rem;
            padding: 0.65rem 0.75rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            line-height: 1.55;
            word-break: break-all;
            flex: 1;
        }
        .cron-page .cron-warn {
            background: var(--cron-warn-bg);
            color: var(--cron-warn-fg);
            border: 1px solid var(--cron-warn-border);
            border-radius: 0.75rem;
            padding: 1rem;
            font-size: 0.875rem;
            line-height: 1.6;
        }
        .cron-page .cron-ok {
            background: var(--cron-ok-bg);
            color: var(--cron-ok-fg);
            font-weight: 600;
        }
        .cron-page .cron-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            color: var(--cron-fg);
        }
        .cron-page .cron-table th {
            text-align: left;
            padding: 0.65rem 0.75rem;
            background: var(--cron-bg-soft);
            color: var(--cron-fg-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid var(--cron-border);
        }
        .cron-page .cron-table td {
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid var(--cron-border);
            color: var(--cron-fg);
            vertical-align: top;
        }
        .cron-page .cron-table tbody tr:last-child td {
            border-bottom: none;
        }
        .cron-page .cron-badge-on {
            display: inline-flex;
            border-radius: 9999px;
            padding: 0.15rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            background: var(--cron-ok-bg);
            color: var(--cron-ok-fg);
        }
        .cron-page .cron-badge-off {
            display: inline-flex;
            border-radius: 9999px;
            padding: 0.15rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            background: var(--cron-tab);
            color: var(--cron-fg);
        }
        .cron-page .cron-tab-btn {
            border-radius: 0.5rem;
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid var(--cron-border);
            background: var(--cron-tab);
            color: var(--cron-fg);
            cursor: pointer;
        }
        .cron-page .cron-tab-btn.is-active {
            background: var(--cron-tab-active-bg);
            color: var(--cron-tab-active-fg);
            border-color: var(--cron-tab-active-bg);
        }
        .cron-page .cron-copy-btn {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            background: #4f46e5;
            color: #fff;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }
        .cron-page .cron-copy-btn:hover {
            background: #4338ca;
        }
        .cron-page ol, .cron-page ul {
            color: var(--cron-fg);
        }
        .cron-page strong {
            color: var(--cron-fg);
            font-weight: 700;
        }
        .cron-page code.cron-inline {
            background: var(--cron-bg-soft);
            border: 1px solid var(--cron-border);
            border-radius: 0.25rem;
            padding: 0.05rem 0.3rem;
            font-size: 0.75rem;
            color: var(--cron-fg);
        }
        /* Filament form field labels inside this page stay readable */
        .cron-page .fi-fo-field-wrp-label span,
        .cron-page .fi-section-header-heading,
        .cron-page .fi-section-header-description {
            color: var(--cron-fg) !important;
        }
        .cron-page .fi-section-header-description {
            color: var(--cron-fg-muted) !important;
            opacity: 1 !important;
        }
    </style>

    <div class="cron-page space-y-6">

        {{-- 1. WHAT --}}
        <x-filament::section>
            <x-slot name="heading">What this page is for (read first)</x-slot>
            <div class="cron-card space-y-3">
                <p class="cron-text">
                    Laravel does <strong>not</strong> run background jobs by itself on shared hosting.
                    Your server must call one command every minute: <code class="cron-inline">schedule:run</code>.
                </p>
                <p class="cron-text">
                    That single call checks the toggles below (metrics, links, digests, etc.) and only runs jobs that are due.
                    You do <strong>not</strong> create a separate cron line for each job.
                </p>
                <ol class="list-decimal list-inside space-y-1.5 cron-text">
                    <li>Copy the <strong>Recommended</strong> command below.</li>
                    <li>Add it in your host’s cron panel on an <strong>every minute</strong> schedule.</li>
                    <li>Turn on the jobs you want in the form at the bottom, then click <strong>Save</strong>.</li>
                    <li>Confirm the heartbeat updates within a few minutes.</li>
                </ol>
            </div>
        </x-filament::section>

        {{-- 2. DETECTED --}}
        <x-filament::section>
            <x-slot name="heading">This installation (auto-detected)</x-slot>
            <x-slot name="description">Values from this live server. They change per domain and host.</x-slot>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="cron-card">
                    <p class="cron-label">Site URL</p>
                    <p class="cron-mono mt-1">{{ $env['app_url'] ?: 'Set APP_URL in .env' }}</p>
                </div>
                <div class="cron-card">
                    <p class="cron-label">Install path</p>
                    <p class="cron-mono mt-1">{{ $env['install_path'] }}</p>
                </div>
                <div class="cron-card">
                    <p class="cron-label">PHP binary (detected)</p>
                    <p class="cron-mono mt-1">{{ $env['php_binary'] }} ({{ $env['php_version'] }})</p>
                </div>
                <div class="cron-card">
                    <p class="cron-label">App timezone</p>
                    <p class="cron-mono mt-1">{{ $env['timezone'] }}</p>
                    <p class="cron-muted mt-1">Job times in the form use this timezone.</p>
                </div>
            </div>
            <div class="cron-card mt-4">
                <p class="cron-text">
                    <strong>Last schedule heartbeat:</strong>
                    @if($this->lastRun)
                        <span class="cron-ok" style="display:inline;padding:0.1rem 0.4rem;border-radius:0.25rem;">
                            {{ \Illuminate\Support\Carbon::parse($this->lastRun)->diffForHumans() }}
                        </span>
                        <span class="cron-muted">({{ $this->lastRun }})</span>
                    @else
                        <span style="color:var(--cron-warn-fg);font-weight:700;">Never recorded — server cron is not calling schedule:run yet.</span>
                    @endif
                </p>
            </div>
        </x-filament::section>

        {{-- 3. COPY COMMAND --}}
        <x-filament::section>
            <x-slot name="heading">1. Copy your cron command</x-slot>
            <x-slot name="description">Schedule this on the host for every minute. Laravel decides which jobs actually run.</x-slot>

            <div class="space-y-4">
                @foreach($variants as $i => $variant)
                    <div class="cron-card" x-data="{ copied: false }">
                        <p class="cron-title">
                            {{ $variant['label'] }}
                            @if($i === 0)
                                <span class="cron-badge-on" style="margin-left:0.5rem;vertical-align:middle;">Start here</span>
                            @endif
                        </p>
                        <p class="cron-muted mt-1 mb-3">{{ $variant['note'] }}</p>
                        <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                            <code class="cron-code-box">{{ $variant['command'] }}</code>
                            <button
                                type="button"
                                class="cron-copy-btn"
                                x-on:click="navigator.clipboard.writeText(@js($variant['command'])); copied = true; setTimeout(() => copied = false, 2000)"
                            >
                                <span x-show="!copied">Copy command</span>
                                <span x-show="copied" x-cloak>Copied</span>
                            </button>
                        </div>
                    </div>
                @endforeach

                <div class="cron-warn">
                    <p style="font-weight:700;margin-bottom:0.35rem;">If the command fails</p>
                    <p>
                        Open your host’s <strong>Select PHP version</strong> or <strong>PHP CLI</strong> page and copy the full path to PHP
                        (example: <code class="cron-inline">/opt/alt/php83/usr/bin/php</code>).
                        Replace only the PHP part of the command with that path. Keep the path to <code class="cron-inline">artisan</code> the same.
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- 4. HOST GUIDES --}}
        <x-filament::section>
            <x-slot name="heading">2. Add it on your host (step by step)</x-slot>
            <x-slot name="description">Pick the tab that matches your control panel.</x-slot>

            <div class="cron-card" x-data="{ tab: 'hostinger' }">
                <div class="flex flex-wrap gap-2 mb-4" style="border-bottom:1px solid var(--cron-border);padding-bottom:0.75rem;">
                    @foreach([
                        'hostinger' => 'Hostinger (hPanel)',
                        'cpanel' => 'cPanel',
                        'plesk' => 'Plesk',
                        'ssh' => 'VPS / SSH',
                        'external' => 'No server cron',
                    ] as $key => $label)
                        <button
                            type="button"
                            class="cron-tab-btn"
                            @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'is-active' : ''"
                        >{{ $label }}</button>
                    @endforeach
                </div>

                <div x-show="tab === 'hostinger'" class="space-y-2 cron-text">
                    <p class="cron-title">Hostinger (hPanel)</p>
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Log in to <strong>hPanel</strong> for this domain.</li>
                        <li>Open <strong>Advanced → Cron Jobs</strong>.</li>
                        <li>Click <strong>Create Cron Job</strong>.</li>
                        <li>Schedule: choose <strong>Every minute</strong> (or enter <code class="cron-inline">* * * * *</code>).</li>
                        <li>Command: paste the <strong>Recommended</strong> command from above.</li>
                        <li>Save. Wait 2–3 minutes, refresh this page, check the heartbeat.</li>
                    </ol>
                    <p class="cron-muted">If the job errors, use the full PHP path from PHP Configuration / Select PHP version.</p>
                </div>

                <div x-show="tab === 'cpanel'" x-cloak class="space-y-2 cron-text">
                    <p class="cron-title">cPanel</p>
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Log in to cPanel → search <strong>Cron Jobs</strong>.</li>
                        <li>Under Add New Cron Job, set Common Settings to <strong>Once Per Minute (* * * * *)</strong>.</li>
                        <li>Paste the Recommended command into the Command box.</li>
                        <li>Click <strong>Add New Cron Job</strong> and confirm it appears under Current Cron Jobs.</li>
                    </ol>
                    <p class="cron-muted">Optional: set Cron Email temporarily so cPanel mails you on failure; clear it when stable.</p>
                </div>

                <div x-show="tab === 'plesk'" x-cloak class="space-y-2 cron-text">
                    <p class="cron-title">Plesk</p>
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Open the domain → <strong>Scheduled Tasks</strong>.</li>
                        <li>Add Task → run a command (or PHP script, depending on version).</li>
                        <li>Paste the Recommended command, or point PHP at <code class="cron-inline">artisan</code> with arguments <code class="cron-inline">schedule:run</code>.</li>
                        <li>Schedule every minute. Save and verify the heartbeat.</li>
                    </ol>
                </div>

                <div x-show="tab === 'ssh'" x-cloak class="space-y-2 cron-text">
                    <p class="cron-title">VPS / dedicated (SSH)</p>
                    <ol class="list-decimal list-inside space-y-2">
                        <li>SSH in as the user that owns the site files.</li>
                        <li>Run <code class="cron-inline">crontab -e</code>.</li>
                        <li>Add this single line at the bottom:</li>
                    </ol>
                    <pre class="cron-code-box mt-2" style="display:block;">* * * * * {{ $recommended }}</pre>
                    <ol class="list-decimal list-inside space-y-2 mt-2" start="4">
                        <li>Save and exit.</li>
                        <li>Confirm with <code class="cron-inline">crontab -l</code>.</li>
                    </ol>
                </div>

                <div x-show="tab === 'external'" x-cloak class="space-y-2 cron-text">
                    <p class="cron-title">When the host blocks cron</p>
                    <p>Use an external HTTP cron service only as a last resort. Prefer real server cron when available.</p>
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Expose a secret URL that runs <code class="cron-inline">schedule:run</code> (protected by a long random token).</li>
                        <li>In the external service, call that URL every minute.</li>
                        <li>Never publish the token. Prefer CLI cron when the host allows it.</li>
                    </ol>
                </div>
            </div>
        </x-filament::section>

        {{-- 5. VERIFY --}}
        <x-filament::section>
            <x-slot name="heading">3. Verify it works</x-slot>
            <div class="cron-card space-y-3">
                <ol class="list-decimal list-inside space-y-2 cron-text">
                    <li>Click <strong>Run schedule:run once</strong> below. “Last output” should show no fatal error.</li>
                    <li>Wait 2–5 minutes after adding the host cron job.</li>
                    <li>Refresh this page. Heartbeat should show a recent time (for example “2 minutes ago”).</li>
                    <li>If it stays on “Never recorded”, the host is not running the command — fix the PHP path or permissions on <code class="cron-inline">artisan</code> and <code class="cron-inline">storage/</code>.</li>
                </ol>
                <div class="cron-card" style="margin-top:0.5rem;">
                    <p class="cron-title mb-2">Common errors</p>
                    <ul class="list-disc list-inside space-y-1.5 cron-text">
                        <li><strong>php: command not found</strong> — use the full PHP path from the host panel.</li>
                        <li><strong>Could not open input file: artisan</strong> — wrong install path; re-copy Recommended from this page.</li>
                        <li><strong>Permission denied</strong> — web user needs read on the app and write on <code class="cron-inline">storage</code> and <code class="cron-inline">bootstrap/cache</code>.</li>
                        <li><strong>Heartbeat never updates</strong> — cron may be hourly; set it to every minute.</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        {{-- 6. JOB TABLE --}}
        <x-filament::section>
            <x-slot name="heading">Scheduled jobs overview</x-slot>
            <x-slot name="description">These run only after server cron calls schedule:run and the toggle is On.</x-slot>
            <div class="cron-card" style="padding:0;overflow:auto;">
                <table class="cron-table">
                    <thead>
                        <tr>
                            <th>Job</th>
                            <th>When</th>
                            <th>Status</th>
                            <th>Artisan command</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->jobs as $job)
                            <tr>
                                <td style="font-weight:600;">{{ $job['label'] }}</td>
                                <td>{{ $job['when'] }}</td>
                                <td>
                                    @if($job['enabled'])
                                        <span class="cron-badge-on">On</span>
                                    @else
                                        <span class="cron-badge-off">Off</span>
                                    @endif
                                </td>
                                <td class="cron-mono">{{ $job['command'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- 7. FORM --}}
        <x-filament::section>
            <x-slot name="heading">4. Turn jobs on and set times</x-slot>
            <x-slot name="description">Times use the app timezone above. Manual buttons do not replace server cron.</x-slot>

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
            <x-filament::section>
                <x-slot name="heading">Last output</x-slot>
                <pre class="cron-code-box" style="display:block;white-space:pre-wrap;">{{ $lastOutput }}</pre>
            </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>
