@component('layouts.app', [
    'title' => $title,
    'description' => $description,
    'robots' => $robots ?? null,
])
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $page->title }}</h1>
    @if($page->excerpt)
        <p class="mt-4 text-lg text-slate-600 dark:text-slate-400 leading-relaxed">{{ $page->excerpt }}</p>
    @endif

    <article class="cms-content prose prose-slate dark:prose-invert max-w-none mt-10
        prose-headings:font-bold prose-a:text-brand-600 dark:prose-a:text-brand-300
        prose-img:rounded-xl prose-table:text-sm">
        {!! $page->body_html !!}
    </article>

    <p class="mt-12 text-sm text-slate-500">
        <a href="{{ route('home') }}" class="hover:underline">Home</a>
        ·
        <a href="{{ route('contact') }}" class="hover:underline">Contact</a>
    </p>
</div>
@endcomponent
