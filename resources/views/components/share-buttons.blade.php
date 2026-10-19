@php
    $shareUrl = $url ?? url()->current();
    $shareTitle = $title ?? (string) ($__env->yieldContent('title') ?: config('app.name'));
    $shareText = $text ?? $shareTitle;
    $shareImage = $image ?? null;
    $isCompact = (bool) ($compact ?? false);
    $siteName = config('app.name', 'Alternova');
    $cleanText = \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags((string) $shareText)) ?: (string) $shareTitle, 200);
    $tweetText = \Illuminate\Support\Str::limit("{$shareTitle}\n\n{$cleanText}", 240);
    $threadsPost = \Illuminate\Support\Str::limit("🔥 {$shareTitle}\n\n{$cleanText}\n\n👉 {$shareUrl}\n\n#OpenSource #SelfHosted #FOSS #{$siteName}", 480);
    $u = rawurlencode($shareUrl);
    $t = rawurlencode($tweetText);
    $ti = rawurlencode($shareTitle);
    $threadsEnc = rawurlencode($threadsPost);
    $rt = rawurlencode(\Illuminate\Support\Str::limit($shareTitle, 280));
    $img = $shareImage ? rawurlencode($shareImage) : '';
@endphp
<div
    x-data="{
        copied: false,
        shareUrl: @js($shareUrl),
        async copyLink() {
            try { await navigator.clipboard.writeText(this.shareUrl); this.copied = true; setTimeout(() => this.copied = false, 1800); }
            catch (e) {
                const ta = document.createElement('textarea'); ta.value = this.shareUrl; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
                this.copied = true; setTimeout(() => this.copied = false, 1800);
            }
        },
        printPage() { window.print(); },
        nativeShare() {
            if (navigator.share) navigator.share({ title: @js($shareTitle), text: @js($cleanText), url: this.shareUrl }).catch(() => {});
        }
    }"
    class="share-buttons {{ $isCompact ? '' : 'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm' }}"
    role="group" aria-label="Share this page"
