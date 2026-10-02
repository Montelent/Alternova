@if(isset($featured) && $featured->isNotEmpty())
    <section class="py-12 border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4 mb-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Featured alternatives</h2>
                <a href="{{ route('finder') }}" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">View all</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($featured as $alt)
                    @include('partials.home-alt-card', ['alt' => $alt])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($recent) && $recent->isNotEmpty())
    <section class="py-12 border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4 mb-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Recently added</h2>
                <a href="{{ route('finder', ['sort' => 'newest']) }}" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">Newest</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($recent as $alt)
                    @include('partials.home-alt-card', ['alt' => $alt])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($tools) && $tools->isNotEmpty())
    <section class="py-12 border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Browse by product</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Open-source options listed for each proprietary product.</p>
            <div class="flex flex-wrap gap-3">
                @foreach($tools as $tool)
                    <a href="{{ route('alternativesto.show', $tool->slug) }}"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:border-brand-400 hover:text-brand-600 transition">
                        {{ $tool->name }}
                        <span class="text-xs text-slate-400">{{ $tool->alternatives_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="py-14">
        <div class="mx-auto max-w-6xl px-4 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['alternatives'] ?? 0 }}</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Published alternatives</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['tools'] ?? 0 }}</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Proprietary tools</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">DNS + RDAP</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Domain checks</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">API + RSS</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Open feeds</div>
            </div>
        </div>
    </section>
