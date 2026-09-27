<div class="min-h-screen bg-slate-50">
    @foreach($schemas as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-6">
            <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-500">
                <li><a href="{{ route('home') }}" class="hover:text-brand-600 transition">Home</a></li>
                <li aria-hidden="true" class="text-slate-300">/</li>
                <li><a href="{{ route('finder') }}" class="hover:text-brand-600 transition">Alternatives</a></li>
                <li aria-hidden="true" class="text-slate-300">/</li>
                <li class="text-slate-800 font-medium truncate max-w-[12rem] sm:max-w-none">{{ $alternative->name }}</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <header class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm mb-8">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-violet-50 pointer-events-none"></div>
            <div class="relative p-6 sm:p-10">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 mb-3">
                            Open-source alternative
                        </p>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-[1.15]">
                            {{ $heading }}
                        </h1>
                        <p class="mt-3 text-lg sm:text-xl text-slate-600 font-medium">
                            {{ $subheading }}
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            @if($alternative->license_type)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 px-3 py-1 text-xs font-semibold">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $alternative->license_type }}
                                </span>
                            @endif
                            @if($alternative->primary_language)
                                <span class="inline-flex items-center rounded-full bg-sky-50 text-sky-700 border border-sky-100 px-3 py-1 text-xs font-semibold">
                                    {{ $alternative->primary_language }}
                                </span>
                            @endif
                            <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1 text-xs font-semibold">
                                Difficulty {{ $alternative->self_host_difficulty }}/5
                            </span>
                            <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-800 border border-amber-100 px-3 py-1 text-xs font-semibold">
                                Health {{ number_format($alternative->overall_health_score, 1) }}/100
                            </span>
                        </div>

                        @if($alternative->description)
                            <p class="mt-6 text-slate-600 leading-relaxed text-base sm:text-[1.05rem]">
                                {{ $alternative->description }}
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                        <a href="{{ $alternative->repo_url }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 transition">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                            </svg>
                            View on GitHub
                        </a>
                        @if($alternative->website_url)
                            <a href="{{ $alternative->website_url }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-brand-600/25 hover:bg-brand-700 transition">
                                Official website
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        {{-- Metrics --}}
        @if($metric)
            <section aria-label="Repository metrics" class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold text-slate-900">{{ number_format($metric->github_stars) }}</div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">Stars</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold text-slate-900">{{ number_format($metric->github_forks) }}</div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">Forks</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold text-slate-900">{{ number_format($metric->open_issues) }}</div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">Open issues</div>
                </div>
                <div class="rounded-2xl border border-brand-100 bg-brand-50/50 p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold text-brand-700">{{ number_format($alternative->overall_health_score, 1) }}</div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-brand-600">Health score</div>
                </div>
            </section>
        @endif

        {{-- Feature comparison --}}
        @if($proprietary && is_array($proprietary->key_features) && count($proprietary->key_features))
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 sm:p-8 mb-8">
                <h2 class="text-xl font-bold text-slate-900 mb-1">Feature comparison</h2>
                <p class="text-sm text-slate-500 mb-6">{{ $propName }} vs {{ $alternative->name }}</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="py-3 pr-4 text-left font-semibold text-slate-500">Feature</th>
                                <th class="py-3 px-4 text-center font-semibold text-slate-500">{{ $propName }}</th>
                                <th class="py-3 pl-4 text-center font-semibold text-brand-600">{{ $alternative->name }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($proprietary->key_features as $feature)
                                <tr>
                                    <td class="py-3 pr-4 text-slate-800">{{ is_string($feature) ? $feature : json_encode($feature) }}</td>
                                    <td class="py-3 px-4 text-center text-emerald-500">✓</td>
                                    <td class="py-3 pl-4 text-center text-emerald-500">✓</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- Docker --}}
        @if($alternative->docker_compose_blueprint)
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 sm:p-8 mb-8"
                x-data="{ copied: false }">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-xl font-bold text-slate-900">Docker Compose blueprint</h2>
                    <button type="button"
                        @click="navigator.clipboard.writeText($refs.code.textContent); copied = true; setTimeout(() => copied = false, 2000)"
                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak class="text-emerald-600">Copied</span>
                    </button>
                </div>
                <pre class="bg-slate-900 text-slate-100 rounded-xl p-4 sm:p-5 overflow-x-auto text-xs sm:text-sm leading-relaxed"><code x-ref="code">{{ $alternative->docker_compose_blueprint }}</code></pre>
            </section>
        @endif

        {{-- Deploy CTAs --}}
        <section class="rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 to-violet-50 p-6 sm:p-8 mb-10">
            <h2 class="text-xl font-bold text-slate-900 mb-2">One-click deploy</h2>
            <p class="text-sm text-slate-600 mb-5">Spin up {{ $alternative->name }} on a managed platform.</p>
            <div class="flex flex-wrap gap-3">
                <a href="https://render.com/deploy?repo={{ urlencode($alternative->repo_url) }}" target="_blank" rel="noopener noreferrer sponsored"
                    class="inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition">
                    Deploy on Render
                </a>
                <a href="https://cloud.digitalocean.com/apps/new?repo={{ urlencode($alternative->repo_url) }}" target="_blank" rel="noopener noreferrer sponsored"
                    class="inline-flex items-center rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sky-700 transition">
                    Deploy on DigitalOcean
                </a>
            </div>
        </section>

        {{-- Styled SEO / guide / FAQ block --}}
        <section class="space-y-6 mb-4" aria-label="Guide and FAQ">
            {{-- Why choose --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">
                        Why choose {{ $alternative->name }} over {{ $propName }}?
                    </h2>
                </div>
                <div class="p-6 sm:p-8">
                    <p class="text-slate-600 leading-relaxed">
                        <strong class="text-slate-900">{{ $alternative->name }}</strong> is a community-driven open-source project
                        that offers a self-hostable alternative to <strong class="text-slate-900">{{ $propName }}</strong>.
                        With a health score of
                        <span class="font-semibold text-brand-700">{{ number_format($alternative->overall_health_score, 1) }}/100</span>,
                        @if($alternative->license_type)
                            an <span class="font-semibold">{{ $alternative->license_type }}</span> license,
                        @endif
                        and full control over your data, it is built for teams that value privacy and ownership.
                    </p>
                    <ul class="mt-5 grid sm:grid-cols-2 gap-3">
                        <li class="flex items-start gap-3 rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">✓</span>
                            <span class="text-sm text-slate-700"><strong>Self-hostable</strong> — run it on your own servers or cloud.</span>
                        </li>
                        <li class="flex items-start gap-3 rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">✓</span>
                            <span class="text-sm text-slate-700"><strong>Open license</strong> — audit, fork, and customize freely.</span>
                        </li>
                        <li class="flex items-start gap-3 rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">✓</span>
                            <span class="text-sm text-slate-700"><strong>No vendor lock-in</strong> — export and migrate your data.</span>
                        </li>
                        <li class="flex items-start gap-3 rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">✓</span>
                            <span class="text-sm text-slate-700"><strong>Active community</strong> — issues and PRs on GitHub.</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Self-hosting guide --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">Self-hosting guide</h2>
                </div>
                <div class="p-6 sm:p-8">
                    <ol class="space-y-4">
                        <li class="flex gap-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-bold">1</span>
                            <div>
                                <p class="font-semibold text-slate-900">Clone or use the Docker blueprint</p>
                                <p class="mt-1 text-sm text-slate-600">Use the Compose file above, or clone the repo from GitHub and follow upstream docs.</p>
                            </div>
                        </li>
                        <li class="flex gap-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-bold">2</span>
                            <div>
                                <p class="font-semibold text-slate-900">Configure environment variables</p>
                                <p class="mt-1 text-sm text-slate-600">Set database, secrets, and app URL. Never commit production secrets to git.</p>
                            </div>
                        </li>
                        <li class="flex gap-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-bold">3</span>
                            <div>
                                <p class="font-semibold text-slate-900">Add reverse proxy &amp; SSL</p>
                                <p class="mt-1 text-sm text-slate-600">Put Nginx or Caddy in front for HTTPS, rate limiting, and static caching.</p>
                            </div>
                        </li>
                        <li class="flex gap-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-bold">4</span>
                            <div>
                                <p class="font-semibold text-slate-900">Backups &amp; updates</p>
                                <p class="mt-1 text-sm text-slate-600">Schedule volume/database backups and watch GitHub releases for security patches.</p>
                            </div>
                        </li>
                    </ol>
                    <p class="mt-6 text-sm text-slate-500">
                        Self-host difficulty for {{ $alternative->name }} is rated
                        <strong class="text-slate-800">{{ $alternative->self_host_difficulty }}/5</strong>.
                    </p>
                </div>
            </div>

            {{-- FAQ --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden" x-data="{ open: 0 }">
                <div class="border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">Frequently asked questions</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <div>
                        <button type="button" @click="open = open === 0 ? -1 : 0"
                            class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left hover:bg-slate-50 transition">
                            <span class="font-semibold text-slate-900">Is {{ $alternative->name }} free?</span>
                            <span class="text-slate-400 text-lg" x-text="open === 0 ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === 0" x-cloak class="px-6 pb-5 text-sm text-slate-600 leading-relaxed">
                            Yes. It is released under the <strong>{{ $alternative->license_type ?? 'open-source' }}</strong> license
                            and can be self-hosted without paying for the software itself.
                        </div>
                    </div>
                    <div>
                        <button type="button" @click="open = open === 1 ? -1 : 1"
                            class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left hover:bg-slate-50 transition">
                            <span class="font-semibold text-slate-900">How difficult is it to self-host?</span>
                            <span class="text-slate-400 text-lg" x-text="open === 1 ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === 1" x-cloak class="px-6 pb-5 text-sm text-slate-600 leading-relaxed">
                            Difficulty is rated <strong>{{ $alternative->self_host_difficulty }}/5</strong>.
                            Basic Docker knowledge is recommended for a stable production setup.
                        </div>
                    </div>
                    <div>
                        <button type="button" @click="open = open === 2 ? -1 : 2"
                            class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left hover:bg-slate-50 transition">
                            <span class="font-semibold text-slate-900">What is {{ $alternative->name }} an alternative to?</span>
                            <span class="text-slate-400 text-lg" x-text="open === 2 ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === 2" x-cloak class="px-6 pb-5 text-sm text-slate-600 leading-relaxed">
                            {{ $alternative->name }} is a self-hostable open-source alternative to
                            <strong>{{ $propName }}</strong>.
                        </div>
                    </div>
                    <div>
                        <button type="button" @click="open = open === 3 ? -1 : 3"
                            class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left hover:bg-slate-50 transition">
                            <span class="font-semibold text-slate-900">Where can I contribute?</span>
                            <span class="text-slate-400 text-lg" x-text="open === 3 ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === 3" x-cloak class="px-6 pb-5 text-sm text-slate-600 leading-relaxed">
                            Visit the
                            <a href="{{ $alternative->repo_url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-brand-600 hover:underline">GitHub repository</a>
                            to open issues or submit pull requests.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
