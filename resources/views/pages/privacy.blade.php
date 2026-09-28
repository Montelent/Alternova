@component('layouts.app', ['title' => $title, 'description' => $description])
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Privacy Policy</h1>
    <p class="mt-2 text-sm text-slate-500">Last updated: {{ date('F j, Y') }}</p>

    <div class="mt-8 space-y-8 text-slate-600 leading-relaxed">
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">1. Who we are</h2>
            <p>
                Alternova ("we", "us") operates this website to provide open-source alternative listings and domain name ideation tools.
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
            <p>We use data to operate the site, prevent abuse, improve listings, and (if you enable it) measure traffic.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">4. Advertising</h2>
            <p>
                We may display ads through Google AdSense or similar partners. These partners may use cookies or similar technologies
                to show relevant ads based on your visits to this and other sites. You can manage ad personalization via
                <a href="https://adssettings.google.com" class="text-brand-600 hover:underline" rel="noopener" target="_blank">Google Ads Settings</a>
                and learn more at
                <a href="https://policies.google.com/technologies/ads" class="text-brand-600 hover:underline" rel="noopener" target="_blank">Google Advertising Policies</a>.
            </p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">5. Third-party services</h2>
            <p>We may use hosting providers, email, analytics, and advertising networks. Their processing is governed by their own policies.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">6. Data retention</h2>
            <p>Logs and search records are kept only as long as needed for security and product improvement, unless a longer period is required by law.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">7. Your choices</h2>
            <p>You can clear cookies in your browser. For access or deletion requests related to an account you control, use the contact page.</p>
        </section>
        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">8. Contact</h2>
            <p>Privacy questions: <a href="{{ route('contact') }}" class="text-brand-600 hover:underline">Contact us</a>.</p>
        </section>
    </div>
</div>
@endcomponent
