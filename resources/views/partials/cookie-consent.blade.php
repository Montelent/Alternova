<div x-data="cookieConsent()" x-init="init()" x-cloak>
    <div x-show="show" x-transition
        class="fixed bottom-0 inset-x-0 z-50 p-4 sm:p-6 pointer-events-none">
        <div class="mx-auto max-w-3xl pointer-events-auto rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <p class="text-sm text-slate-600 dark:text-slate-300 flex-1">
                We use cookies for essential site functions and, if enabled, advertising (e.g. Google AdSense).
                See our <a href="{{ route('privacy') }}" class="font-semibold text-brand-600 hover:underline">Privacy Policy</a>.
            </p>
            <div class="flex gap-2 shrink-0">
                <button type="button" @click="decline()"
                    class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                    Essential only
                </button>
                <button type="button" @click="accept()"
                    class="rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 text-sm font-semibold">
                    Accept
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function cookieConsent() {
    return {
        show: false,
        init() {
            try {
                if (!localStorage.getItem('alternova-cookies')) this.show = true;
            } catch (e) { this.show = true; }
        },
        accept() {
            localStorage.setItem('alternova-cookies', 'accepted');
            this.show = false;
            window.dispatchEvent(new CustomEvent('alternova-cookies-accepted'));
        },
        decline() {
            localStorage.setItem('alternova-cookies', 'essential');
            this.show = false;
        }
    }
}
</script>
