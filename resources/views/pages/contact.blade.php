@component('layouts.app', ['title' => $title, 'description' => $description])
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Contact</h1>
    <p class="mt-4 text-lg text-slate-600 leading-relaxed">
        For corrections, listing requests, or general feedback, email us. We read every message but may not reply to every one.
    </p>

    <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900">Email</h2>
        <p class="mt-2 text-slate-600">
            Replace this with your real address in the template or admin settings:
        </p>
        <p class="mt-4">
            <a href="mailto:hello@{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com' }}"
                class="text-brand-600 font-semibold text-lg hover:underline">
                hello@{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com' }}
            </a>
        </p>
        <p class="mt-6 text-sm text-slate-500">
            Please include the tool or page URL if you are reporting an error.
        </p>
    </div>

    <div class="mt-8 text-sm text-slate-500">
        <a href="{{ route('privacy') }}" class="hover:text-slate-800 underline">Privacy Policy</a>
        ·
        <a href="{{ route('terms') }}" class="hover:text-slate-800 underline">Terms of Use</a>
    </div>
</div>
@endcomponent
