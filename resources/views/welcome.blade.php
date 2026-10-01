@php
    $seo = app(\App\Services\SeoManager::class);
    $siteName = $seo->siteName();
    $title = $seo->homepageTitle();
    $description = $seo->homepageDescription();
@endphp
@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-slate-200 dark:border-slate-800 bg-slate-950 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-brand-900/40 via-slate-950 to-slate-950"></div>
        <div class="relative mx-auto max-w-4xl px-4 pt-16 pb-16 sm:pt-24 sm:pb-20 text-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300 mb-6">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Open source · Self-hostable · Brandable
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                Find better tools.<br>
                <span class="text-brand-300">Name them well.</span>
            </h1>
            <p class="mt-5 text-base sm:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Discover high-quality open-source alternatives to proprietary software,
                and generate brandable domain ideas with live availability checks.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('finder') }}" class="inline-flex justify-center rounded-xl bg-brand-600 hover:bg-brand-500 px-6 py-3.5 text-sm font-semibold text-white transition">Browse alternatives</a>
                <a href="{{ route('domains') }}" class="inline-flex justify-center rounded-xl border border-white/15 hover:border-white/30 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white transition">Generate domains</a>
            </div>
        </div>
    </section>

    @if(empty($stats['alternatives']) && empty($stats['tools']))
    <section class="py-16">
        <div class="mx-auto max-w-2xl px-4 text-center">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Catalog is empty</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                No published alternatives yet. Add tools in the admin panel, or use
                <strong>System tools → Seed demo data</strong> for an optional starter set of real open-source projects.
            </p>
            <a href="{{ url('/admin') }}" class="mt-6 inline-flex rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-500">Open admin</a>
        </div>
    </section>
    @endif

    @if(isset($trending) && $trending->isNotEmpty())
    <section class="py-12 border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4 mb-8">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-1">This week</p>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Trending</h2>
                </div>
                <a href="{{ route('trending') }}" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline">Full board</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($trending as $i => $alt)
                    <a href="{{ route('alternatives.show', $alt) }}" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 hover:border-brand-300 dark:hover:border-brand-600 transition">
                        <div class="flex justify-between gap-2">
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $alt->name }}</h3>
                            <span class="text-xs font-bold text-amber-600">▲ {{ $alt->period_votes ?? 0 }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            @if($alt->proprietaryTool) vs {{ $alt->proprietaryTool->name }} · @endif
                            Health {{ number_format($alt->overall_health_score, 0) }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(isset($featured) && $featured->isNotEmpty())
    <section class="py-12 border-b border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4 mb-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Featured alternatives</h2>
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
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Recently added</h2>
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
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Browse by product</h2>
            <p class="text-sm text-slate-500 mb-6">Open-source options listed for each proprietary product.</p>
            <div class="flex flex-wrap gap-3">
                @foreach($tools as $tool)
                    <a href="{{ route('alternativesto.show', $tool->slug) }}"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:border-brand-400 hover:text-brand-600 transition">
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
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Published alternatives</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['tools'] ?? 0 }}</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Proprietary tools</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">DNS + RDAP</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Domain checks</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">API + RSS</div>
                <div class="mt-1 text-xs sm:text-sm text-slate-500">Open feeds</div>
            </div>
        </div>
    </section>
</div>
@endsection
