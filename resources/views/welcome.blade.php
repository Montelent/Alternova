<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alternova – Open-source alternatives & brandable domains</title>
    <meta name="description" content="Discover self-hostable open-source alternatives to proprietary tools. Generate brandable domain names with live availability checks.">
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="font-sans bg-slate-950 text-white antialiased">
    {{-- Nav --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/5 bg-slate-950/80 backdrop-blur-xl">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-bold text-lg tracking-tight">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-sm">A</span>
                Alternova
            </a>
            <nav class="hidden sm:flex items-center gap-8 text-sm text-slate-300">
                <a href="{{ route('finder') }}" class="hover:text-white transition">Alternatives</a>
                <a href="{{ route('domains') }}" class="hover:text-white transition">Domains</a>
                <a href="{{ url('/admin') }}" class="hover:text-white transition">Admin</a>
            </nav>
            <a href="{{ route('finder') }}" class="rounded-full bg-brand-600 hover:bg-brand-500 px-4 py-2 text-sm font-medium transition">
                Get started
            </a>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative pt-32 pb-24 overflow-hidden">
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
                Alternova helps you discover high-quality open-source alternatives to proprietary software,
                and generate brandable domain ideas with live availability checks.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('finder') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-500 px-6 py-3.5 text-sm font-semibold shadow-lg shadow-brand-600/25 transition">
                    Browse alternatives
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('domains') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-white/15 hover:border-white/30 bg-white/5 px-6 py-3.5 text-sm font-semibold transition">
                    Generate domains
                </a>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="py-24 border-t border-white/5">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="text-center mb-16">
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
                    <span class="mt-6 inline-flex items-center text-sm font-medium text-brand-500">
                        Explore →
                    </span>
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
                    <span class="mt-6 inline-flex items-center text-sm font-medium text-violet-400">
                        Generate →
                    </span>
                </a>
            </div>
        </div>
    </section>

    {{-- Stats strip --}}
    <section class="py-16 border-t border-white/5">
        <div class="mx-auto max-w-6xl px-4 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-3xl font-bold text-white">Live</div>
                <div class="mt-1 text-sm text-slate-500">GitHub metrics sync</div>
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
            <div class="flex gap-6">
                <a href="{{ route('finder') }}" class="hover:text-slate-300">Alternatives</a>
                <a href="{{ route('domains') }}" class="hover:text-slate-300">Domains</a>
                <a href="{{ url('/admin') }}" class="hover:text-slate-300">Admin</a>
            </div>
        </div>
    </footer>
</body>
</html>
