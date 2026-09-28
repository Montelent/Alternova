<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance — Alternova</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white flex items-center justify-center px-4">
    <div class="max-w-md text-center">
        <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 font-bold text-xl">A</div>
        <h1 class="text-2xl font-bold tracking-tight">We'll be right back</h1>
        <p class="mt-4 text-slate-400 leading-relaxed">{{ $message ?? 'Alternova is temporarily offline for maintenance.' }}</p>
        <p class="mt-8 text-xs text-slate-600">HTTP 503 · Maintenance mode</p>
    </div>
</body>
</html>
