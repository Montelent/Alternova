{{-- Long-form editorial for AdSense / SEO. Written for humans, not keyword stuffing. --}}
<section class="py-16 sm:py-20 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950" id="guides">
    <div class="mx-auto max-w-3xl px-4 sm:px-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400 mb-3">Field guide</p>
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
            How teams actually choose open-source alternatives
        </h2>
        <p class="mt-4 text-base sm:text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
            Software budgets used to mean picking a logo from a vendor shortlist and signing a multi-year order form.
            That model still works for many companies. It is no longer the only model that serious operators consider.
            {{ config('app.name', 'Alternova') }} exists for the other conversation: when engineering, finance, and security
            want the option to run critical workflows on infrastructure they control, with code they can inspect.
        </p>

        <div class="mt-10 space-y-6 text-[15px] sm:text-base text-slate-700 dark:text-slate-300 leading-relaxed">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">What this site is for</h3>
            <p>
                The <a href="{{ route('finder') }}" class="text-brand-600 dark:text-brand-400 font-medium hover:underline">Open Source Alternative Finder</a>
                is a curated directory, not a random scrape of GitHub. Each listing ties a proprietary product people already know
                to open-source projects that aim at a similar job to be done. You can filter by category, language, and license,
                then open a profile that surfaces repository signals, self-host difficulty, and practical links.
            </p>
            <p>
                The <a href="{{ route('domains') }}" class="text-brand-600 dark:text-brand-400 font-medium hover:underline">Domain Name Idea Combinator</a>
                is a separate utility for founders and product teams who need brandable names while they evaluate tools.
                It generates combinations from seed keywords, scores them for brandability, and checks availability so you are not
                juggling five registrar tabs during a naming workshop.
            </p>

            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Why open source enters the shortlist</h3>
            <p>
                Open source is not automatically cheaper. It moves cost from subscription lines into people, monitoring, and upgrade discipline.
                Teams still pursue it when they need data residency, deep customization, protection from sudden price changes,
                or a credible exit path if a vendor is acquired. The winning question is rarely “Is there a free version?”
                It is “Can we operate this safely for three years?”
            </p>
            <p>
                A healthy evaluation weighs license fit, authentication options, backup and restore, release cadence, and whether
                your staff already knows the primary language of the project. Stars on GitHub are a weak proxy for production readiness.
                Clear docs, responsive security contacts, and boring upgrade notes are stronger ones.
            </p>

            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">How to use the Alternative Finder</h3>
            <ol class="list-decimal pl-5 space-y-2">
                <li>Start from a product you already use, or browse <a href="{{ route('browse.type', 'categories') }}" class="text-brand-600 dark:text-brand-400 hover:underline">categories</a>.</li>
                <li>Open two or three candidate profiles. Note license, language, and self-host difficulty.</li>
                <li>Prefer projects with recent commits and readable install guides over pure popularity metrics.</li>
                <li>Run a time-boxed pilot on non-critical data. Write success criteria before the trial starts.</li>
                <li>Involve security and legal before production, even when the license looks permissive.</li>
            </ol>

            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">How to use the Domain Combinator</h3>
            <p>
                Add a few seed words that describe the product or audience. Adjust prefixes, suffixes, and TLDs, then generate.
                Availability checks run in batches so the page stays responsive on shared hosting. Export the names you like,
                and register only after trademark screening. A clever domain does not replace a clear product story.
            </p>

            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">What “health score” means here</h3>
            <p>
                Our health score is a composite aid for sorting, built from repository activity and related checks.
                It is not a certification, penetration test, or guarantee. Always verify upstream release notes,
                security advisories, and operational requirements yourself. Treat the score as a flashlight, not a verdict.
            </p>
        </div>

        {{-- FAQ --}}
        <div class="mt-14" id="faq">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Frequently asked questions</h3>
            <div class="space-y-4" x-data="{ open: 0 }">
                @php
                    $faqs = [
                        [
                            'q' => 'Is every listing fully free for commercial use?',
                            'a' => 'No. Open-source licenses differ. Some projects use permissive licenses; others use copyleft or offer a commercial edition. Read the license on the upstream repository and confirm it matches how you deploy software.',
                        ],
                        [
                            'q' => 'Can I suggest a missing alternative?',
                            'a' => 'Yes. Use the Suggest a tool flow on the site. Editors review submissions for basic quality before publishing so the directory stays useful rather than noisy.',
                        ],
                        [
                            'q' => 'Do you host the software for me?',
                            'a' => config('app.name', 'Alternova').' is a discovery and research layer. We do not operate your instances. Self-hosting, managed providers, or official cloud offers from the project are separate decisions.',
                        ],
                        [
                            'q' => 'How often are GitHub metrics updated?',
                            'a' => 'Metrics sync on a schedule when cron is configured in Admin, and editors can refresh individual projects manually. If a number looks stale, check the upstream repository for the latest activity.',
                        ],
                        [
                            'q' => 'Is the Domain Combinator a registrar?',
                            'a' => 'No. It helps you invent and screen names. Registration happens at registrars you choose. Affiliate links, when enabled, may support the site at no extra cost to you.',
                        ],
                        [
                            'q' => 'How should regulated industries approach open source?',
                            'a' => 'Start with a written risk assessment: data classification, access control, logging, backup, vendor (or maintainer) dependency, and exit plan. Open source can improve transparency; it does not remove compliance obligations.',
                        ],
                    ];
                @endphp
                @foreach($faqs as $i => $faq)
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <button type="button"
                            class="w-full flex items-center justify-between gap-3 px-4 py-3.5 text-left text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-900/80"
                            @click="open = open === {{ $i }} ? null : {{ $i }}"
                            :aria-expanded="open === {{ $i }}">
                            <span>{{ $faq['q'] }}</span>
                            <span class="text-slate-400 text-lg leading-none" x-text="open === {{ $i }} ? '−' : '+'"></span>
                        </button>
                        <div x-show="open === {{ $i }}" x-cloak class="px-4 pb-4 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-12 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-6 sm:p-8">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">A simple decision checklist</h3>
            <ul class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                <li>Problem and success metrics written down before demos begin</li>
                <li>License reviewed by someone accountable for compliance</li>
                <li>Pilot environment isolated from production secrets</li>
                <li>Backup and restore tested at least once</li>
                <li>Owner named for upgrades and security patches</li>
                <li>Exit plan if the project slows down or changes license</li>
            </ul>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('finder') }}" class="inline-flex rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold px-4 py-2.5">Browse alternatives</a>
                <a href="{{ route('domains') }}" class="inline-flex rounded-xl border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-100 text-sm font-semibold px-4 py-2.5">Try domain ideas</a>
                <a href="{{ route('contact') }}" class="inline-flex rounded-xl border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-100 text-sm font-semibold px-4 py-2.5">Contact support</a>
            </div>
        </div>
    </div>
</section>
