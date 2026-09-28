<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="themeApp()" x-init="init()" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Alternova') }}</title>
    <meta name="description" content="{{ $description ?? 'Discover open-source alternatives and generate brandable domain names with Alternova.' }}">
    <meta name="robots" content="{{ $robots ?? 'index,follow,max-image-preview:large,max-snippet:-1' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <link rel="alternate" type="application/rss+xml" title="Alternova Alternatives" href="{{ url('/feed') }}">
    <link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
    <meta name="theme-color" content="#4f46e5">

    <meta property="og:site_name" content="{{ config('app.name', 'Alternova') }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $title ?? config('app.name', 'Alternova') }}">
    <meta property="og:description" content="{{ $description ?? 'Discover open-source alternatives and generate brandable domain names with Alternova.' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? config('app.name', 'Alternova') }}">
    <meta name="twitter:description" content="{{ $description ?? 'Discover open-source alternatives and generate brandable domain names with Alternova.' }}">

    @include('partials.adsense-head')

    <script>
        try {
            if (localStorage.getItem('alternova-theme') === 'dark' ||
                (!localStorage.getItem('alternova-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#eef2ff',100:'#e0e7ff',500:'#6366f1',600:'#4f46e5',700:'#4338ca' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @livewireStyles
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 min-h-screen flex flex-col">
    <header class="sticky top-0 z-40 border-b border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-slate-900/90 backdrop-blur-lg">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-14 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-white text-xs">A</span>
                    Alternova
                </a>
                <nav class="flex items-center gap-1 sm:gap-2 text-sm font-medium">
                    <a href="{{ route('finder') }}"
                        class="px-3 py-1.5 rounded-lg {{ request()->routeIs('finder','alternatives.show','tools.show') ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/20 dark:text-brand-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        Alternatives
                    </a>
                    <a href="{{ route('whats-new') }}"
                        class="hidden sm:inline px-3 py-1.5 rounded-lg {{ request()->routeIs('whats-new') ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/20 dark:text-brand-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        What's new
                    </a>
                    <a href="{{ route('alternatives.compare') }}"
                        class="hidden md:inline px-3 py-1.5 rounded-lg {{ request()->routeIs('alternatives.compare') ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/20 dark:text-brand-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        Compare
                    </a>
                    <a href="{{ route('domains') }}"
                        class="px-3 py-1.5 rounded-lg {{ request()->routeIs('domains') ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/20 dark:text-brand-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        Domains
                    </a>
                    <button type="button" @click="toggle()" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="Toggle theme" aria-label="Toggle dark mode">
                        <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
                    <a href="{{ url('/admin') }}" class="px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs sm:text-sm">
                        Admin
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <div class="mx-auto max-w-7xl px-4 w-full">
        <x-ad-slot slot="footer" class="my-4" />
    </div>

    <footer class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 py-10 mt-auto">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-8">
                <div>
                    <div class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-white text-xs">A</span>
                        Alternova
                    </div>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-xs">Open-source alternatives and brandable domain ideas.</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-8 text-sm">
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white mb-3">Product</p>
                        <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                            <li><a href="{{ route('finder') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Alternatives</a></li>
                            <li><a href="{{ route('whats-new') }}" class="hover:text-slate-800 dark:hover:text-slate-200">What's new</a></li>
                            <li><a href="{{ route('alternatives.compare') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Compare</a></li>
                            <li><a href="{{ route('domains') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Domains</a></li>
                            <li><a href="{{ route('suggest') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Suggest</a></li>
                            <li><a href="{{ url('/feed') }}" class="hover:text-slate-800 dark:hover:text-slate-200">RSS</a></li>
                            <li><a href="{{ url('/api/alternatives') }}" class="hover:text-slate-800 dark:hover:text-slate-200">API</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white mb-3">Company</p>
                        <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                            <li><a href="{{ route('about') }}" class="hover:text-slate-800 dark:hover:text-slate-200">About</a></li>
                            <li><a href="{{ route('contact') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white mb-3">Legal</p>
                        <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                            <li><a href="{{ route('privacy') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Privacy</a></li>
                            <li><a href="{{ route('terms') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Terms</a></li>
                            <li><a href="{{ route('disclosure') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Affiliate disclosure</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400">
                © {{ date('Y') }} Alternova. All trademarks belong to their owners.
            </div>
        </div>
    </footer>

    <script>
        function themeApp() {
            return {
                dark: false,
                init() {
                    const saved = localStorage.getItem('alternova-theme');
                    if (saved === 'dark') this.dark = true;
                    else if (saved === 'light') this.dark = false;
                    else this.dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.apply();
                },
                toggle() {
                    this.dark = !this.dark;
                    localStorage.setItem('alternova-theme', this.dark ? 'dark' : 'light');
                    this.apply();
                },
                apply() {
                    document.documentElement.classList.toggle('dark', this.dark);
                }
            }
        }

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('{{ url('/sw.js') }}').catch(function () {});
            });
        }
    </script>
    @livewireScripts
</body>
</html>
