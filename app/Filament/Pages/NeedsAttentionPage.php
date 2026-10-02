<?php

namespace App\Filament\Pages;

use App\Filament\Resources\AlternativeSubmissionResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\IssueReportResource;
use App\Filament\Resources\OpenSourceAlternativeResource;
use App\Jobs\SyncGitHubMetricsJob;
use App\Models\AlternativeSubmission;
use App\Models\ContactMessage;
use App\Models\IssueReport;
use App\Models\OpenSourceAlternative;
use App\Services\DuplicateAlternativeService;
use App\Services\LinkHealthService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;

class NeedsAttentionPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Needs attention';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.needs-attention';

    protected static ?string $title = 'Needs attention';

    public string $lastOutput = '';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function getNavigationBadge(): ?string
    {
        $n = static::totalCount();

        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::totalCount() > 0 ? 'warning' : 'gray';
    }

    public static function totalCount(): int
    {
        try {
            return static::draftsCount()
                + static::brokenLinksCount()
                + static::zeroHealthCount()
                + static::pendingSubmissionsCount()
                + static::unreadContactsCount()
                + static::openIssuesCount()
                + static::duplicateSuspectsCount();
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function draftsCount(): int
    {
        return OpenSourceAlternative::query()
            ->where('is_published', false)
            ->count();
    }

    public static function brokenLinksCount(): int
    {
        if (! Schema::hasColumn('open_source_alternatives', 'repo_reachable')) {
            return 0;
        }

        // Only count explicit failures (false), not "not checked yet" (null)
        return OpenSourceAlternative::query()
            ->where(function ($q) {
                $q->where('repo_reachable', false)
                    ->orWhere('website_reachable', false);
            })
            ->count();
    }

    public static function zeroHealthCount(): int
    {
        return OpenSourceAlternative::query()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('overall_health_score')->orWhere('overall_health_score', '<=', 0);
            })
            ->whereNotNull('repo_url')
            ->where('repo_url', '!=', '')
            ->count();
    }

    public static function pendingSubmissionsCount(): int
    {
        try {
            if (! Schema::hasTable('alternative_submissions')) {
                return 0;
            }

            return AlternativeSubmission::query()->where('status', 'pending')->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function unreadContactsCount(): int
    {
        try {
            if (! Schema::hasTable('contact_messages')) {
                return 0;
            }

            $q = ContactMessage::query();

            if (Schema::hasColumn('contact_messages', 'read_at')) {
                $q->whereNull('read_at');
            }

            if (Schema::hasColumn('contact_messages', 'status')) {
                $q->where(function ($inner) {
                    $inner->whereNull('status')
                        ->orWhereIn('status', ['new', 'unread', 'open', 'pending']);
                });
            }

            return $q->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function openIssuesCount(): int
    {
        try {
            if (! Schema::hasTable('issue_reports')) {
                return 0;
            }

            return IssueReport::query()
                ->where(function ($q) {
                    $q->whereIn('status', ['open', 'pending', 'new'])
                        ->orWhereNull('status');
                })
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Number of alternatives involved in duplicate groups/pairs (not the array key count).
     */
    public static function duplicateSuspectsCount(): int
    {
        try {
            $counts = app(DuplicateAlternativeService::class)->summaryCounts();

            return (int) array_sum($counts);
        } catch (\Throwable) {
            return 0;
        }
    }

    public function summary(): array
    {
        return [
            [
                'label' => 'Draft alternatives',
                'count' => self::draftsCount(),
                'hint' => 'Not visible on the public site until published.',
                'href' => OpenSourceAlternativeResource::getUrl('index'),
                'cta' => 'Review drafts',
            ],
            [
                'label' => 'Broken links',
                'count' => self::brokenLinksCount(),
                'hint' => 'Repo or website URL failed the last health check.',
                'href' => LinkHealthPage::getUrl(),
                'cta' => 'Link health',
            ],
            [
                'label' => 'Health score is 0',
                'count' => self::zeroHealthCount(),
                'hint' => 'Published items that still need a GitHub metrics sync.',
                'href' => OpenSourceAlternativeResource::getUrl('index'),
                'cta' => 'Open alternatives',
            ],
            [
                'label' => 'Pending submissions',
                'count' => self::pendingSubmissionsCount(),
                'hint' => 'Community suggestions waiting for approval.',
                'href' => AlternativeSubmissionResource::getUrl('index'),
                'cta' => 'Review submissions',
            ],
            [
                'label' => 'Unread contact messages',
                'count' => self::unreadContactsCount(),
                'hint' => 'Messages that may still need a reply.',
                'href' => ContactMessageResource::getUrl('index'),
                'cta' => 'Inbox',
            ],
            [
                'label' => 'Open issue reports',
                'count' => self::openIssuesCount(),
                'hint' => 'User-reported problems on listings.',
                'href' => IssueReportResource::getUrl('index'),
                'cta' => 'Issue reports',
            ],
            [
                'label' => 'Possible duplicates',
                'count' => self::duplicateSuspectsCount(),
                'hint' => 'Same repo URL or very similar names — review before publishing more.',
                'href' => DuplicateAlternativesPage::getUrl(),
                'cta' => 'Duplicate detector',
            ],
        ];
    }

    public function drafts()
    {
        return OpenSourceAlternative::query()
            ->with('proprietaryTool')
            ->where('is_published', false)
            ->orderByDesc('updated_at')
            ->limit(15)
            ->get();
    }

    public function broken()
    {
        if (! Schema::hasColumn('open_source_alternatives', 'repo_reachable')) {
            return collect();
        }

        return OpenSourceAlternative::query()
            ->where(function ($q) {
                $q->where('repo_reachable', false)->orWhere('website_reachable', false);
            })
            ->orderByDesc('links_checked_at')
            ->limit(15)
            ->get();
    }

    public function zeroHealth()
    {
        return OpenSourceAlternative::query()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('overall_health_score')->orWhere('overall_health_score', '<=', 0);
            })
            ->whereNotNull('repo_url')
            ->where('repo_url', '!=', '')
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    public function publishDraft(int $id): void
    {
        $alt = OpenSourceAlternative::query()->find($id);
        if (! $alt) {
            return;
        }
        $alt->update(['is_published' => true]);
        Notification::make()->title('Published')->body($alt->name)->success()->send();
    }

    public function syncOne(int $id): void
    {
        $alt = OpenSourceAlternative::query()->find($id);
        if (! $alt) {
            return;
        }
        try {
            SyncGitHubMetricsJob::dispatchSync($alt);
            $this->lastOutput = $alt->name.': health '.$alt->fresh()->overall_health_score;
            Notification::make()->title('Metrics synced')->body($this->lastOutput)->success()->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('Sync failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function recheckOne(int $id): void
    {
        $alt = OpenSourceAlternative::query()->find($id);
        if (! $alt) {
            return;
        }
        try {
            app(LinkHealthService::class)->checkAlternative($alt);
            Notification::make()->title('Links rechecked')->body($alt->name)->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Recheck failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function syncAllZeroHealth(): void
    {
        $items = $this->zeroHealth();
        $ok = 0;
        $fail = 0;
        foreach ($items as $alt) {
            try {
                SyncGitHubMetricsJob::dispatchSync($alt);
                $ok++;
            } catch (\Throwable) {
                $fail++;
            }
        }
        $this->lastOutput = "Synced {$ok}, failed {$fail}.";
        Notification::make()->title('Batch metrics sync')->body($this->lastOutput)->success()->send();
    }
}
