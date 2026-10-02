<footer class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 py-10 mt-auto">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-10">
            <div class="max-w-sm">
                <div class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-white text-xs">A</span>
                    Alternova
                </div>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ \App\Models\SiteSetting::get('site_tagline', 'Open-source alternatives and brandable domain ideas.') }}
                </p>
                <div class="mt-5">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white mb-2">Get product updates</p>
                    @livewire('newsletter-subscribe', ['source' => 'footer'])
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-8 text-sm">
                <div>
                    <p class="font-semibold text-slate-900 dark:text-white mb-3">Product</p>
                    <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                        <li><a href="{{ route('finder') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Alternatives</a></li>
                        <li><a href="{{ route('browse.type', 'categories') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Browse by category</a></li>
                        <li><a href="{{ route('browse.type', 'languages') }}" class="hover:text-slate-800 dark:hover:text-slate-200">By language</a></li>
                        <li><a href="{{ route('browse.type', 'licenses') }}" class="hover:text-slate-800 dark:hover:text-slate-200">By license</a></li>
                        <li><a href="{{ route('collections.index') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Collections</a></li>
                        <li><a href="{{ route('trending') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Trending</a></li>
                        <li><a href="{{ route('domains') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Domains</a></li>
                        <li><a href="{{ route('api.docs') }}" class="hover:text-slate-800 dark:hover:text-slate-200">API docs</a></li>
                        <li><a href="{{ route('suggest') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Suggest tool</a></li>
                    </ul>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 dark:text-white mb-3">Account</p>
                    <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                        @auth
                            <li><a href="{{ route('account') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Account</a></li>
                            <li><a href="{{ route('favorites') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Favorites</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Sign in</a></li>
                            <li><a href="{{ route('register') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Register</a></li>
                        @endauth
                        <li><a href="{{ route('contact') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 dark:text-white mb-3">Legal</p>
                    <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                        @isset($footerPages)
                            @foreach($footerPages as $fp)
                                <li><a href="{{ $fp->publicUrl() }}" class="hover:text-slate-800 dark:hover:text-slate-200">{{ $fp->title }}</a></li>
                            @endforeach
                        @endisset
                        <li><a href="{{ route('privacy') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Privacy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Terms</a></li>
                        <li><a href="{{ route('disclosure') }}" class="hover:text-slate-800 dark:hover:text-slate-200">Disclosure</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <p class="mt-10 text-xs text-slate-400">&copy; {{ date('Y') }} {{ config('app.name', 'Alternova') }}</p>
    </div>
</footer>
