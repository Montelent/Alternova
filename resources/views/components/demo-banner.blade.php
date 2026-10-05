@if(\App\Support\DemoMode::enabled())
    <div class="w-full border-b border-amber-300 bg-amber-50 px-4 py-2 text-center text-sm text-amber-900">
        <strong>Demo mode</strong> — {{ \App\Support\DemoMode::message() }}
    </div>
@endif
