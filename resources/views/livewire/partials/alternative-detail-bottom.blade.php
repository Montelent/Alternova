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
                        class="rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak class="text-emerald-600">Copied</span>
                    </button>
                </div>
                <pre x-ref="code" class="overflow-x-auto rounded-xl bg-slate-950 text-slate-100 text-xs p-4"><code>{{ $alternative->docker_compose_blueprint }}</code></pre>
            </section>
        @endif

        @if(($proprietaryTools ?? collect())->isNotEmpty() || !empty($proprietary))
            <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Compared to proprietary tools</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach(($proprietaryTools ?? collect())->isNotEmpty() ? $proprietaryTools : collect([$proprietary]) as $pt)
                        @continue(!$pt)
                        <a href="{{ route('alternativesto.show', $pt->slug) }}"
                            class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 hover:border-brand-400 dark:hover:border-brand-500 transition">
                            @if($pt->logo_url)
                                <img src="{{ $pt->logo_url }}" alt="" class="h-10 w-10 rounded-xl object-contain bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-1" loading="lazy" width="40" height="40">
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 text-sm font-bold">
                                    {{ strtoupper(\Illuminate\Support\Str::substr($pt->name, 0, 1)) }}
                                </span>
                            @endif
                            <span>
                                <span class="block font-semibold text-slate-900 dark:text-white">{{ $pt->name }}</span>
                                <span class="block text-xs text-brand-600 dark:text-brand-400">View all alternatives →</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if(isset($related) && $related->isNotEmpty())
            <section class="mb-10">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-5">Related alternatives</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                    @foreach($related as $rel)
                        @include('partials.home-alt-card', ['alt' => $rel])
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mb-10">
            <livewire:comment-section :alternative-id="$alternative->id" :key="'comments-'.$alternative->id" />
        </div>
    </div>
</div>
