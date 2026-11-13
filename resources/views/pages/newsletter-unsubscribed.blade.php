@component('layouts.app', ['title' => 'Newsletter — Alternova', 'robots' => 'noindex,nofollow'])
<div class="max-w-lg mx-auto px-4 py-16 text-center">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
        @if($ok)
            Unsubscribed
        @elseif(!empty($confirm))
            Confirm unsubscribe
        @else
            Something went wrong
        @endif
    </h1>
    <p class="mt-3 text-slate-600 dark:text-slate-400">{{ $message }}</p>
    @if(!empty($email))
        <p class="mt-1 text-sm text-slate-400">{{ $email }}</p>
    @endif

    @if(!empty($confirm) && !empty($token))
        <form method="POST" action="{{ route('newsletter.unsubscribe.confirm') }}" class="mt-8">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <button type="submit" class="inline-flex rounded-xl bg-rose-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-rose-700">
                Confirm unsubscribe
            </button>
        </form>
    @endif

    <a href="{{ route('home') }}" class="mt-8 inline-flex rounded-xl bg-brand-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-brand-700">Home</a>
</div>
@endcomponent
