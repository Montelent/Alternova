<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <nav class="mb-6 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('browse.type', $type) }}" class="hover:text-brand-600 capitalize">{{ $type }}</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $meta['name'] }}</span>
        </nav>

        <header class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $heading }}</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-400 text-sm">
                {{ $alternatives->total() }} published project{{ $alternatives->total() === 1 ? '' : 's' }}.
                <a href="{{ route('finder') }}" class="text-brand-600 dark:text-brand-400 font-semibold hover:underline">Open full finder</a>
            </p>
        </header>

        @if($alternatives->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-600 dark:text-slate-400">
                No published alternatives in this group yet.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @foreach($alternatives as $alt)
                    @include('partials.home-alt-card', ['alt' => $alt])
                @endforeach
            </div>
            <div class="mt-8">
                {{ $alternatives->links() }}
            </div>
        @endif
    </div>
</div>
