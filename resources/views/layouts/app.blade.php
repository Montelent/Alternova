<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Alternova') }}</title>
    <meta name="description" content="{{ $description ?? 'Discover open-source alternatives and generate brandable domain names with Alternova.' }}">
    <meta name="robots" content="{{ $robots ?? 'index,follow,max-image-preview:large,max-snippet:-1' }}">
    @if(!empty($canonical))
        <link rel="canonical" href="{{ $canonical }}">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ config('app.name', 'Alternova') }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $title ?? config('app.name', 'Alternova') }}">
    <meta property="og:description" content="{{ $description ?? 'Discover open-source alternatives and generate brandable domain names with Alternova.' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta property="og:locale" content="en_US">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? config('app.name', 'Alternova') }}">
    <meta name="twitter:description" content="{{ $description ?? 'Discover open-source alternatives and generate brandable domain names with Alternova.' }}">
    @if(!empty($ogImage))
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
<body class="font-sans antialiased bg-slate-50 text-slate-900 min-h-screen flex flex-col">
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-lg">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-14 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-slate-900">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-white text-xs">A</span>
                    Alternova
                </a>
                <nav class="flex items-center gap-1 sm:gap-2 text-sm font-medium">
                    <a href="{{ route('finder') }}"
                        class="px-3 py-1.5 rounded-lg {{ request()->routeIs('finder','alternatives.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        Alternatives
                    </a>
                    <a href="{{ route('domains') }}"
                        class="px-3 py-1.5 rounded-lg {{ request()->routeIs('domains') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        Domains
                    </a>
                    <a href="{{ url('/admin') }}" class="ml-1 px-3 py-1.5 rounded-lg text-slate-500 hover:bg-slate-100 text-xs sm:text-sm">
                        Admin
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 bg-white py-8 mt-auto">
        <div class="mx-auto max-w-7xl px-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500">
            <span>© {{ date('Y') }} Alternova</span>
            <div class="flex gap-5">
                <a href="{{ route('finder') }}" class="hover:text-slate-800">Alternatives</a>
                <a href="{{ route('domains') }}" class="hover:text-slate-800">Domains</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
