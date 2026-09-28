@component('layouts.app', [
    'title' => 'Submission status | Alternova',
    'robots' => 'noindex,follow',
])
<div class="max-w-lg mx-auto px-4 py-14">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Submission status</h1>
    <p class="mt-2 text-sm text-slate-500">Public tracking page — keep this link private if you prefer.</p>

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
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Alternative</p>
            <p class="font-semibold text-slate-900 dark:text-white">{{ $submission->alternative_name }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Replaces</p>
            <p class="text-slate-700 dark:text-slate-300">{{ $submission->proprietary_name }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Repo</p>
            <a href="{{ $submission->repo_url }}" target="_blank" rel="noopener" class="text-sm text-brand-600 break-all hover:underline">{{ $submission->repo_url }}</a>
        </div>
        <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Submitted</p>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $submission->created_at?->toDayDateTimeString() }}</p>
        </div>

        @if($submission->status === 'approved' && $submission->created_alternative_id)
            <p class="text-sm text-emerald-600 dark:text-emerald-400">This submission was approved and published on the directory.</p>
        @elseif($submission->status === 'rejected')
            <p class="text-sm text-slate-500">This submission was not approved. You may submit a revised entry from the suggest form.</p>
        @else
            <p class="text-sm text-slate-500">Our editors review submissions regularly. Bookmark this page to check back.</p>
        @endif
    </div>

    <div class="mt-6 flex gap-3 text-sm">
        <a href="{{ route('suggest') }}" class="font-semibold text-brand-600 hover:underline">Submit another</a>
        <a href="{{ route('finder') }}" class="text-slate-500 hover:underline">Browse alternatives</a>
    </div>
</div>
@endcomponent
