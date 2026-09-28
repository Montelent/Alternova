<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Open-source alternatives to {{ $tool->name }} — Alternova</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-slate-900 font-sans antialiased p-3">
    <div class="border border-slate-200 rounded-xl overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Alternova</p>
                <p class="text-sm font-bold text-slate-900">Open-source alternatives to {{ $tool->name }}</p>
            </div>
            <a href="{{ route('tools.show', $tool) }}" target="_blank" rel="noopener"
                class="text-xs font-semibold text-indigo-600 hover:underline shrink-0">View all</a>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($alternatives as $alt)
                <li>
                    <a href="{{ route('alternatives.show', $alt) }}" target="_blank" rel="noopener"
                        class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $alt->name }}</p>
                            <p class="text-xs text-slate-500 truncate">
                                {{ $alt->license_type ?? 'Open source' }}
                                @if($alt->repoMetric)
                                    · ★ {{ number_format($alt->repoMetric->github_stars) }}
                                @endif
                            </p>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 shrink-0">{{ number_format($alt->overall_health_score, 0) }}</span>
                    </a>
                </li>
            @empty
                <li class="px-4 py-6 text-sm text-slate-500 text-center">No published alternatives yet.</li>
            @endforelse
        </ul>
        <div class="bg-slate-50 border-t border-slate-100 px-4 py-2 text-[10px] text-slate-400 text-center">
            Powered by <a href="{{ url('/') }}" target="_blank" rel="noopener" class="text-indigo-600">Alternova</a>
        </div>
    </div>
</body>
</html>
