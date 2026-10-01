<?php

namespace App\Filament\Pages;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Models\SiteSetting;
use App\Support\CronSettings;
use App\Support\IntegrationsSettings;
use Filament\Pages\Page;

class GettingStartedPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationLabel = 'Getting started';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.getting-started';

    protected static ?string $title = 'Getting started';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    /**
     * @return list<array{id: string, title: string, body: string, done: bool, href: ?string, cta: ?string}>
     */
    public function steps(): array
    {
        $tools = 0;
        $alts = 0;
        $published = 0;
        try {
            $tools = ProprietaryTool::query()->where('is_published', true)->count();
            $alts = OpenSourceAlternative::query()->count();
            $published = OpenSourceAlternative::query()->where('is_published', true)->count();
        } catch (\Throwable) {
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        $urlLooksLocal = str_contains($appUrl, 'localhost')
            || str_contains($appUrl, '127.0.0.1')
            || $appUrl === '';

        $mailer = SiteSetting::get('mail_mailer', config('mail.default', 'log'));
        $mailConfigured = in_array($mailer, ['smtp', 'resend'], true);

        $cronRan = CronSettings::lastScheduleRun() !== null;
        $github = IntegrationsSettings::githubToken() !== null;

        $seoName = trim((string) SiteSetting::get('seo_site_name', ''));
        $seoDesc = trim((string) SiteSetting::get('default_meta_description', ''));
        $seoDone = ($seoName !== '' && $seoName !== 'Alternova') || $seoDesc !== '';

        return [
            [
                'id' => 'url',
                'title' => 'Confirm your site URL',
                'body' => 'APP_URL should match the public domain (https://your-domain.com). Wrong URL breaks emails, sitemaps, and social previews.',
                'done' => ! $urlLooksLocal,
                'href' => SiteSeoSettings::getUrl(),
                'cta' => 'Open SEO settings',
            ],
            [
                'id' => 'content',
                'title' => 'Add at least one proprietary tool and one alternative',
                'body' => 'Published catalog: '.$tools.' tool(s), '.$published.' published alternative(s)'
                    .($alts > $published ? ' ('.$alts.' total including drafts).' : '.'),
                'done' => $tools > 0 && $published > 0,
                'href' => \App\Filament\Resources\ProprietaryToolResource::getUrl('index'),
                'cta' => 'Manage tools',
            ],
            [
                'id' => 'github',
                'title' => 'Add a GitHub token (recommended)',
                'body' => 'Raises API rate limits so health scores and stars stay accurate. Create a token with public repository read access.',
                'done' => $github,
                'href' => IntegrationsPage::getUrl(),
                'cta' => 'Integrations',
            ],
            [
                'id' => 'cron',
                'title' => 'Turn on the server cron',
                'body' => 'Metrics sync, link checks, and digests only run when the host calls schedule:run. Copy the auto-detected command from Cron settings.',
                'done' => $cronRan,
                'href' => CronSettingsPage::getUrl(),
                'cta' => 'Cron settings',
            ],
            [
                'id' => 'mail',
                'title' => 'Configure outbound email',
                'body' => 'Use Resend or SMTP so contact forms, digests, and admin alerts actually send. Log driver is fine for testing only.',
                'done' => $mailConfigured,
                'href' => MailSettingsPage::getUrl(),
                'cta' => 'Email settings',
            ],
            [
                'id' => 'seo',
                'title' => 'Set site name and default meta description',
                'body' => 'Templates control titles site-wide. You can still override SEO on each tool or alternative.',
                'done' => $seoDone,
                'href' => SiteSeoSettings::getUrl(),
                'cta' => 'SEO settings',
            ],
            [
                'id' => 'pages',
                'title' => 'Review About / legal pages',
                'body' => 'Publish CMS pages or use built-in About, Privacy, and Terms until you customize them.',
                'done' => true,
                'href' => url('/about'),
                'cta' => 'View About',
            ],
        ];
    }

    public function progress(): array
    {
        $steps = $this->steps();
        $done = collect($steps)->where('done', true)->count();
        $total = count($steps);

        return [
            'done' => $done,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
        ];
    }
}
