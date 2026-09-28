@if(isset($related) && $related->isNotEmpty())
<section class="mt-10 mb-4" aria-label="Related alternatives">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900">More alternatives to {{ $propName }}</h2>
            <p class="mt-1 text-sm text-slate-500">Other open-source options in the same category.</p>
        </div>
        <div class="flex flex-wrap gap-3 text-sm font-semibold shrink-0">
            <a href="{{ route('alternatives.compare') }}" class="text-brand-600 hover:underline">Compare tools</a>
            <a href="{{ route('finder') }}" class="text-slate-500 hover:underline">View all</a>
        </div>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($related as $item)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-brand-300 hover:shadow-md transition">
                <a href="{{ route('alternatives.show', $item) }}" class="group block">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold text-slate-900 group-hover:text-brand-600 transition">{{ $item->name }}</h3>
                        <span class="text-xs font-semibold rounded-full bg-brand-50 text-brand-700 px-2 py-0.5">
                            {{ number_format($item->overall_health_score, 0) }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ $item->description }}</p>
                </a>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if($item->license_type)
                        <span class="text-xs rounded-full bg-slate-100 text-slate-600 px-2 py-0.5">{{ $item->license_type }}</span>
                    @endif
                    @if($item->repoMetric)
                        <span class="text-xs rounded-full bg-slate-100 text-slate-600 px-2 py-0.5">★ {{ number_format($item->repoMetric->github_stars) }}</span>
                    @endif
                    <a href="{{ route('alternatives.compare', ['a' => $alternative->slug, 'b' => $item->slug]) }}"
                        class="text-xs font-semibold text-brand-600 hover:underline ml-auto">
                        Compare →
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
