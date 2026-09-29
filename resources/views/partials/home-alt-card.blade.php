<a href="{{ route('alternatives.show', $alt) }}"
    class="group rounded-2xl border border-white/10 bg-white/[0.04] p-6 hover:border-brand-400/50 hover:bg-white/[0.07] transition {{ method_exists($alt, 'hasActiveSponsorship') && $alt->hasActiveSponsorship() ? 'ring-1 ring-amber-400/40' : '' }}">
    <div class="flex items-start justify-between gap-3">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                @if(method_exists($alt, 'hasActiveSponsorship') && $alt->hasActiveSponsorship())
                    <span class="rounded-full bg-amber-500/25 text-amber-200 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5">
                        {{ $alt->sponsor_label ?: 'Sponsored' }}
                    </span>
                @elseif($alt->is_featured)
                    <span class="rounded-full bg-brand-500/25 text-brand-200 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5">
                        Featured
                    </span>
                @endif
            </div>
            <h3 class="text-lg font-semibold text-white group-hover:text-brand-200 transition">{{ $alt->name }}</h3>
            <p class="mt-1 text-sm text-slate-400">
                vs
                @if($alt->proprietaryTool)
                    <span class="text-slate-300">{{ $alt->proprietaryTool->name }}</span>
                @else
                    proprietary tools
                @endif
            </p>
        </div>
        <span class="shrink-0 rounded-full bg-brand-500/20 text-brand-200 text-xs font-semibold px-2.5 py-1 border border-brand-400/20">
            {{ number_format($alt->overall_health_score, 0) }}
        </span>
    </div>
    <p class="mt-3 text-sm text-slate-400 line-clamp-2">{{ $alt->description }}</p>
    <div class="mt-4 flex flex-wrap gap-2">
        @if($alt->license_type)
            <span class="text-xs rounded-full bg-white/10 text-slate-300 px-2 py-0.5">{{ $alt->license_type }}</span>
        @endif
        @if($alt->repoMetric)
            <span class="text-xs rounded-full bg-white/10 text-slate-300 px-2 py-0.5">★ {{ number_format($alt->repoMetric->github_stars) }}</span>
        @endif
        @if(($alt->votes_count ?? 0) > 0)
            <span class="text-xs rounded-full bg-white/10 text-slate-300 px-2 py-0.5">{{ $alt->votes_count }} votes</span>
        @endif
    </div>
</a>
