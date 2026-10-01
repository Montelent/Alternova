<!DOCTYPE html>
<html lang="en" class="scroll-smooth" x-data="{ mobileOpen: false }" @keydown.escape.window="mobileOpen = false">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Alternova – Open-source alternatives & brandable domains</title>
    <meta name="description" content="Discover self-hostable open-source alternatives to proprietary tools. Generate brandable domain names with live availability checks.">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta property="og:title" content="Alternova – Open-source alternatives & brandable domains">
    <meta property="og:description" content="Discover self-hostable open-source alternatives to proprietary tools. Generate brandable domain names with live availability checks.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">
    @include('partials.homepage-schema')
    @include('partials.adsense-head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81', 950: '#1e1b4b',
                        }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans bg-slate-950 text-white antialiased">
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/5 bg-slate-950/90 backdrop-blur-xl">
        <div class="mx-auto max-w-6xl px-3 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-2">
            <a href="/" class="flex items-center gap-2 font-bold text-base sm:text-lg tracking-tight text-white min-w-0">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-sm text-white">A</span>
                <span class="truncate">Alternova</span>
            </a>

            <nav class="hidden md:flex items-center gap-5 lg:gap-6 text-sm text-slate-300">
                <a href="{{ route('finder') }}" class="hover:text-white transition">Alternatives</a>
                <a href="{{ route('collections.index') }}" class="hover:text-white transition">Collections</a>
                <a href="{{ route('trending') }}" class="hover:text-white transition">Trending</a>
                <a href="{{ route('domains') }}" class="hover:text-white transition">Domains</a>
                <a href="{{ route('about') }}" class="hover:text-white transition">About</a>
            </nav>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('finder') }}" class="hidden sm:inline-flex rounded-full bg-brand-600 hover:bg-brand-500 px-3.5 py-2 text-sm font-medium text-white transition">
                    Get started
                </a>
                <button type="button"
                    class="md:hidden inline-flex items-center justify-center p-2.5 rounded-lg text-slate-200 hover:bg-white/10"
                    @click="mobileOpen = !mobileOpen"
                    :aria-expanded="mobileOpen.toString()"
                    aria-label="Menu">
                    <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div x-show="mobileOpen" x-cloak
            x-transition
            class="md:hidden border-t border-white/10 bg-slate-950 max-h-[min(75vh,calc(100dvh-3.5rem))] overflow-y-auto">
            <nav class="px-3 py-3 space-y-0.5 text-sm font-medium">
                <a href="{{ route('finder') }}" @click="mobileOpen=false" class="flex rounded-xl px-3 py-3 text-slate-200 hover:bg-white/5">Alternatives</a>
                <a href="{{ route('collections.index') }}" @click="mobileOpen=false" class="flex rounded-xl px-3 py-3 text-slate-200 hover:bg-white/5">Collections</a>
                <a href="{{ route('trending') }}" @click="mobileOpen=false" class="flex rounded-xl px-3 py-3 text-slate-200 hover:bg-white/5">Trending</a>
                <a href="{{ route('domains') }}" @click="mobileOpen=false" class="flex rounded-xl px-3 py-3 text-slate-200 hover:bg-white/5">Domains</a>
                <a href="{{ route('about') }}" @click="mobileOpen=false" class="flex rounded-xl px-3 py-3 text-slate-200 hover:bg-white/5">About</a>
                <a href="{{ route('contact') }}" @click="mobileOpen=false" class="flex rounded-xl px-3 py-3 text-slate-200 hover:bg-white/5">Contact</a>
                <a href="{{ route('finder') }}" @click="mobileOpen=false" class="mt-2 flex items-center justify-center rounded-xl bg-brand-600 px-3 py-3 text-white font-semibold">Get started</a>
            </nav>
        </div>
    </header>

    <section class="relative pt-28 sm:pt-32 pb-16 sm:pb-20 overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-brand-900/40 via-slate-950 to-slate-950"></div>
        <div class="absolute top-20 left-1/2 -translate-x-1/2 w-[min(600px,100vw)] h-[400px] sm:h-[600px] bg-brand-600/20 rounded-full blur-3xl"></div>
        <div class="relative mx-auto max-w-4xl px-4 text-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300 mb-6 sm:mb-8">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Open source · Self-hostable · Brandable
            </div>
            <h1 class="text-3xl xs:text-4xl sm:text-6xl font-extrabold tracking-tight leading-[1.15] text-white">
                Find better tools.<br>
                <span class="text-brand-300">Name them well.</span>
            </h1>
            <p class="mt-5 sm:mt-6 text-base sm:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Discover high-quality open-source alternatives to proprietary software,
                and generate brandable domain ideas with live availability checks.
            </p>
            <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                <a href="{{ route('finder') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition">
                    Browse alternatives
                </a>
                <a href="{{ route('domains') }}" class="inline-flex items-center justify-center rounded-xl border border-white/15 hover:border-white/30 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white transition">
                    Generate domains
                </a>
            </div>
        </div>
    </section>

    @if(isset($trending) && $trending->isNotEmpty())
    <section class="py-12 border-t border-white/5" id="trending">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-amber-400 mb-2">This week</p>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Trending</h2>
                    <p class="mt-1 text-sm text-slate-400">Most upvoted in the last 7 days</p>
                </div>
                <a href="{{ route('trending') }}" class="text-sm font-medium text-brand-300 hover:text-brand-200 shrink-0">Full board →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($trending as $i => $alt)
                    <a href="{{ route('alternatives.show', $alt) }}"
                        class="group flex items-start gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-4 hover:border-amber-400/40 transition">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500/20 text-amber-200 text-xs font-bold">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-white group-hover:text-amber-200 transition truncate">{{ $alt->name }}</h3>
                                <span class="shrink-0 text-xs font-bold text-amber-400">▲ {{ $alt->period_votes ?? 0 }}</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">
                                @if($alt->proprietaryTool) vs {{ $alt->proprietaryTool->name }} · @endif
                                Health {{ number_format($alt->overall_health_score, 0) }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($collections) && $collections->isNotEmpty())
    <section class="py-16 border-t border-white/5" id="collections">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-violet-300 mb-2">Curated lists</p>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Featured collections</h2>
                </div>
                <a href="{{ route('collections.index') }}" class="text-sm font-medium text-brand-300 hover:text-brand-200 shrink-0">All collections →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($collections as $c)
                    <a href="{{ route('collections.show', $c->slug) }}"
                        class="group rounded-2xl border border-white/10 bg-white/[0.04] p-6 hover:border-brand-400/50 transition">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-semibold text-white group-hover:text-brand-300 transition">{{ $c->name }}</h3>
                            <span class="text-xs text-slate-500 shrink-0">{{ $c->items_count }} tools</span>
                        </div>
                        @if($c->description)
                            <p class="mt-2 text-sm text-slate-400 line-clamp-2">{{ $c->description }}</p>
                        @endif
                        <span class="mt-4 inline-block text-sm font-medium text-brand-300">View →</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($featured) && $featured->isNotEmpty())
    <section class="py-16 border-t border-white/5" id="featured">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-brand-400 mb-2">Editor’s picks</p>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Featured alternatives</h2>
                </div>
                <a href="{{ route('finder') }}" class="text-sm font-medium text-brand-300 hover:text-brand-200 shrink-0">View all →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($featured as $alt)
                    @include('partials.home-alt-card', ['alt' => $alt])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($recent) && $recent->isNotEmpty())
    <section class="py-16 border-t border-white/5">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-emerald-400 mb-2">Fresh</p>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Recently added</h2>
                </div>
                <a href="{{ route('finder', ['sort' => 'newest']) }}" class="text-sm font-medium text-brand-300 hover:text-brand-200">Newest →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($recent as $alt)
                    @include('partials.home-alt-card', ['alt' => $alt])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($popular) && $popular->isNotEmpty())
    <section class="py-16 border-t border-white/5">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-amber-400 mb-2">Community</p>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Most voted</h2>
                </div>
                <a href="{{ route('finder', ['sort' => 'votes']) }}" class="text-sm font-medium text-brand-300 hover:text-brand-200">By votes →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($popular as $alt)
                    @include('partials.home-alt-card', ['alt' => $alt])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($tools) && $tools->isNotEmpty())
    <section class="py-16 border-t border-white/5">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-10">
                <p class="text-sm font-semibold uppercase tracking-wider text-violet-300 mb-2">Browse by product</p>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Proprietary tools</h2>
                <p class="mt-2 text-slate-400 text-sm">See every open-source option we list for a given product.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach($tools as $tool)
                    <a href="{{ route('tools.show', $tool) }}"
                        class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:border-brand-400 hover:bg-brand-600/10 transition">
                        {{ $tool->name }}
                        <span class="text-xs text-slate-500">{{ $tool->alternatives_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <div class="mx-auto max-w-6xl px-4">
        <x-ad-slot slot="header" class="my-4" />
    </div>

    <section class="py-16 sm:py-20 border-t border-white/5">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="text-center mb-10 sm:mb-14">
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Two tools. One platform.</h2>
                <p class="mt-3 text-slate-400 text-sm sm:text-base">Everything you need to go open-source and launch with a great name.</p>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <a href="{{ route('finder') }}" class="group relative rounded-2xl border border-white/10 bg-gradient-to-b from-white/[0.07] to-transparent p-6 sm:p-8 hover:border-brand-400 transition">
                    <h3 class="text-lg sm:text-xl font-semibold text-white group-hover:text-brand-300 transition">Open Source Finder</h3>
                    <p class="mt-2 text-slate-400 text-sm leading-relaxed">Search self-hostable alternatives with health scores, licenses, and deploy links.</p>
                    <span class="mt-6 inline-flex text-sm font-medium text-brand-300">Explore →</span>
                </a>
                <a href="{{ route('domains') }}" class="group relative rounded-2xl border border-white/10 bg-gradient-to-b from-white/[0.07] to-transparent p-6 sm:p-8 hover:border-violet-400 transition">
                    <h3 class="text-lg sm:text-xl font-semibold text-white group-hover:text-violet-300 transition">Domain Combinator</h3>
                    <p class="mt-2 text-slate-400 text-sm leading-relaxed">Generate brandable domains, score them, and check availability.</p>
                    <span class="mt-6 inline-flex text-sm font-medium text-violet-300">Generate →</span>
                </a>
            </div>
        </div>
    </section>

    <section class="py-14 border-t border-white/5">
        <div class="mx-auto max-w-6xl px-4 grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 text-center">
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-white">{{ $stats['alternatives'] ?? '—' }}</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Published alternatives</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-white">{{ $stats['tools'] ?? '—' }}</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Proprietary tools</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-white">DNS + RDAP</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Domain checks</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-white">API + RSS</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Open feeds</div>
            </div>
        </div>
    </section>

    <footer class="border-t border-white/5 py-10">
        <div class="mx-auto max-w-6xl px-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-500">
            <span>© {{ date('Y') }} Alternova</span>
            <div class="flex flex-wrap justify-center gap-4 sm:gap-5">
                <a href="{{ route('finder') }}" class="hover:text-slate-300">Alternatives</a>
                <a href="{{ route('trending') }}" class="hover:text-slate-300">Trending</a>
                <a href="{{ route('collections.index') }}" class="hover:text-slate-300">Collections</a>
                <a href="{{ route('domains') }}" class="hover:text-slate-300">Domains</a>
                <a href="{{ route('privacy') }}" class="hover:text-slate-300">Privacy</a>
                <a href="{{ route('about') }}" class="hover:text-slate-300">About</a>
            </div>
        </div>
    </footer>
</body>
</html>
