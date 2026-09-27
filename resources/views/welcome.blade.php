<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alternova – Open-source alternatives & brandable domains</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-white min-h-screen antialiased">
    <div class="max-w-5xl mx-auto px-6 py-24 text-center">
        <h1 class="text-5xl font-bold tracking-tight">Alternova</h1>
        <p class="mt-4 text-xl text-slate-400">Find open-source alternatives. Generate brandable domains.</p>
        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('finder') }}"
                class="inline-flex justify-center px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-medium">
                Open Source Finder
            </a>
            <a href="{{ route('domains') }}"
                class="inline-flex justify-center px-6 py-3 rounded-xl border border-slate-600 hover:border-slate-400 font-medium">
                Domain Combinator
            </a>
        </div>
    </div>
</body>
</html>
