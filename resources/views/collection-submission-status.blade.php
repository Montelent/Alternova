<x-layouts.app :title="'Collection submission status | Alternova'">
    <div class="mx-auto max-w-lg px-4 py-16 text-center">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $submission->title }}</h1>
        <p class="mt-4 inline-flex rounded-full px-3 py-1 text-sm font-semibold
            {{ $submission->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : '' }}
            {{ $submission->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200' : '' }}
            {{ $submission->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' : '' }}">
            {{ $submission->statusLabel() }}
        </p>
        @if($submission->description)
            <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">{{ $submission->description }}</p>
        @endif
        @if($submission->status === 'approved' && $submission->created_collection_id)
            @php
                $coll = \App\Models\Collection::query()->find($submission->created_collection_id);
            @endphp
            @if($coll && $coll->is_published)
                <a href="{{ route('collections.show', $coll->slug) }}" class="mt-6 inline-block text-brand-600 dark:text-brand-300 font-semibold hover:underline">
                    View published collection →
                </a>
            @endif
        @endif
        <p class="mt-10 text-sm text-slate-500">
            <a href="{{ route('collections.index') }}" class="hover:underline">All collections</a>
            ·
            <a href="{{ route('suggest.collection') }}" class="hover:underline">Suggest another</a>
        </p>
    </div>
</x-layouts.app>
