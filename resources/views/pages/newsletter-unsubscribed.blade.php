@component('layouts.app', ['title' => 'Newsletter — Alternova', 'robots' => 'noindex,follow'])
<div class="max-w-lg mx-auto px-4 py-16 text-center">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
        {{ $ok ? 'Unsubscribed' : 'Something went wrong' }}
    </h1>
    <p class="mt-3 text-slate-600 dark:text-slate-400">{{ $message }}</p>
    @if(!empty($email))
        <p class="mt-1 text-sm text-slate-400">{{ $email }}</p>
    @endif
    <a href="{{ route('home') }}" class="mt-8 inline-flex rounded-xl bg-brand-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-brand-700">Home</a>
</div>
@endcomponent
