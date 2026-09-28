<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alternova – Open-source alternatives & brandable domains</title>
    <meta name="description" content="Discover self-hostable open-source alternatives to proprietary tools. Generate brandable domain names with live availability checks.">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:title" content="Alternova – Open-source alternatives & brandable domains">
    <meta property="og:description" content="Discover self-hostable open-source alternatives to proprietary tools. Generate brandable domain names with live availability checks.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">
    @include('partials.adsense-head')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#eef2ff',100:'#e0e7ff',500:'#6366f1',600:'#4f46e5',700:'#4338ca',900:'#312e81' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="font-sans bg-slate-950 text-white antialiased">
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/5 bg-slate-950/80 backdrop-blur-xl">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-bold text-lg tracking-tight">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-sm">A</span>
                Alternova
            </a>
            <nav class="hidden sm:flex items-center gap-6 text-sm text-slate-300">
                <a href="{{ route('finder') }}" class="hover:text-white transition">Alternatives</a>
                <a href="{{ route('domains') }}" class="hover:text-white transition">Domains</a>
                <a href="{{ route('suggest') }}" class="hover:text-white transition">Suggest</a>
                <a href="{{ route('about') }}" class="hover:text-white transition">About</a>
            </nav>
            <a href="{{ route('finder') }}" class="rounded-full bg-brand-600 hover:bg-brand-500 px-4 py-2 text-sm font-medium transition">
                Get started
            </a>
        </div>
    </header>

    <section class="relative pt-32 pb-20 overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-brand-900/40 via-slate-950 to-slate-950"></div>
        <div class="absolute top-20 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-brand-600/20 rounded-full blur-3xl"></div>
        <div class="relative mx-auto max-w-4xl px-4 text-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300 mb-8">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Open source · Self-hostable · Brandable
            </div>
            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight leading-[1.1]">
                Find better tools.<br>
                <span class="bg-gradient-to-r from-brand-500 to-violet-400 bg-clip-text text-transparent">Name them well.</span>
            </h1>
            <p class="mt-6 text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Discover high-quality open-source alternatives to proprietary software,
                and generate brandable domain ideas with live availability checks.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('finder') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-500 px-6 py-3.5 text-sm font-semibold shadow-lg shadow-brand-600/25 transition">
                    Browse alternatives
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('domains') }}" class="inline-flex items-center justify-center rounded-xl border border-white/15 hover:border-white/30 bg-white/5 px-6 py-3.5 text-sm font-semibold transition">
                    Generate domains
                </a>
            </div>
        </div>
    </section>

    {{-- Featured alternatives --}}
    @if(isset($featured) && $featured->isNotEmpty())
    <section class="py-16 border-t border-white/5" id="featured">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-brand-500 mb-2">Editor’s picks</p>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">Featured alternatives</h2>
                    <p class="mt-2 text-slate-400 text-sm">Self-hostable projects worth a look — mark Featured in admin to pin them here.</p>
                </div>
                <a href="{{ route('finder') }}" class="text-sm font-medium text-brand-500 hover:text-brand-400 shrink-0">View all →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($featured as $alt)
                    <a href="{{ route('alternatives.show', $alt) }}"
                        class="group rounded-2xl border border-white/10 bg-white/[0.03] p-6 hover:border-brand-500/40 hover:bg-white/[0.05] transition">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-white group-hover:text-brand-500 transition">{{ $alt->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    vs {{ $alt->proprietaryTool?->name ?? 'proprietary tools' }}
                                </p>
                            </div>
                            <span class="shrink-0 rounded-full bg-brand-600/20 text-brand-400 text-xs font-semibold px-2.5 py-1">
                                {{ number_format($alt->overall_health_score, 0) }}
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-slate-400 line-clamp-2">{{ $alt->description }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($alt->license_type)
                                <span class="text-xs rounded-full bg-white/5 text-slate-400 px-2 py-0.5">{{ $alt->license_type }}</span>
                            @endif
                            @if($alt->primary_language)
                                <span class="text-xs rounded-full bg-white/5 text-slate-400 px-2 py-0.5">{{ $alt->primary_language }}</span>
                            @endif
                            @if($alt->repoMetric)
                                <span class="text-xs rounded-full bg-white/5 text-slate-400 px-2 py-0.5">★ {{ number_format($alt->repoMetric->github_stars) }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <div class="mx-auto max-w-6xl px-4">
        <x-ad-slot slot="header" class="my-4" />
    </div>

    <section class="py-20 border-t border-white/5">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold tracking-tight">Two tools. One platform.</h2>
                <p class="mt-3 text-slate-400">Everything you need to go open-source and launch with a great name.</p>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <a href="{{ route('finder') }}" class="group relative rounded-2xl border border-white/10 bg-gradient-to-b from-white/[0.07] to-transparent p-8 hover:border-brand-500/40 transition">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600/20 text-brand-500 mb-5">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold group-hover:text-brand-500 transition">Open Source Finder</h3>
                    <p class="mt-2 text-slate-400 text-sm leading-relaxed">
                        Search self-hostable alternatives with live GitHub metrics, health scores,
                        license filters, Docker blueprints, and one-click deploy links.
                    </p>
                    <span class="mt-6 inline-flex items-center text-sm font-medium text-brand-500">Explore →</span>
                </a>
                <a href="{{ route('domains') }}" class="group relative rounded-2xl border border-white/10 bg-gradient-to-b from-white/[0.07] to-transparent p-8 hover:border-violet-500/40 transition">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-600/20 text-violet-400 mb-5">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold group-hover:text-violet-400 transition">Domain Combinator</h3>
                    <p class="mt-2 text-slate-400 text-sm leading-relaxed">
                        Combine keywords into brandable domains, score pronounceability,
                        check availability in real time, and export with registrar links.
                    </p>
                    <span class="mt-6 inline-flex items-center text-sm font-medium text-violet-400">Generate →</span>
                </a>
            </div>
        </div>
    </section>

    <section class="py-14 border-t border-white/5">
        <div class="mx-auto max-w-6xl px-4 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-3xl font-bold text-white">{{ $stats['alternatives'] ?? '—' }}</div>
                <div class="mt-1 text-sm text-slate-500">Published alternatives</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-white">DNS + RDAP</div>
                <div class="mt-1 text-sm text-slate-500">Domain checks</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-white">Docker</div>
                <div class="mt-1 text-sm text-slate-500">Ready blueprints</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-white">SEO</div>
                <div class="mt-1 text-sm text-slate-500">Schema & sitemaps</div>
            </div>
        </div>
    </section>

    <footer class="border-t border-white/5 py-10">
        <div class="mx-auto max-w-6xl px-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-500">
            <span>© {{ date('Y') }} Alternova</span>
            <div class="flex flex-wrap justify-center gap-5">
                <a href="{{ route('finder') }}" class="hover:text-slate-300">Alternatives</a>
                <a href="{{ route('domains') }}" class="hover:text-slate-300">Domains</a>
                <a href="{{ route('suggest') }}" class="hover:text-slate-300">Suggest</a>
                <a href="{{ route('privacy') }}" class="hover:text-slate-300">Privacy</a>
                <a href="{{ route('about') }}" class="hover:text-slate-300">About</a>
            </div>
        </div>
    </footer>
</body>
</html>
