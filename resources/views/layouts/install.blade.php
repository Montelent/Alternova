<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install – Alternova</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-950 min-h-screen antialiased text-slate-100">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 text-center">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-indigo-500/20 text-indigo-400 mb-4">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Alternova</h1>
            <p class="mt-2 text-sm text-slate-400">Open-source alternatives · Brandable domains</p>
            <p class="mt-1 text-xs text-slate-500">First-time installation</p>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
