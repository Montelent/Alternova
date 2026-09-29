@props(['series' => collect(), 'points' => '', 'trend' => 'flat'])

@php
    $stroke = match ($trend) {
        'up' => '#10b981',
        'down' => '#f43f5e',
        default => '#4f46e5',
    };
    $fill = match ($trend) {
        'up' => 'rgba(16,185,129,0.12)',
        'down' => 'rgba(244,63,94,0.12)',
        default => 'rgba(79,70,229,0.12)',
    };
    $labels = $series->pluck('label')->all();
    $first = $series->first();
    $last = $series->last();
    $delta = $first && $last ? round(((float) $last['score']) - ((float) $first['score']), 1) : 0;
@endphp

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8']) }}>
    <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Health score history</h2>
            <p class="text-sm text-slate-500 mt-1">Tracked when GitHub metrics sync. Max one snapshot per 6 hours if unchanged.</p>
        </div>
        @if($series->count() >= 2)
            <p class="text-sm font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-rose-500' : 'text-slate-500') }}">
                {{ $delta > 0 ? '+' : '' }}{{ $delta }} pts
            </p>
        @endif
    </div>

    @if($series->isEmpty() || ! $points)
        <p class="text-sm text-slate-500">No history yet. Run a GitHub metrics sync from admin to start tracking.</p>
    @else
        <div class="relative h-40 w-full">
            <svg viewBox="0 0 100 28" class="w-full h-full" preserveAspectRatio="none" role="img" aria-label="Health score over time">
                <defs>
                    <linearGradient id="healthFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="{{ $stroke }}" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="{{ $stroke }}" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                @php
                    $area = $points;
                    if ($points) {
                        $firstPt = explode(',', explode(' ', $points)[0]);
                        $lastPt = explode(',', collect(explode(' ', $points))->last());
                        $area = $points.' '.$lastPt[0].',28 '.$firstPt[0].',28';
                    }
                @endphp
                <polygon fill="url(#healthFill)" points="{{ $area }}" />
                <polyline fill="none" stroke="{{ $stroke }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" points="{{ $points }}" />
            </svg>
        </div>
        <div class="mt-2 flex justify-between text-xs text-slate-400">
            <span>{{ $labels[0] ?? '' }}</span>
            <span>{{ $labels[count($labels) - 1] ?? '' }}</span>
        </div>
        @if($last)
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                Latest: <strong>{{ number_format($last['score'], 1) }}</strong>/100
                @if(! empty($last['stars']))
                    · {{ number_format($last['stars']) }} stars
                @endif
            </p>
        @endif
    @endif
</section>
