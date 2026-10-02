{{-- Long-form guide + FAQ on alternative detail pages --}}
@if(!empty($editorial['guide_html']))
<section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8" aria-labelledby="alt-guide-heading">
    <h2 id="alt-guide-heading" class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
        In-depth guide to {{ $alternative->name }}
    </h2>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        Editorial overview for operators and evaluators
        @if(!empty($editorial['word_count']))
            · {{ $editorial['word_count'] }} words
        @endif
    </p>
    <div class="mt-5 space-y-4 text-[15px] sm:text-base text-slate-700 dark:text-slate-300 leading-relaxed max-w-none">
        {!! $editorial['guide_html'] !!}
    </div>

    @if(!empty($editorial['faq']) && count($editorial['faq']))
        <div class="mt-10 pt-8 border-t border-slate-200 dark:border-slate-800" x-data="{ open: 0 }">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Questions teams ask about {{ $alternative->name }}</h3>
            <div class="space-y-3">
                @foreach($editorial['faq'] as $i => $item)
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <button type="button"
                            class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800/80"
                            @click="open = open === {{ $i }} ? null : {{ $i }}">
                            <span>{{ $item['q'] }}</span>
                            <span class="text-slate-400" x-text="open === {{ $i }} ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === {{ $i }}" x-cloak class="px-4 pb-3 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ $item['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endif
