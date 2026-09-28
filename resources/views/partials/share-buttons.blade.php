@php
    $shareUrl = urlencode($url ?? url()->current());
    $shareText = urlencode($text ?? ($title ?? config('app.name')));
@endphp
<div class="flex flex-wrap items-center gap-2" aria-label="Share">
    <span class="text-xs font-medium text-slate-500 mr-1">Share</span>
    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
        X
    </a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
        LinkedIn
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
        Facebook
    </a>
    <a href="https://www.reddit.com/submit?url={{ $shareUrl }}&title={{ $shareText }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
        Reddit
    </a>
    <button type="button"
        onclick="navigator.clipboard.writeText(decodeURIComponent('{{ $shareUrl }}')); this.innerText='Copied'; setTimeout(() => this.innerText='Copy link', 1500)"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
        Copy link
    </button>
</div>
