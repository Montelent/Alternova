@component('layouts.app', [
    'title' => 'Collection submission status | Alternova',
    'robots' => 'noindex,follow',
])
<div class="max-w-lg mx-auto px-4 py-14">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Collection suggestion</h1>
    <p class="mt-2 text-sm text-slate-500">Public tracking page.</p>

    <div class="mt-8 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <span class="text-sm text-slate-500">Status</span>
            @php
                $color = match($submission->status) {
                    'approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                    'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
                    default => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                };
            @endphp
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $color }}">
                {{ $submission->statusLabel() }}
            </span>
        </div>

        <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Title</p>
            <p class="font-semibold text-slate-900 dark:text-white">{{ $submission->title }}</p>
        </div>

        @if($submission->description)
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Description</p>
                <p class="text-sm text-slate-700 dark:text-slate-300">{{ $submission->description }}</p>
            </div>
        @endif

        @if(is_array($submission->alternative_slugs) && count($submission->alternative_slugs))
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Suggested tools</p>
                <p class="text-sm font-mono text-slate-600 dark:text-slate-400">{{ implode(', ', $submission->alternative_slugs) }}</p>
            </div>
        @endif

        <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Submitted</p>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $submission->created_at?->toDayDateTimeString() }}</p>
        </div>

        @if($submission->status === 'approved' && $submission->created_collection_id)
            @php $coll = \App\Models\Collection::query()->find($submission->created_collection_id); @endphp
            @if($coll && $coll->is_published)
                <a href="{{ route('collections.show', $coll->slug) }}" class="inline-block text-sm font-semibold text-brand-600 dark:text-brand-300 hover:underline">View published collection →</a>
            @else
                <p class="text-sm text-emerald-600">Approved — publishing soon.</p>
            @endif
        @elseif($submission->status === 'rejected')
            <p class="text-sm text-slate-500">Not approved this time. You can submit a revised idea.</p>
        @else
            <p class="text-sm text-slate-500">Editors review collection ideas regularly. Bookmark this page.</p>
        @endif
    </div>

    <div class="mt-6 flex gap-3 text-sm">
        <a href="{{ route('suggest.collection') }}" class="font-semibold text-brand-600 hover:underline">Suggest another</a>
        <a href="{{ route('collections.index') }}" class="text-slate-500 hover:underline">Browse collections</a>
    </div>
</div>
@endcomponent
