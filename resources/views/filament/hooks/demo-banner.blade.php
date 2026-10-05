@if(\App\Support\DemoMode::enabled())
    <div class="fi-demo-banner sticky top-0 z-50 border-b border-amber-300 bg-amber-50 px-4 py-2 text-center text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-100">
        <strong>Demo mode</strong> — Explore every admin screen. Save / Create will not persist changes.
    </div>
@endif
