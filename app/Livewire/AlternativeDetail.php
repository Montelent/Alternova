<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use App\Services\FavoriteService;
use App\Services\VoteService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class AlternativeDetail extends Component
{
    public OpenSourceAlternative $alternative;

    public bool $hasVoted = false;

    public int $votesCount = 0;

    public string $voteMessage = '';

    public bool $isFavorited = false;

    public string $favoriteMessage = '';

    public function mount(OpenSourceAlternative $alternative): void
    {
        abort_unless($alternative->is_published, 404);

        $this->alternative = $alternative->load(['proprietaryTool', 'repoMetric', 'tags']);
        $this->votesCount = (int) ($this->alternative->votes_count ?? 0);

        try {
            $voterKey = app(VoteService::class)->voterKey(session()->getId(), request()->ip());
            $this->hasVoted = app(VoteService::class)->hasVoted($this->alternative, $voterKey);
        } catch (\Throwable) {
            $this->hasVoted = false;
        }

        try {
            $this->isFavorited = app(FavoriteService::class)->has($this->alternative);
        } catch (\Throwable) {
            $this->isFavorited = false;
        }
    }

    public function vote(): void
    {
        $key = 'vote:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            $this->voteMessage = 'Too many votes from this network. Try later.';

            return;
        }

        RateLimiter::hit($key, 3600);

        $service = app(VoteService::class);
        $voterKey = $service->voterKey(session()->getId(), request()->ip());
        $result = $service->vote($this->alternative, $voterKey, request()->ip());

        $this->votesCount = $result['votes'];
        $this->hasVoted = $result['voted'];
        $this->voteMessage = $result['message'];
    }

    public function toggleFavorite(): void
    {
        try {
            $result = app(FavoriteService::class)->toggle($this->alternative);
            $this->isFavorited = $result['favorited'];
            $this->favoriteMessage = $result['message'];
        } catch (\Throwable $e) {
            $this->favoriteMessage = 'Could not update favorites. Run migrations first.';
        }
    }

    public function render()
    {
        $alt = $this->alternative;
        $prop = $alt->proprietaryTool;
        $propName = $prop?->name ?? 'proprietary tools';
        $canonical = route('alternatives.show', $alt);

        $related = OpenSourceAlternative::query()
            ->with(['repoMetric'])
            ->where('is_published', true)
            ->where('id', '!=', $alt->id)
            ->when(
                $alt->proprietary_tool_id,
                fn ($q) => $q->where('proprietary_tool_id', $alt->proprietary_tool_id),
                fn ($q) => $q->whereRaw('0 = 1')
            )
            ->orderByDesc('overall_health_score')
            ->limit(6)
            ->get();

        $badgeUrl = url('/badge/'.$alt->slug.'/health.svg');

        return view('livewire.alternative-detail', [
            'schemas' => $this->buildSchemas($alt, $prop, $canonical),
            'proprietary' => $prop,
            'metric' => $alt->repoMetric,
            'heading' => $alt->name,
            'subheading' => 'The open-source alternative to '.$propName,
            'propName' => $propName,
            'related' => $related,
            'badgeUrl' => $badgeUrl,
        ])->layout('layouts.app', [
            'title' => $alt->seoTitle(),
            'description' => $alt->seoDescription(),
            'canonical' => $canonical,
            'ogType' => 'article',
            'ogImage' => $prop?->logo_path ? url($prop->logo_path) : null,
        ]);
    }

    protected function buildSchemas(OpenSourceAlternative $alt, $prop, string $canonical): array
    {
        $software = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $alt->name,
            'description' => $alt->description,
            'url' => $canonical,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Cross-platform',
            'softwareLicense' => $alt->license_type,
            'codeRepository' => $alt->repo_url,
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ];

        if ($alt->website_url) {
            $software['sameAs'] = array_values(array_filter([$alt->website_url, $alt->repo_url]));
        }

        if ($alt->repoMetric && $alt->repoMetric->github_stars > 0) {
            $software['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => max(1, min(5, round($alt->overall_health_score / 20, 1))),
                'ratingCount' => max(1, (int) $alt->repoMetric->github_stars),
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Open Source Alternatives', 'item' => route('finder')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $alt->name, 'item' => $canonical],
            ],
        ];

        $faq = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Is '.$alt->name.' free?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Yes. '.$alt->name.' is released under the '.($alt->license_type ?? 'open-source').' license and can be self-hosted at no software cost.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'How difficult is it to self-host '.$alt->name.'?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Self-host difficulty is rated '.$alt->self_host_difficulty.'/5.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'What is '.$alt->name.' an alternative to?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $alt->name.' is a self-hostable open-source alternative to '.($prop?->name ?? 'proprietary software').'.',
                    ],
                ],
            ],
        ];

        return [$software, $breadcrumb, $faq];
    }
}
