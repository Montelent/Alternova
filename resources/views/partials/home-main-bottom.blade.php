@php
    use App\Models\SiteSetting;
    $d = \App\Filament\Pages\HomepageContentPage::defaults();
    $g = fn (string $k) => SiteSetting::get($k, $d[$k] ?? '');
@endphp
@if(isset($featured) && $featured->isNotEmpty())
<section class="py-12 border-b border-slate-200 dark:border-slate-800">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex items-end justify-between gap-4 mb-8">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $g('home_section_featured') }}</h2>
            <a href="{{ route('finder') }}" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">View all</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($featured as $alt)
                @include('partials.home-alt-card', ['alt' => $alt])
            @endforeach
        </div>
    </div>
</section>
@endif

@if(isset($recent) && $recent->isNotEmpty())
<section class="py-12 border-b border-slate-200 dark:border-slate-800">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex items-end justify-between gap-4 mb-8">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $g('home_section_recent') }}</h2>
            <a href="{{ route('finder', ['sort' => 'newest']) }}" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">Newest</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($recent as $alt)
                @include('partials.home-alt-card', ['alt' => $alt])
            @endforeach
        </div>
    </div>
</section>
@endif

@if(isset($tools) && $tools->isNotEmpty())
<section class="py-12 border-b border-slate-200 dark:border-slate-800">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ $g('home_section_products') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">{{ $g('home_section_products_sub') }}</p>
        <div class="flex flex-wrap gap-3">
            @foreach($tools as $tool)
                <a href="{{ route('alternativesto.show', $tool->slug) }}"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:border-brand-400 hover:text-brand-600 transition">
                    @if($tool->logo_url)
                        <img src="{{ $tool->logo_url }}" alt="" class="h-5 w-5 rounded object-contain" loading="lazy" width="20" height="20">
                    @endif
                    {{ $tool->name }}
                    <span class="text-xs text-slate-400">{{ $tool->alternatives_count }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-14">
    <div class="mx-auto max-w-6xl px-4 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
        <div>
            <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['alternatives'] ?? 0 }}</div>
            <div class="mt-1 text-xs sm:text-sm text-slate-500">{{ $g('home_stat_alternatives') }}</div>
        </div>
        <div>
            <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['tools'] ?? 0 }}</div>
            <div class="mt-1 text-xs sm:text-sm text-slate-500">{{ $g('home_stat_tools') }}</div>
        </div>
        <div>
            <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ $g('home_stat_domains_value') }}</div>
            <div class="mt-1 text-xs sm:text-sm text-slate-500">{{ $g('home_stat_domains') }}</div>
        </div>
        <div>
            <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ $g('home_stat_feeds_value') }}</div>
            <div class="mt-1 text-xs sm:text-sm text-slate-500">{{ $g('home_stat_feeds') }}</div>
        </div>
    </div>
</section>
