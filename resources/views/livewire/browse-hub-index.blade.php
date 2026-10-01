<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <nav class="mb-6 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('finder') }}" class="hover:text-brand-600">Alternatives</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $heading }}</span>
        </nav>

        <header class="mb-8 sm:mb-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $heading }}</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-400 text-sm sm:text-base max-w-2xl">
                Pick a topic to see published open-source alternatives. All counts are live from this catalog.
            </p>
            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                <a href="{{ route('browse.type', 'categories') }}" @class(['rounded-full px-3 py-1.5 font-semibold border', $type === 'categories' ? 'bg-brand-600 text-white border-brand-600' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200'])">Categories</a>
                <a href="{{ route('browse.type', 'languages') }}" @class(['rounded-full px-3 py-1.5 font-semibold border', $type === 'languages' ? 'bg-brand-600 text-white border-brand-600' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200'])">Languages</a>
                <a href="{{ route('browse.type', 'licenses') }}" @class(['rounded-full px-3 py-1.5 font-semibold border', $type === 'licenses' ? 'bg-brand-600 text-white border-brand-600' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200'])">Licenses</a>
            </div>
        </header>

        @if(count($items) === 0)
            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-600 dark:text-slate-400">
                Nothing to show yet. Publish alternatives and assign categories, languages, or licenses in admin.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                @foreach($items as $item)
                    <a href="{{ route('browse.show', [$type, $item['slug']]) }}"
                        class="group flex items-center justify-between gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-4 hover:border-brand-300 dark:hover:border-brand-600 hover:shadow-sm transition">
                        <span class="font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400">{{ $item['name'] }}</span>
                        <span class="shrink-0 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold px-2.5 py-1">{{ $item['count'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
