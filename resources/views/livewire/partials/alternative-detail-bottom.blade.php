@include('partials.alternative-editorial', ['alternative' => $alternative, 'editorial' => $editorial ?? []])
        @include('livewire.partials.changelog-gallery', ['alternative' => $alternative])

        @php
            $curatedPros = array_values(array_filter((array) ($alternative->pros ?? [])));
            $curatedCons = array_values(array_filter((array) ($alternative->cons ?? [])));
        @endphp
        @if(count($curatedPros) || count($curatedCons))
            <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8" aria-labelledby="curated-pros-cons-heading">
                <div class="mb-6">
                    <h2 id="curated-pros-cons-heading" class="text-xl font-bold text-slate-900 dark:text-white">Pros & cons</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Editor summary for {{ $alternative->name }}.</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wide text-emerald-600 dark:text-emerald-400 mb-3">Pros</h3>
                        @if(count($curatedPros))
                            <ul class="space-y-2">
                                @foreach($curatedPros as $pro)
                                    <li class="flex gap-2.5 items-start rounded-xl border border-emerald-100 dark:border-emerald-900/40 bg-emerald-50/60 dark:bg-emerald-950/25 px-3 py-2.5">
                                        <span class="text-emerald-600 font-bold">✓</span>
                                        <span class="text-sm text-slate-800 dark:text-slate-100">{{ $pro }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-400">No pros listed yet.</p>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wide text-rose-600 dark:text-rose-400 mb-3">Cons</h3>
                        @if(count($curatedCons))
                            <ul class="space-y-2">
                                @foreach($curatedCons as $con)
                                    <li class="flex gap-2.5 items-start rounded-xl border border-rose-100 dark:border-rose-900/40 bg-rose-50/60 dark:bg-rose-950/25 px-3 py-2.5">
                                        <span class="text-rose-600 font-bold">−</span>
                                        <span class="text-sm text-slate-800 dark:text-slate-100">{{ $con }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-400">No cons listed yet.</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <div class="mb-8">
            <livewire:pros-cons-section :alternative-id="$alternative->id" :key="'proscons-'.$alternative->id" />
        </div>

        @if($alternative->docker_compose_blueprint)
            <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8" x-data="{ copied: false }">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Docker Compose blueprint</h2>
                    <button type="button" @click="navigator.clipboard.writeText($refs.code.textContent); copied = true; setTimeout(() => copied = false, 2000)"
                        class="rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-1.5 text-xs font-semibold">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak class="text-emerald-600">Copied</span>
                    </button>
                </div>
                <pre x-ref="code" class="overflow-x-auto rounded-xl bg-slate-950 text-slate-100 text-xs p-4"><code>{{ $alternative->docker_compose_blueprint }}</code></pre>
            </section>
        @endif

        @if(($proprietaryTools ?? collect())->isNotEmpty())
            <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">Compared to proprietary tools</h2>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    @foreach($proprietaryTools as $pt)
                        <a href="{{ route('alternativesto.show', $pt->slug) }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">{{ $pt->name }}</a>@if(!$loop->last), @endif
                    @endforeach
                </p>
            </section>
        @endif

        @if(isset($related) && $related->isNotEmpty())
            <section class="mb-10">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Related alternatives</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($related as $rel)
                        <a href="{{ route('alternatives.show', $rel) }}" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 hover:border-brand-400 transition">
                            <div class="font-semibold text-slate-900 dark:text-white">{{ $rel->name }}</div>
                            <div class="text-xs text-slate-500 mt-1">Health {{ number_format($rel->overall_health_score ?? 0, 1) }}</div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mb-10">
            <livewire:comment-section :alternative-id="$alternative->id" :key="'comments-'.$alternative->id" />
        </div>
    </div>
</div>
