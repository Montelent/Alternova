@php
    $propTools = collect();
    try {
        if ($alt->relationLoaded('proprietaryTools') && $alt->proprietaryTools->isNotEmpty()) {
            $propTools = $alt->proprietaryTools;
        } elseif ($alt->proprietaryTool) {
            $propTools = collect([$alt->proprietaryTool]);
        }
    } catch (\Throwable) {
        if ($alt->proprietaryTool ?? null) {
            $propTools = collect([$alt->proprietaryTool]);
        }
    }
@endphp
<a href="{{ route('alternatives.show', $alt) }}"
    class="group flex flex-col h-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80 p-5 sm:p-6 shadow-sm hover:border-brand-300 dark:hover:border-brand-500/60 hover:shadow-md transition {{ method_exists($alt, 'hasActiveSponsorship') && $alt->hasActiveSponsorship() ? 'ring-1 ring-amber-400/50' : '' }}">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                @if(method_exists($alt, 'hasActiveSponsorship') && $alt->hasActiveSponsorship())
                    <span class="rounded-full bg-amber-100 dark:bg-amber-500/25 text-amber-800 dark:text-amber-200 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5">
                        {{ $alt->sponsor_label ?: 'Sponsored' }}
                    </span>
                @elseif($alt->is_featured ?? false)
                    <span class="rounded-full bg-brand-50 dark:bg-brand-500/25 text-brand-700 dark:text-brand-200 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5">
                        Featured
                    </span>
                @endif
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-300 transition leading-snug">
                {{ $alt->name }}
            </h3>
            <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400 leading-snug">
                Open-source alternative to
                @forelse($propTools as $pt)
                    <span
                        role="link"
                        tabindex="0"
                        class="inline-flex items-center gap-1 font-semibold text-brand-600 dark:text-brand-400 hover:underline underline-offset-2 cursor-pointer"
                        onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('alternativesto.show', $pt->slug) }}';"
                        onkeydown="if(event.key==='Enter'){event.preventDefault();event.stopPropagation();window.location.href='{{ route('alternativesto.show', $pt->slug) }}';}"
                    >
                        @if($pt->logo_url)
                            <img src="{{ $pt->logo_url }}" alt="" class="h-4 w-4 rounded object-contain bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700" loading="lazy" width="16" height="16">
                        @endif
                        {{ $pt->name }}
                    </span>@if(!$loop->last)<span class="text-slate-400">,</span>@endif
                @empty
                    <span class="text-slate-700 dark:text-slate-300">proprietary tools</span>
                @endforelse
            </p>
        </div>
        <span class="shrink-0 rounded-full bg-brand-50 dark:bg-brand-500/20 text-brand-700 dark:text-brand-200 text-xs font-semibold px-2.5 py-1 border border-brand-100 dark:border-brand-400/20" title="Health score">
            {{ number_format($alt->overall_health_score ?? 0, 0) }}
        </span>
    </div>
    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 line-clamp-2 flex-1">
        {{ \Illuminate\Support\Str::limit(strip_tags((string) ($alt->description ?? '')), 120) }}
    </p>
    <div class="mt-4 flex flex-wrap gap-2">
        @if($alt->license_type)
            <span class="text-xs rounded-full bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 px-2 py-0.5">{{ $alt->license_type }}</span>
        @endif
        @if($alt->primary_language)
            <span class="text-xs rounded-full bg-sky-50 dark:bg-sky-500/15 text-sky-800 dark:text-sky-200 px-2 py-0.5">{{ $alt->primary_language }}</span>
        @endif
        @if($alt->repoMetric)
            <span class="text-xs rounded-full bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 px-2 py-0.5">★ {{ number_format($alt->repoMetric->github_stars) }}</span>
        @endif
        @if(($alt->votes_count ?? 0) > 0)
            <span class="text-xs rounded-full bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 px-2 py-0.5">{{ $alt->votes_count }} votes</span>
        @endif
    </div>
</a>
