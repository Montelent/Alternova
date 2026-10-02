<section class="relative overflow-hidden border-b border-slate-200 dark:border-slate-800 bg-slate-950 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-brand-900/40 via-slate-950 to-slate-950"></div>
        <div class="relative mx-auto max-w-4xl px-4 pt-16 pb-16 sm:pt-24 sm:pb-20 text-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300 mb-6">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Open source · Self-hostable · Brandable
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                Find better tools.<br>
                <span class="text-brand-300">Name them well.</span>
            </h1>
            <p class="mt-5 text-base sm:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Discover high-quality open-source alternatives to proprietary software,
                and generate brandable domain ideas with live availability checks.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('finder') }}" class="inline-flex justify-center rounded-xl bg-brand-600 hover:bg-brand-500 px-6 py-3.5 text-sm font-semibold text-white transition">Browse alternatives</a>
                <a href="{{ route('browse.type', 'categories') }}" class="inline-flex justify-center rounded-xl border border-white/15 hover:border-white/30 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white transition">Browse by category</a>
                <a href="{{ route('domains') }}" class="inline-flex justify-center rounded-xl border border-white/15 hover:border-white/30 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white transition">Generate domains</a>
            </div>
        </div>
    </section>

    @if(empty($stats['alternatives']) && empty($stats['tools']))
    <section class="py-16">
        <div class="mx-auto max-w-2xl px-4 text-center">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Catalog is empty</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                No published alternatives yet. Add tools in the admin panel, or use
                <strong>System tools → Seed demo data</strong> for an optional starter set of real open-source projects.
            </p>
            <a href="{{ url('/admin') }}" class="mt-6 inline-flex rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-500">Open admin</a>
        </div>
    </section>
    @endif

    <section class="py-12 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Browse the catalog</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Jump in by category, language, or license — built for discovery and SEO.</p>
                </div>
                <a href="{{ route('browse.type', 'categories') }}" class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline shrink-0">All hubs →</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('browse.type', 'categories') }}"
                    class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-5 hover:border-brand-300 dark:hover:border-brand-600 transition">
                    <p class="text-xs font-bold uppercase tracking-wide text-brand-600 dark:text-brand-400">Categories</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-300">By product type</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">CRM, notes, chat, analytics, and more.</p>
                </a>
                <a href="{{ route('browse.type', 'languages') }}"
                    class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-5 hover:border-brand-300 dark:hover:border-brand-600 transition">
                    <p class="text-xs font-bold uppercase tracking-wide text-sky-600 dark:text-sky-400">Languages</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-300">By stack</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">TypeScript, Go, Python, Rust, and more.</p>
                </a>
                <a href="{{ route('browse.type', 'licenses') }}"
                    class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-5 hover:border-brand-300 dark:hover:border-brand-600 transition">
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">Licenses</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-300">By license</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">MIT, Apache, GPL, AGPL, and more.</p>
                </a>
            </div>
        </div>
    </section>

    @if(isset($trending) && $trending->isNotEmpty())
    <section class="py-12 border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4 mb-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Trending</h2>
                <a href="{{ route('trending') }}" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">See all</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($trending as $alt)
                    @include('partials.home-alt-card', ['alt' => $alt])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($popular) && $popular->isNotEmpty())
    <section class="py-12 border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4 mb-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Community favorites</h2>
                <a href="{{ route('leaderboard') }}" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">Leaderboard</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($popular as $alt)
                    @include('partials.home-alt-card', ['alt' => $alt])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($collections) && $collections->isNotEmpty())
    <section class="py-12 border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4 mb-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Collections</h2>
                <a href="{{ route('collections.index') }}" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">All collections</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($collections as $col)
                    <a href="{{ route('collections.show', $col->slug) }}" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 hover:border-brand-300 transition">
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ $col->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500 line-clamp-2">{{ $col->description }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
