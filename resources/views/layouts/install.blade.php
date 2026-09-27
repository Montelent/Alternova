<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install – {{ config('app.name', 'Open Alt Finder') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen antialiased">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ config('app.name', 'Open Alt Finder') }}</h1>
            <p class="mt-2 text-sm text-gray-600">First-time installation</p>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
