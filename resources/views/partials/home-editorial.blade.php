@php
    use App\Models\SiteSetting;
    $d = \App\Filament\Pages\HomepageContentPage::defaults();
    $g = fn (string $k) => SiteSetting::get($k, $d[$k] ?? '');
    $enabled = SiteSetting::getBool('home_editorial_enabled', true);
    $body = (string) $g('home_editorial_body');
    $paragraphs = preg_split('/\n\s*\n/', trim($body)) ?: [];
    $faqRaw = (string) $g('home_faq_json');
    $faqs = json_decode($faqRaw, true);
    if (! is_array($faqs)) {
        $faqs = json_decode($d['home_faq_json'] ?? '[]', true) ?: [];
    }
    $checklist = array_values(array_filter(array_map('trim', explode("\n", (string) $g('home_checklist_items')))));
@endphp
@if($enabled)
<section class="py-16 sm:py-20 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950" id="guides">
    <div class="mx-auto max-w-3xl px-4 sm:px-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400 mb-3">{{ $g('home_editorial_eyebrow') }}</p>
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
            {{ $g('home_editorial_title') }}
        </h2>
        <p class="mt-4 text-base sm:text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
            {{ $g('home_editorial_intro') }}
        </p>

        <div class="mt-10 space-y-6 text-[15px] sm:text-base text-slate-700 dark:text-slate-300 leading-relaxed">
            @foreach($paragraphs as $block)
                @php $block = trim($block); @endphp
                @if($block === '')
                    @continue
                @endif
                @php
                    $lines = preg_split('/\n/', $block) ?: [$block];
                    $first = trim($lines[0] ?? '');
                    $isHeading = count($lines) === 1 && mb_strlen($first) <= 80 && ! str_ends_with($first, '.');
                @endphp
                @if($isHeading)
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $first }}</h3>
                @else
                    <p>{!! nl2br(e($block)) !!}</p>
                @endif
            @endforeach
        </div>

        @if(count($faqs))
            <div class="mt-14" id="faq">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Frequently asked questions</h3>
                <div class="space-y-4" x-data="{ open: 0 }">
                    @foreach($faqs as $i => $faq)
                        @continue(empty($faq['q']))
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                            <button type="button"
                                class="w-full flex items-center justify-between gap-3 px-4 py-3.5 text-left text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-900/80"
                                @click="open = open === {{ $i }} ? null : {{ $i }}">
                                <span>{{ $faq['q'] }}</span>
                                <span class="text-slate-400 text-lg leading-none" x-text="open === {{ $i }} ? '−' : '+'"></span>
                            </button>
                            <div x-show="open === {{ $i }}" x-cloak class="px-4 pb-4 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                {{ $faq['a'] ?? '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(count($checklist))
            <div class="mt-12 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-6 sm:p-8">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $g('home_checklist_title') }}</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                    @foreach($checklist as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('finder') }}" class="inline-flex rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold px-4 py-2.5">{{ $g('home_cta_finder') }}</a>
                    <a href="{{ route('domains') }}" class="inline-flex rounded-xl border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-100 text-sm font-semibold px-4 py-2.5">{{ $g('home_cta_domains') }}</a>
                    <a href="{{ route('contact') }}" class="inline-flex rounded-xl border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-100 text-sm font-semibold px-4 py-2.5">Contact support</a>
                </div>
            </div>
        @endif
    </div>
</section>
@endif
