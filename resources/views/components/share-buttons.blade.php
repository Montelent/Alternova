{{--
  Engaging social share bar.
  Props:
    $url   (string, optional) — defaults to current URL
    $title (string, optional)
    $text  (string, optional) — share body / tweet text
    $image (string, optional) — used by Pinterest
    $compact (bool, optional) — smaller icons only
--}}
@php
    $shareUrl = $url ?? url()->current();
    $shareTitle = $title ?? (string) ($__env->yieldContent('title') ?: config('app.name'));
    $shareText = $text ?? $shareTitle;
    $shareImage = $image ?? null;
    $isCompact = (bool) ($compact ?? false);

    $u = rawurlencode($shareUrl);
    $t = rawurlencode($shareText);
    $ti = rawurlencode($shareTitle);
    $img = $shareImage ? rawurlencode($shareImage) : '';

    $networks = [
        [
            'key' => 'x',
            'label' => 'X',
            'href' => 'https://twitter.com/intent/tweet?url='.$u.'&text='.$t,
            'color' => 'hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900',
            'svg' => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.727-8.849L1.254 2.25H8.08l4.253 5.622L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/>',
        ],
        [
            'key' => 'facebook',
            'label' => 'Facebook',
            'href' => 'https://www.facebook.com/sharer/sharer.php?u='.$u,
            'color' => 'hover:bg-[#1877F2] hover:text-white',
            'svg' => '<path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953h-1.513c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>',
        ],
        [
            'key' => 'threads',
            'label' => 'Threads',
            'href' => 'https://www.threads.net/intent/post?text='.rawurlencode($shareText.' '.$shareUrl),
            'color' => 'hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900',
            'svg' => '<path d="M16.2 7.2c-.7-.1-1.2-.3-1.8-.6-.2-.1-.4 0-.5.2-.8 1.4-1.4 3-1.6 4.7 1.2-.4 2.3-1 3.2-1.9.2-.2.2-.5 0-.6-1-.7-2.1-1.1-3.3-1.3.4-1.5 1.1-2.9 2-4.1.1-.2.1-.4-.1-.5-.5-.3-1.1-.5-1.7-.6-.2 0-.3.1-.4.2-1 1.4-1.8 3-2.2 4.7-.5 0-1-.1-1.5-.1H6.8c-.2 0-.4.2-.4.4v.6c0 2.2.6 4.3 1.7 6.1.1.2.3.2.5.1 1.1-.7 2.1-1.6 2.9-2.6.1 1.6.5 3.1 1.2 4.5.1.2.3.3.5.2 1.5-.6 2.8-1.6 3.8-2.8.1-.2.1-.4-.1-.5-1.1-.9-2.1-1.9-2.8-3.1.9.2 1.8.3 2.7.3h.3c.2 0 .4-.2.4-.4v-.6c0-.1 0-.2-.1-.3-1.2-.4-2.4-1.1-3.4-2z"/>',
        ],
        [
            'key' => 'bluesky',
            'label' => 'Bluesky',
            'href' => 'https://bsky.app/intent/compose?text='.rawurlencode($shareText.' '.$shareUrl),
            'color' => 'hover:bg-[#1185FE] hover:text-white',
            'svg' => '<path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.138.022-.276.04-.415.056-3.912.58-7.397 2.007-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.423 9.337 7.764 4.308 4.267-4.308 1.162-6.498-2.749-7.078a8.741 8.741 0 0 1-.415-.056c.14.017.279.036.415.056 2.67.297 5.568-.628 6.383-3.364.246-.828.624-5.79.624-6.478 0-.69-.139-1.861-.902-2.206-.659-.298-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8Z"/>',
        ],
        [
            'key' => 'linkedin',
            'label' => 'LinkedIn',
            'href' => 'https://www.linkedin.com/sharing/share-offsite/?url='.$u,
            'color' => 'hover:bg-[#0A66C2] hover:text-white',
            'svg' => '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>',
        ],
        [
            'key' => 'pinterest',
            'label' => 'Pinterest',
            'href' => 'https://pinterest.com/pin/create/button/?url='.$u.'&description='.$t.($img ? '&media='.$img : ''),
            'color' => 'hover:bg-[#E60023] hover:text-white',
            'svg' => '<path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.219-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 0 1 .083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.888-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12.001 24c6.624 0 11.999-5.373 11.999-12C24 5.372 18.627.001 12.001.001z"/>',
        ],
        [
            'key' => 'tumblr',
            'label' => 'Tumblr',
            'href' => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl='.$u.'&title='.$ti.'&caption='.$t,
            'color' => 'hover:bg-[#36465D] hover:text-white',
            'svg' => '<path d="M14.563 24c-5.093 0-7.031-3.756-7.031-6.411V9.747H5.116V6.573c3.51-1.275 4.374-4.48 4.566-6.573.013-.14.122 0 .122 0h3.515v6.152h4.566v3.595h-4.566v7.563c0 1.07.536 2.868 2.55 2.868h.199L20.5 24h-5.937z"/>',
        ],
        [
            'key' => 'vk',
            'label' => 'VK',
            'href' => 'https://vk.com/share.php?url='.$u.'&title='.$ti.'&comment='.$t,
            'color' => 'hover:bg-[#0077FF] hover:text-white',
            'svg' => '<path d="M15.684 0H8.316C1.253 0 0 1.253 0 8.316v7.368C0 22.747 1.253 24 8.316 24h7.368C22.747 24 24 22.747 24 15.684V8.316C24 1.253 22.747 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.862-.525-2.049-1.714-1.033-1.01-1.49-1.147-1.747-1.147-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.678.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.762-.491h1.744c.525 0 .643.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.049.17.475-.085.719-.576.719z"/>',
        ],
        [
            'key' => 'reddit',
            'label' => 'Reddit',
            'href' => 'https://www.reddit.com/submit?url='.$u.'&title='.$ti,
            'color' => 'hover:bg-[#FF4500] hover:text-white',
            'svg' => '<path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.094z"/>',
        ],
        [
            'key' => 'whatsapp',
            'label' => 'WhatsApp',
            'href' => 'https://api.whatsapp.com/send?text='.rawurlencode($shareText.' '.$shareUrl),
            'color' => 'hover:bg-[#25D366] hover:text-white',
            'svg' => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>',
        ],
        [
            'key' => 'telegram',
            'label' => 'Telegram',
            'href' => 'https://t.me/share/url?url='.$u.'&text='.$t,
            'color' => 'hover:bg-[#26A5E4] hover:text-white',
            'svg' => '<path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.788.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>',
        ],
        [
            'key' => 'email',
            'label' => 'Email',
            'href' => 'mailto:?subject='.$ti.'&body='.rawurlencode($shareText."\n\n".$shareUrl),
            'color' => 'hover:bg-brand-600 hover:text-white',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" fill="none"/>',
            'stroke' => true,
        ],
    ];
