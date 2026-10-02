<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="themeApp()" x-init="init()" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo-head')

    <link rel="alternate" type="application/rss+xml" title="Alternova Alternatives" href="{{ url('/feed') }}">
    <link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
    <meta name="theme-color" content="#4f46e5">

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
                        brand: {
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
        [x-cloak]{display:none!important}
        .dark input:not([type=checkbox]):not([type=radio]),
        .dark select,
        .dark textarea { color-scheme: dark; }
        .prose img { max-width: 100%; height: auto; }
        body { padding-left: env(safe-area-inset-left); padding-right: env(safe-area-inset-right); }
        body.menu-locked { overflow: hidden; touch-action: none; }
        /* Light mode: keep form controls readable when only dark: variants were set */
        input:not([type=checkbox]):not([type=radio]), select, textarea {
            color: #0f172a;
            background-color: #fff;
        }
        .dark input:not([type=checkbox]):not([type=radio]),
        .dark select,
        .dark textarea {
            color: #f1f5f9;
            background-color: #1e293b;
        }
        ::placeholder { color: #94a3b8; opacity: 1; }
        .dark ::placeholder { color: #64748b; opacity: 1; }
    </style>
</head>
<body
    class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 min-h-screen flex flex-col"
    x-data="{ mobileOpen: false }"
    @keydown.escape.window="mobileOpen = false"
    x-effect="document.body.classList.toggle('menu-locked', mobileOpen)"
>
    <div
        x-show="mobileOpen"
        x-cloak
        x-transition.opacity.duration.200ms
        @click="mobileOpen = false"
        class="lg:hidden fixed inset-0 z-40 bg-slate-900/50"
        style="top: 3.5rem;"
        aria-hidden="true"
    ></div>

    <header class="sticky top-0 z-50 border-b border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            <div class="flex h-14 items-center justify-between gap-2">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-slate-900 dark:text-white shrink-0 min-w-0">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-white text-xs">A</span>
                    <span class="truncate">Alternova</span>
                </a>

                <nav class="hidden lg:flex items-center gap-1 text-sm font-medium">
                    <a href="{{ route('finder') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('finder','alternatives.show','tools.show') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">Alternatives</a>
                    <a href="{{ route('browse.type', 'categories') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('browse.*') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">Browse</a>
                    <a href="{{ route('collections.index') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('collections.*') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">Collections</a>
                    <a href="{{ route('trending') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('trending') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">Trending</a>
                    <a href="{{ route('domains') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('domains') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">Domains</a>
                    @isset($navPages)
                        @foreach($navPages as $np)
                            <a href="{{ $np->publicUrl() }}" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">{{ $np->title }}</a>
                        @endforeach
                    @endisset
                </nav>

                <div class="flex items-center gap-0.5 sm:gap-1 shrink-0">
                    <button type="button" @click="toggle()" class="p-2.5 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" title="Toggle theme" aria-label="Toggle dark mode">
                        <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>

                    @auth
                        @php
                            $unreadNotifs = 0;
                            try { $unreadNotifs = app(\App\Services\UserNotificationService::class)->unreadCount(auth()->user()); } catch (\Throwable) {}
                        @endphp
                        <a href="{{ route('notifications') }}" class="relative hidden sm:inline-flex p-2.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" title="Notifications">
                            <span class="text-sm">🔔</span>
                            @if($unreadNotifs > 0)
                                <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white px-1">{{ $unreadNotifs > 9 ? '9+' : $unreadNotifs }}</span>
                            @endif
                        </a>
                        <a href="{{ route('account') }}" class="hidden md:inline-flex px-2.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">Account</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex px-2.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">Sign in</a>
                    @endauth

                    <a href="{{ url('/admin') }}" class="hidden md:inline-flex px-2.5 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">Admin</a>

                    <button type="button"
                        class="lg:hidden inline-flex items-center justify-center p-2.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen.toString()"
                        aria-controls="mobile-nav"
                        aria-label="Open menu">
                        <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-nav"
            x-show="mobileOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="lg:hidden relative z-50 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl max-h-[min(80vh,calc(100dvh-3.5rem))] overflow-y-auto overscroll-contain">
            <nav class="px-3 py-3 space-y-0.5 text-sm font-medium" @click.stop>
                <a href="{{ route('finder') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 {{ request()->routeIs('finder','alternatives.*','tools.*') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800' }}">Alternatives</a>
                <a href="{{ route('browse.type', 'categories') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 {{ request()->routeIs('browse.*') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800' }}">Browse hubs</a>
                <div class="pl-3 pb-1 flex flex-wrap gap-2">
                    <a href="{{ route('browse.type', 'categories') }}" @click="mobileOpen=false" class="rounded-full border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300">Categories</a>
                    <a href="{{ route('browse.type', 'languages') }}" @click="mobileOpen=false" class="rounded-full border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300">Languages</a>
                    <a href="{{ route('browse.type', 'licenses') }}" @click="mobileOpen=false" class="rounded-full border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300">Licenses</a>
                </div>
                <a href="{{ route('collections.index') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 {{ request()->routeIs('collections.*') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800' }}">Collections</a>
                <a href="{{ route('trending') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 {{ request()->routeIs('trending') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800' }}">Trending</a>
                <a href="{{ route('leaderboard') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">Rankings</a>
                <a href="{{ route('domains') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 {{ request()->routeIs('domains') ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200' : 'text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800' }}">Domains</a>
                <a href="{{ route('whats-new') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">What's new</a>
                <a href="{{ route('suggest') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">Suggest a tool</a>
                @isset($navPages)
                    @foreach($navPages as $np)
                        <a href="{{ $np->publicUrl() }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">{{ $np->title }}</a>
                    @endforeach
                @endisset

                <div class="my-2 border-t border-slate-100 dark:border-slate-800"></div>

                @auth
                    <a href="{{ route('notifications') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">Notifications</a>
                    <a href="{{ route('account') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">Account</a>
                    <a href="{{ route('favorites') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">Favorites</a>
                @else
                    <a href="{{ route('login') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">Sign in</a>
                    <a href="{{ route('register') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">Create account</a>
                @endauth
                <a href="{{ url('/admin') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-500 dark:text-slate-400 active:bg-slate-100 dark:active:bg-slate-800">Admin panel</a>
                <a href="{{ route('contact') }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-700 dark:text-slate-200 active:bg-slate-100 dark:active:bg-slate-800">Contact</a>
            </nav>
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
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-10">
                <div class="max-w-sm">
                    <div class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-white text-xs">A</span>
                        Alternova
                    </div>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        {{ \App\Models\SiteSetting::get('site_tagline', 'Open-source alternatives and brandable domain ideas.') }}
                    </p>
                    <div class="mt-5">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white mb-2">Get product updates</p>
                        @livewire('newsletter-subscribe', ['source' => 'footer'])
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-8 text-sm">
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white mb-3">Product</p>
                        <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                            <li><a href="{{ route('finder') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Alternatives</a></li>
                            <li><a href="{{ route('browse.type', 'categories') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Browse by category</a></li>
                            <li><a href="{{ route('browse.type', 'languages') }}" class="hover:text-slate-800 dark:hover:text-slate-200">By language</a></li>
                            <li><a href="{{ route('browse.type', 'licenses') }}" class="hover:text-slate-800 dark:hover:text-slate-200">By license</a></li>
                            <li><a href="{{ route('collections.index') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Collections</a></li>
                            <li><a href="{{ route('trending') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Trending</a></li>
                            <li><a href="{{ route('leaderboard') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Rankings</a></li>
                            <li><a href="{{ route('domains') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Domains</a></li>
                            <li><a href="{{ route('suggest') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Suggest tool</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white mb-3">Account</p>
                        <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                            @auth
                                <li><a href="{{ route('account') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Your account</a></li>
                                <li><a href="{{ route('notifications') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Notifications</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Sign in</a></li>
                                <li><a href="{{ route('register') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Create account</a></li>
                            @endauth
                            <li><a href="{{ route('contact') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white mb-3">Pages</p>
                        <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                            @isset($footerPages)
                                @forelse($footerPages as $fp)
                                    <li><a href="{{ $fp->publicUrl() }}" class="hover:text-slate-800 dark:hover:text-slate-200">{{ $fp->title }}</a></li>
                                @empty
                                    <li><a href="{{ route('about') }}" class="hover:text-slate-800 dark:hover:text-slate-200">About</a></li>
                                    <li><a href="{{ route('privacy') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Privacy</a></li>
                                    <li><a href="{{ route('terms') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Terms</a></li>
                                    <li><a href="{{ route('disclosure') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Disclosure</a></li>
                                @endforelse
                            @else
                                <li><a href="{{ route('about') }}" class="hover:text-slate-800 dark:hover:text-slate-200">About</a></li>
                                <li><a href="{{ route('privacy') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Privacy</a></li>
                                <li><a href="{{ route('terms') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Terms</a></li>
                                <li><a href="{{ route('disclosure') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Disclosure</a></li>
                            @endisset
                        </ul>
                    </div>
                </div>
            </div>
            <p class="mt-10 text-center text-xs text-slate-400">&copy; {{ date('Y') }} {{ config('app.name', 'Alternova') }}</p>
        </div>
    </footer>

    @livewireScripts
    <script>
        function themeApp() {
            return {
                dark: document.documentElement.classList.contains('dark'),
                init() {
                    this.dark = document.documentElement.classList.contains('dark');
                },
                toggle() {
                    this.dark = !this.dark;
                    document.documentElement.classList.toggle('dark', this.dark);
                    try { localStorage.setItem('alternova-theme', this.dark ? 'dark' : 'light'); } catch (e) {}
                }
            }
        }
    </script>
</body>
</html>
