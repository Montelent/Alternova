<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Alternova') }}</title>
    @if(!empty($description))
        <meta name="description" content="{{ $description }}">
        <meta property="og:description" content="{{ $description }}">
    @endif
    <meta property="og:title" content="{{ $title ?? config('app.name', 'Alternova') }}">
    <meta property="og:type" content="website">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14 items-center">
                <a href="{{ route('home') }}" class="text-lg font-bold text-indigo-600">Alternova</a>
                <div class="flex gap-6 text-sm font-medium text-gray-600">
                    <a href="{{ route('finder') }}" class="hover:text-indigo-600">Alternatives</a>
                    <a href="{{ route('domains') }}" class="hover:text-indigo-600">Domains</a>
                    <a href="{{ url('/admin') }}" class="hover:text-indigo-600">Admin</a>
                </div>
            </div>
        </div>
    </nav>

    {{ $slot }}

    @livewireScripts
</body>
</html>
