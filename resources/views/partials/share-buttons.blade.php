@php
    $shareUrl = $url ?? url()->current();
    $shareTitle = $title ?? config('app.name');
    $shareText = $text ?? $shareTitle;
    $siteName = config('app.name', 'Alternova');
    $body = \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags((string) $shareText)) ?? $shareText, 220);
    $threadsPost = "🔥 {$shareTitle}\n\n{$body}\n\n👉 {$shareUrl}\n\n#OpenSource #SelfHosted #FOSS #{$siteName}";
    $u = rawurlencode($shareUrl);
    $t = rawurlencode($shareText);
    $threadsEnc = rawurlencode(\Illuminate\Support\Str::limit($threadsPost, 480));
@endphp
<div class="flex flex-wrap items-center gap-2" aria-label="Share">
    <span class="text-xs font-medium text-slate-500 mr-1">Share</span>
    <a href="https://twitter.com/intent/tweet?url={{ $u }}&text={{ $t }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-900 hover:text-white">X</a>
    <a href="https://www.threads.net/intent/post?text={{ $threadsEnc }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-900 hover:text-white">Threads</a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $u }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-[#1877F2] hover:text-white">Facebook</a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $u }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-[#0A66C2] hover:text-white">LinkedIn</a>
    <a href="https://www.reddit.com/submit?url={{ $u }}&title={{ $t }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-[#FF4500] hover:text-white">Reddit</a>
    <button type="button"
        onclick="navigator.clipboard.writeText(@js($shareUrl)); this.innerText='Copied'; setTimeout(() => this.innerText='Copy link', 1500)"
        class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-emerald-600 hover:text-white">Copy link</button>
</div>
