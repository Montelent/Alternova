<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4 mb-8">
        @foreach([
            ['Votes total', $stats['votes_total'] ?? 0],
            ['Votes 7d', $stats['votes_7d'] ?? 0],
            ['Votes 30d', $stats['votes_30d'] ?? 0],
            ['Comments', $stats['comments_total'] ?? 0],
            ['Comments pending', $stats['comments_pending'] ?? 0],
            ['Pros/cons total', $stats['pros_cons_total'] ?? 0],
            ['Pros/cons pending', $stats['pros_cons_pending'] ?? 0],
            ['Unread notifs', $stats['notifications_unread'] ?? 0],
            ['Domain searches 7d', $stats['domain_searches_7d'] ?? 0],
            ['Approved pros', $stats['pros_approved'] ?? 0],
            ['Approved cons', $stats['cons_approved'] ?? 0],
        ] as [$label, $value])
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</div>
                <div class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($value) }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-950 dark:text-white">Top voted alternatives</h2>
            @if(empty($topVoted))
                <p class="text-sm text-gray-500">No votes yet.</p>
            @else
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($topVoted as $row)
                        <li class="py-2.5 flex items-center justify-between gap-3 text-sm">
                            <a href="{{ $row['url'] }}" target="_blank" class="font-medium text-primary-600 hover:underline">{{ $row['name'] }}</a>
                            <span class="text-gray-500 shrink-0">▲ {{ $row['votes'] }} · health {{ number_format($row['health'], 1) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-950 dark:text-white">Recent votes</h2>
            @if(empty($recentVotes))
                <p class="text-sm text-gray-500">No recent votes.</p>
            @else
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($recentVotes as $row)
                        <li class="py-2.5 flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $row['alt'] }}</span>
                            <span class="text-gray-500 shrink-0">{{ $row['when'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold mb-4 text-gray-950 dark:text-white">Pros & cons breakdown</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="rounded-lg bg-emerald-50 dark:bg-emerald-950/30 p-4">
                    <div class="text-sm text-emerald-700 dark:text-emerald-300 font-semibold">Pros</div>
                    <div class="text-2xl font-bold text-emerald-900 dark:text-emerald-100 mt-1">{{ number_format($prosConsBreakdown['pro']['count'] ?? 0) }}</div>
                    <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($prosConsBreakdown['pro']['votes'] ?? 0) }} community upvotes</div>
                </div>
                <div class="rounded-lg bg-rose-50 dark:bg-rose-950/30 p-4">
                    <div class="text-sm text-rose-700 dark:text-rose-300 font-semibold">Cons</div>
                    <div class="text-2xl font-bold text-rose-900 dark:text-rose-100 mt-1">{{ number_format($prosConsBreakdown['con']['count'] ?? 0) }}</div>
                    <div class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ number_format($prosConsBreakdown['con']['votes'] ?? 0) }} community upvotes</div>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-500">
                Run <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">php artisan alternova:send-notification-digest</code> to email unread inboxes (scheduled Mondays 09:30).
            </p>
        </div>
    </div>
</x-filament-panels::page>
