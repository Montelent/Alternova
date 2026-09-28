@php
    $repo = $alternative->repo_url ?? '';
    $compose = $alternative->docker_compose_blueprint ?? '';
@endphp
<div class="flex flex-wrap gap-2 print:hidden" x-data="{ copied: '' }">
    @if($repo)
        <button type="button"
            @click="navigator.clipboard.writeText(@js($repo)); copied='repo'; setTimeout(() => copied='', 1500)"
            class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
            <span x-text="copied === 'repo' ? 'Copied repo' : 'Copy repo URL'"></span>
        </button>
    @endif
    @if($compose)
        <button type="button"
            @click="navigator.clipboard.writeText(@js($compose)); copied='compose'; setTimeout(() => copied='', 1500)"
            class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
            <span x-text="copied === 'compose' ? 'Copied compose' : 'Copy docker-compose'"></span>
        </button>
    @endif
    <button type="button"
        @click="navigator.clipboard.writeText(@js(route('alternatives.show', $alternative))); copied='page'; setTimeout(() => copied='', 1500)"
        class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
        <span x-text="copied === 'page' ? 'Copied link' : 'Copy page link'"></span>
    </button>
</div>