@endphp

<div
    class="share-buttons {{ $isCompact ? '' : 'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm' }}"
    x-data="{
        copied: false,
        url: @js($shareUrl),
        title: @js($shareTitle),
        text: @js($shareText),
        canNative: typeof navigator !== 'undefined' && !!navigator.share,
        async nativeShare() {
            try {
                await navigator.share({ title: this.title, text: this.text, url: this.url });
            } catch (e) {}
        },
        async copyLink() {
            try {
                await navigator.clipboard.writeText(this.url);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = this.url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            }
        },
        printPage() { window.print(); }
    }"
    role="group"
    aria-label="Share this page"
>
    @unless($isCompact)
        <div class="flex items-center justify-between gap-2 mb-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Share</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[60%] hidden sm:block" title="{{ $shareText }}">{{ \Illuminate\Support\Str::limit($shareText, 48) }}</p>
        </div>
    @endunless

    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
        <button type="button" x-show="canNative" x-cloak @click="nativeShare()"
            class="inline-flex items-center justify-center {{ $isCompact ? 'h-9 w-9' : 'h-10 w-10' }} rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-brand-600 hover:text-white hover:border-brand-600 transition"
            title="Share" aria-label="Share via device">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
        </button>

        @foreach($networks as $net)
            <a href="{{ $net['href'] }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center {{ $isCompact ? 'h-9 w-9' : 'h-10 w-10' }} rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 {{ $net['color'] }} transition"
                title="Share on {{ $net['label'] }}"
                aria-label="Share on {{ $net['label'] }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" @if(!empty($net['stroke'])) fill="none" stroke="currentColor" @else fill="currentColor" @endif aria-hidden="true">{!! $net['svg'] !!}</svg>
            </a>
        @endforeach

        <button type="button" @click="copyLink()"
            class="inline-flex items-center justify-center gap-1.5 {{ $isCompact ? 'h-9 px-2.5' : 'h-10 px-3' }} rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition text-xs font-semibold"
            title="Copy link" aria-label="Copy link">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
        </button>

        <button type="button" @click="printPage()"
            class="inline-flex items-center justify-center {{ $isCompact ? 'h-9 w-9' : 'h-10 w-10' }} rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-700 hover:text-white transition"
            title="Print" aria-label="Print this page">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        </button>
    </div>
</div>
