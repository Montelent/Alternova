{{-- viral threads via threadsEnc --}}
@php
    $shareUrl = $url ?? url()->current();
    $shareTitle = $title ?? (string) ($__env->yieldContent('title') ?: config('app.name'));
    $shareText = $text ?? $shareTitle;
    $shareImage = $image ?? null;
    $isCompact = (bool) ($compact ?? false);
    $siteName = config('app.name', 'Alternova');
    $threadsBody = \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags((string) $shareText)) ?? $shareText, 220);
    $threadsPost = "🔥 {$shareTitle}\n\n{$threadsBody}\n\n👉 {$shareUrl}\n\n#OpenSource #SelfHosted #FOSS #{$siteName}";
    $threadsPost = \Illuminate\Support\Str::limit($threadsPost, 480);
    $u = rawurlencode($shareUrl);
    $t = rawurlencode($shareText);
    $ti = rawurlencode($shareTitle);
    $img = $shareImage ? rawurlencode($shareImage) : '';
    $threadsEnc = rawurlencode($threadsPost);
@endphp
{{-- Keep existing network UI by including original after injecting vars is hard; use parallel share bar --}}
<div
    x-data="{
        copied: false,
        shareUrl: @js($shareUrl),
        async copyLink() {
            try { await navigator.clipboard.writeText(this.shareUrl); this.copied = true; setTimeout(() => this.copied = false, 1600); } catch (e) {}
        },
        printPage() { window.print(); },
        nativeShare() {
            if (navigator.share) {
                navigator.share({ title: @js($shareTitle), text: @js($shareText), url: this.shareUrl }).catch(() => {});
            }
        }
    }"
    class="share-buttons {{ $isCompact ? '' : 'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm' }}"
>
    @unless($isCompact)
        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-3">Share this page</p>
    @endunless
    <div class="flex flex-wrap items-center gap-2">
        <a href="https://twitter.com/intent/tweet?url={{ $u }}&text={{ $t }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-900 hover:text-white transition" title="Share on X" aria-label="Share on X">X</a>
        <a href="https://www.threads.net/intent/post?text={{ $threadsEnc }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-1 h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-900 hover:text-white transition text-xs font-semibold" title="Share on Threads" aria-label="Share on Threads">Threads</a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $u }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-[#1877F2] hover:text-white transition" title="Share on Facebook" aria-label="Share on Facebook">f</a>
        <a href="https://bsky.app/intent/compose?text={{ $threadsEnc }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-sky-500 hover:text-white transition" title="Share on Bluesky" aria-label="Share on Bluesky">Bsky</a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $u }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-[#0A66C2] hover:text-white transition" title="Share on LinkedIn" aria-label="Share on LinkedIn">in</a>
        <a href="https://pinterest.com/pin/create/button/?url={{ $u }}&description={{ $t }}@if($img)&media={{ $img }}@endif" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-[#E60023] hover:text-white transition" title="Share on Pinterest" aria-label="Share on Pinterest">Pin</a>
        <a href="https://www.tumblr.com/widgets/share/tool?canonicalUrl={{ $u }}&title={{ $ti }}&caption={{ $t }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-[#36465D] hover:text-white transition" title="Share on Tumblr" aria-label="Share on Tumblr">Tumblr</a>
        <a href="https://vk.com/share.php?url={{ $u }}&title={{ $ti }}&comment={{ $t }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-[#0077FF] hover:text-white transition" title="Share on VK" aria-label="Share on VK">VK</a>
        <button type="button" @click="copyLink()" class="inline-flex items-center justify-center gap-1.5 h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-emerald-600 hover:text-white transition text-xs font-semibold" title="Copy link"><span x-text="copied ? 'Copied!' : 'Copy'"></span></button>
        <button type="button" @click="printPage()" class="inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-700 hover:text-white transition" title="Print">Print</button>
        <button type="button" @click="nativeShare()" class="inline-flex items-center justify-center h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-brand-600 hover:text-white transition text-xs font-semibold md:hidden">Share…</button>
    </div>
</div>
