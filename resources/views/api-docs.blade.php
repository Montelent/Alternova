@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 sm:px-6 py-10 sm:py-14">
    <p class="text-xs font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">Developers</p>
    <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
        Public API documentation
    </h1>
    <p class="mt-3 text-base text-slate-600 dark:text-slate-300 leading-relaxed">
        Use these JSON endpoints to list and fetch open-source alternatives from {{ config('app.name', 'Alternova') }}.
        Base URL is always this site’s domain — nothing is hardcoded to a single host.
    </p>

    @if(! $apiEnabled)
        <div class="mt-6 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-950 dark:text-amber-100">
            The public API is currently <strong>disabled</strong> by the site administrator. Endpoints return HTTP 503 until it is turned back on.
        </div>
    @else
        <div class="mt-6 rounded-xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3 text-sm text-emerald-950 dark:text-emerald-100">
            API status: <strong>enabled</strong>.
            @if($requireKey)
                A valid API key is <strong>required</strong> for every request.
            @else
                API keys are optional for read endpoints (higher rate limits with a key).
            @endif
        </div>
    @endif

    <section class="mt-10 space-y-4">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Authentication</h2>
        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
            Create a key under your account (when allowed), then send it as:
        </p>
        <pre class="rounded-xl bg-slate-900 text-emerald-300 text-xs sm:text-sm p-4 overflow-x-auto">Authorization: Bearer alt_your_key_here

# or
X-Api-Key: alt_your_key_here

# or query (less preferred)
?api_key=alt_your_key_here</pre>
        <p class="text-sm text-slate-600 dark:text-slate-300">
            Rate limits (per minute): anonymous <strong>{{ $rateAnon }}</strong>, with API key <strong>{{ $rateKey }}</strong>.
            Responses include <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 rounded">X-RateLimit-Limit</code> and <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 rounded">X-RateLimit-Remaining</code>.
        </p>
    </section>

    <section class="mt-10 space-y-6">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Endpoints</h2>
        @foreach($endpoints as $ep)
            <article class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/80 p-5 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-md bg-indigo-600 text-white text-xs font-bold px-2 py-1">{{ $ep['method'] }}</span>
                    <code class="text-xs sm:text-sm font-mono text-slate-800 dark:text-slate-200 break-all">{{ $ep['path'] }}</code>
                </div>
                <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">{{ $ep['summary'] }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Auth: {{ $ep['auth'] }}</p>
                @if(!empty($ep['params']))
                    <ul class="mt-3 list-disc list-inside text-xs sm:text-sm text-slate-600 dark:text-slate-400 space-y-1">
                        @foreach($ep['params'] as $param)
                            <li>{{ $param }}</li>
                        @endforeach
                    </ul>
                @endif
            </article>
        @endforeach
    </section>

    <section class="mt-10 space-y-3">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Example</h2>
        <pre class="rounded-xl bg-slate-900 text-slate-100 text-xs sm:text-sm p-4 overflow-x-auto">curl -s "{{ $baseUrl }}/api/alternatives?sort=health&amp;per_page=5" \
  -H "Accept: application/json"</pre>
        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
            Site operators control API availability under <strong>Admin → System → API access</strong>
            (global switch, require-key, rate limits, and per-user API permission).
        </p>
    </section>
</div>
@endsection
