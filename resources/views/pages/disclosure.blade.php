@component('layouts.app', ['title' => $title, 'description' => $description])
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Affiliate &amp; advertising disclosure</h1>

    <div class="mt-8 space-y-8 text-slate-600 leading-relaxed">
        <p>
            Alternova is free to use. To keep the project running we may earn commissions or advertising revenue as described below.
            This does not change our editorial process for listing open-source projects.
        </p>

        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">Domain registrar links</h2>
            <p>
                When you click through to register a domain name (for example via Namecheap, Porkbun, or GoDaddy),
                we may use affiliate or referral links. If you complete a purchase, we may receive a commission at no extra cost to you.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">Advertising</h2>
            <p>
                We may display ads (including Google AdSense) on some pages. Ad partners may use cookies or similar technologies
                as described in our <a href="{{ route('privacy') }}" class="text-brand-600 font-medium hover:underline">Privacy Policy</a>.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">Open-source listings</h2>
            <p>
                Project listings, health scores, and descriptions are informational. We do not sell placement in organic results.
                Featured badges, when used, are labeled. Always verify licenses, security, and suitability yourself before deploying software.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 mb-2">Questions</h2>
            <p>
                Contact us via the <a href="{{ route('contact') }}" class="text-brand-600 font-medium hover:underline">contact form</a>.
            </p>
        </section>

        <p class="text-sm text-slate-400">Last updated: {{ date('F j, Y') }}</p>
    </div>
</div>
@endcomponent
