@php $site = config('app.name', 'Alternova'); @endphp
@component('layouts.app', ['title' => $title, 'description' => $description])
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Privacy Policy</h1>
    <p class="mt-2 text-sm text-slate-500">Last updated: {{ date('F j, Y') }}</p>

    <div class="mt-8 space-y-8 text-slate-600 leading-relaxed">
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">1. Who we are</h2>
            <p>
                {{ $site }} ("we", "us") operates this website to provide open-source alternative listings and domain name ideation tools.
            </p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">2. Information we collect</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li><strong>Usage data</strong> — pages visited, approximate location from IP, browser type (via server logs or analytics if enabled).</li>
                <li><strong>Domain search logs</strong> — keywords and TLDs you submit in the Domain Combinator (may be stored to improve the product).</li>
                <li><strong>Account data</strong> — if you create an admin account: name, email, and password hash.</li>
                <li><strong>Cookies</strong> — session cookies required for login and security (CSRF).</li>
            </ul>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">3. How we use information</h2>
            <p>We use data to operate and improve the site, prevent abuse, and (if you opt in) send product updates. We do not sell personal information.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">4. Third parties</h2>
            <p>Analytics, advertising, or email providers may process data under their own policies when those features are enabled by the site operator.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">5. Contact</h2>
            <p>Questions about privacy: use the <a href="{{ route('contact') }}" class="text-brand-600 font-medium hover:underline">contact form</a>.</p>
        </section>
    </div>
</div>
@endcomponent
