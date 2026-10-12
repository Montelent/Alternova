@php $site = config('app.name', 'Alternova'); @endphp
@component('layouts.app', ['title' => $title, 'description' => $description])
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Terms of Use</h1>
    <p class="mt-2 text-sm text-slate-500">Last updated: {{ date('F j, Y') }}</p>

    <div class="mt-8 space-y-8 text-slate-600 leading-relaxed">
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">1. Acceptance</h2>
            <p>By using {{ $site }} you agree to these terms. If you do not agree, do not use the site.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">2. Service description</h2>
            <p>
                {{ $site }} provides informational listings of software projects and domain name ideation tools.
                We do not host the third-party software listed, and we are not affiliated with every project mentioned unless stated.
            </p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">3. No warranty</h2>
            <p>Content is provided as-is without warranty. Evaluate open-source projects yourself before deploying them.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">4. User content</h2>
            <p>Comments, submissions, and votes must be lawful and respectful. We may moderate or remove content.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">5. Trademarks</h2>
            <p>{{ $site }} branding and original site content belong to us. Third-party names and logos belong to their owners.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">6. Limitation of liability</h2>
            <p>We are not liable for damages arising from use of third-party software or domain registrations.</p>
        </section>
    </div>
</div>
@endcomponent
