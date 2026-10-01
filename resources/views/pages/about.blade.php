@php $site = config('app.name', 'Alternova'); @endphp
@component('layouts.app', ['title' => $title, 'description' => $description])
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">About {{ $site }}</h1>
    <p class="mt-4 text-lg text-slate-600 dark:text-slate-300 leading-relaxed">
        {{ $site }} helps you discover high-quality <strong>open-source alternatives</strong> to proprietary software
        and generate <strong>brandable domain name ideas</strong> with availability checks.
    </p>

    <div class="mt-10 space-y-8 text-slate-600 dark:text-slate-300 leading-relaxed">
        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">What we do</h2>
            <p>
                We curate self-hostable projects with licenses, difficulty ratings, health signals, and practical
                deploy guidance so you can move away from vendor lock-in with confidence.
            </p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Our tools</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li><a href="{{ route('finder') }}" class="text-brand-600 font-medium hover:underline">Open Source Finder</a> — compare alternatives to popular proprietary products.</li>
                <li><a href="{{ route('domains') }}" class="text-brand-600 font-medium hover:underline">Domain Combinator</a> — invent brandable names and check availability.</li>
            </ul>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Editorial standards</h2>
            <p>
                Listings are reviewed before publishing. Metrics may be synced from public GitHub data.
                Descriptions should be verified by editors before relying on them for production decisions.
            </p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Contact</h2>
            <p>
                Questions or corrections?
                <a href="{{ route('contact') }}" class="text-brand-600 font-medium hover:underline">Get in touch</a>.
            </p>
        </section>
    </div>
</div>
@endcomponent