>
    @unless($isCompact)
        <div class="flex items-center gap-2 mb-3">
            <svg class="h-4 w-4 text-brand-600 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13"/></svg>
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Share this page</p>
        </div>
    @endunless

    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2">
        @php $btn = 'inline-flex flex-col sm:flex-row items-center justify-center gap-1.5 min-h-[2.75rem] px-2 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 transition text-[11px] sm:text-xs font-semibold'; @endphp

        <a href="https://twitter.com/intent/tweet?url={{ $u }}&text={{ $t }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-black hover:border-black hover:text-white" title="Share on X" aria-label="Share on X">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            <span>X</span>
        </a>
        <a href="https://www.threads.net/intent/post?text={{ $threadsEnc }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-black hover:border-black hover:text-white" title="Share on Threads" aria-label="Share on Threads">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M14.5 9.5c-.8-1-2.1-1.5-3.5-1.3-1.8.2-3.2 1.7-3.3 3.5-.2 2.3 1.5 4.3 3.8 4.5 1.5.1 2.9-.6 3.6-1.8"/></svg>
            <span>Threads</span>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $u }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-[#1877F2] hover:border-[#1877F2] hover:text-white" title="Share on Facebook" aria-label="Share on Facebook">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
            <span>Facebook</span>
        </a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $u }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-[#0A66C2] hover:border-[#0A66C2] hover:text-white" title="Share on LinkedIn" aria-label="Share on LinkedIn">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            <span>LinkedIn</span>
        </a>
        <a href="https://api.whatsapp.com/send?text={{ $t }}%20{{ $u }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-[#25D366] hover:border-[#25D366] hover:text-white" title="Share on WhatsApp" aria-label="Share on WhatsApp">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            <span>WhatsApp</span>
        </a>
        <a href="https://www.reddit.com/submit?url={{ $u }}&title={{ $rt }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-[#FF4500] hover:border-[#FF4500] hover:text-white" title="Share on Reddit" aria-label="Share on Reddit">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 01-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.05 1.605a3.55 3.55 0 01.04.52c0 2.668-3.11 4.83-6.95 4.83-3.84 0-6.95-2.162-6.95-4.83 0-.175.014-.347.04-.515A1.756 1.756 0 013.35 10.5c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 01.14-.197.35.35 0 01.238-.028l2.907.617a1.214 1.214 0 011.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.561-1.249-1.249-1.249zm-5.038 3.75a.77.77 0 00-.528.194.76.76 0 00.007 1.074c.828.75 2.048 1.13 3.309 1.13 1.26 0 2.48-.379 3.309-1.13a.76.76 0 00.007-1.074.77.77 0 00-.528-.194h-.013a.76.76 0 00-.531.22c-.601.545-1.473.844-2.244.844-.771 0-1.642-.299-2.244-.844a.76.76 0 00-.53-.22h-.014z"/></svg>
            <span>Reddit</span>
        </a>
        <a href="https://bsky.app/intent/compose?text={{ $t }}%20{{ $u }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-[#1185FE] hover:border-[#1185FE] hover:text-white" title="Share on Bluesky" aria-label="Share on Bluesky">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.136.022-.273.043-.415.056-3.912.58-7.387 2.005-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.05 9.271 7.733 4.308 4.267-4.308 1.172-6.498-2.74-7.078a8.741 8.741 0 01-.415-.056c.14.017.279.036.415.056 2.67.297 5.568-.628 6.383-3.364.246-.828.624-5.79.624-6.478 0-.69-.139-1.861-.902-2.206-.659-.298-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8z"/></svg>
            <span>Bluesky</span>
        </a>
        <a href="https://t.me/share/url?url={{ $u }}&text={{ $t }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-[#26A5E4] hover:border-[#26A5E4] hover:text-white" title="Share on Telegram" aria-label="Share on Telegram">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            <span>Telegram</span>
        </a>
        <a href="https://pinterest.com/pin/create/button/?url={{ $u }}&description={{ $ti }}@if($img)&media={{ $img }}@endif" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-[#E60023] hover:border-[#E60023] hover:text-white" title="Share on Pinterest" aria-label="Share on Pinterest">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.624 0 11.99-5.367 11.99-11.987C24.007 5.367 18.641.001 12.017.001z"/></svg>
            <span>Pinterest</span>
        </a>
        <a href="https://www.tumblr.com/widgets/share/tool?canonicalUrl={{ $u }}&title={{ $ti }}&caption={{ $t }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-[#36465D] hover:border-[#36465D] hover:text-white" title="Share on Tumblr" aria-label="Share on Tumblr">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14.563 24c-5.093 0-7.031-3.756-7.031-6.411V9.747H5.116V6.648c3.63-1.313 4.512-4.596 4.71-6.469C9.84.051 9.941 0 9.999 0h3.517v6.123h4.801v3.624h-4.82v7.39c.016 1.18.525 2.804 2.508 2.804h.148c.905 0 1.9-.12 2.74-.37v3.763c-.91.392-2.42.663-3.33.663z"/></svg>
            <span>Tumblr</span>
        </a>
        <a href="https://vk.com/share.php?url={{ $u }}&title={{ $ti }}&comment={{ $t }}" target="_blank" rel="noopener noreferrer" class="{{ $btn }} hover:bg-[#0077FF] hover:border-[#0077FF] hover:text-white" title="Share on VK" aria-label="Share on VK">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.391 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.372 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.762-.491h1.744c.525 0 .643.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/></svg>
            <span>VK</span>
        </a>
        <button type="button" @click="copyLink()" class="{{ $btn }} hover:bg-emerald-600 hover:border-emerald-600 hover:text-white" title="Copy link" aria-label="Copy link">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
        </button>
        <button type="button" @click="printPage()" class="{{ $btn }} hover:bg-slate-700 hover:border-slate-700 hover:text-white" title="Print" aria-label="Print this page">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
            <span>Print</span>
        </button>
        <button type="button" @click="nativeShare()" class="md:hidden {{ $btn }} border-brand-200 dark:border-brand-700 bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-200 hover:bg-brand-600 hover:border-brand-600 hover:text-white" title="Share via device" aria-label="Share via device">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
            <span>More</span>
        </button>
    </div>
</div>
