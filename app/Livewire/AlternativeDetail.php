<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use App\Models\SlugRedirect;
use App\Services\CompareBasket;
use App\Services\FavoriteService;
use App\Services\OgImageService;
use App\Services\RecentlyViewedService;
use App\Services\SeoManager;
use App\Services\VoteService;
use Illuminate\Http\Exceptions\HttpResponseException;
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

    public bool $inCompare = false;

    public string $compareMessage = '';

    public ?string $compareUrl = null;

    public function mount(string $alternative): void
    {
        $requestedSlug = $alternative;

        $record = OpenSourceAlternative::query()
            ->where('slug', $requestedSlug)
            ->where('is_published', true)
            ->first();

        if (! $record) {
            try {
                $redirect = SlugRedirect::query()
                    ->where('old_slug', $requestedSlug)
                    ->where(function ($q) {
                        $q->where('model_type', 'alternative')
                            ->orWhereNull('model_type')
                            ->orWhere('model_type', '');
                    })
                    ->first();

                if ($redirect) {
                    $target = OpenSourceAlternative::query()
                        ->where('slug', $redirect->new_slug)
                        ->where('is_published', true)
                        ->first();

                    if ($target) {
                        throw new HttpResponseException(
                            redirect()->to(route('alternatives.show', $target), 301)
                        );
                    }
                }
            } catch (HttpResponseException $e) {
                throw $e;
            } catch (\Throwable) {
            }

            abort(404);
        }

        $this->alternative = $record->load(['proprietaryTool', 'repoMetric', 'tags']);
        $this->votesCount = (int) ($this->alternative->votes_count ?? 0);

        try {
            app(RecentlyViewedService::class)->push($this->alternative->id);
        } catch (\Throwable) {
        }

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

        $this->refreshCompareState();
    }

    protected function refreshCompareState(): void
    {
        $basket = app(CompareBasket::class);
        $this->inCompare = $basket->has($this->alternative->slug);
        $state = $basket->state();
        $this->compareUrl = $state['url'];
        $this->compareMessage = $state['count'] === 1 && $this->inCompare
            ? 'Pick one more alternative to compare'
            : '';
    }

    public function toggleCompare(): void
    {
        $basket = app(CompareBasket::class);

        if ($basket->has($this->alternative->slug)) {
            $state = $basket->remove($this->alternative->slug);
        } else {
            $state = $basket->add($this->alternative->slug);
        }

        $this->inCompare = $basket->has($this->alternative->slug);
        $this->compareUrl = $state['url'];
        $this->compareMessage = $state['message'];
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
        } catch (\Throwable) {
            $this->favoriteMessage = 'Could not update favorites. Run migrations first.';
        }
    }

    public function render()
    {
        $alt = $this->alternative;
        $prop = $alt->proprietaryTool;
        $propName = $prop?->name ?? 'proprietary tools';
        $seo = app(SeoManager::class);

        $title = $seo->alternativeTitle($alt);
        $description = $seo->alternativeDescription($alt);
        $canonical = $alt->canonical_url ?: route('alternatives.show', $alt);

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

        if ($related->count() < 6) {
            $categoryNames = $alt->tags->where('type', 'category')->pluck('name')->all();
            if ($categoryNames !== []) {
                $more = OpenSourceAlternative::query()
                    ->with(['repoMetric'])
                    ->where('is_published', true)
                    ->where('id', '!=', $alt->id)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->withAnyTags($categoryNames, 'category')
                    ->orderByDesc('overall_health_score')
                    ->limit(6 - $related->count())
                    ->get();
                $related = $related->concat($more)->values();
            }
        }

        $recent = app(RecentlyViewedService::class)->list($alt->id);
        $badgeUrl = url('/badge/'.$alt->slug.'/health.svg');
        $ogImage = app(OgImageService::class)->alternativeUrl($alt);

        return view('livewire.alternative-detail', [
            'schemas' => $this->buildSchemas($alt, $prop, $canonical),
            'proprietary' => $prop,
            'metric' => $alt->repoMetric,
            'heading' => $alt->name,
            'subheading' => 'The open-source alternative to '.$propName,
            'propName' => $propName,
            'related' => $related,
            'recent' => $recent,
            'badgeUrl' => $badgeUrl,
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $alt->robots_meta ?: null,
            'ogType' => 'article',
            'ogTitle' => $alt->og_title ?: $title,
            'ogDescription' => $alt->og_description ?: $description,
            'ogImage' => $ogImage,
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
