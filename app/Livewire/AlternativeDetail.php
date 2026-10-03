<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use App\Models\SlugRedirect;
use App\Services\AlternativePageCopy;
use App\Services\CompareBasket;
use App\Services\FavoriteService;
use App\Services\HealthHistoryService;
use App\Services\RecentlyViewedService;
use App\Services\SeoManager;
use App\Services\VoteService;
use App\Services\WatchlistService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class AlternativeDetail extends Component
{
    public OpenSourceAlternative $alternative;

    public bool $hasVoted = false;

    public int $votesCount = 0;

    public string $voteMessage = '';

    public bool $isFavorited = false;

    public string $favoriteMessage = '';

    public bool $isWatching = false;

    public string $watchMessage = '';

    public bool $inCompare = false;

    public string $compareMessage = '';

    public ?string $compareUrl = null;

    public function mount(string $slug): void
    {
        $requestedSlug = trim($slug);

        $record = OpenSourceAlternative::query()
            ->where('slug', $requestedSlug)
            ->where('is_published', true)
            ->first();

        if (! $record) {
            try {
                $redirect = null;
                if (class_exists(SlugRedirect::class)) {
                    $redirect = slugRedirect::query()
                        ->where('old_slug', $requestedSlug)
                        ->where(function ($q) {
                            $q->where('model_type', 'alternative')
                                ->orWhereNull('model_type')
                                ->orWhere('model_type', '');
                        })
                        ->first();
                }

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

        try {
            $with = ['proprietaryTool', 'repoMetric', 'tags'];
            if (Schema::hasTable('alternative_proprietary_tool')) {
                $with[] = 'proprietaryTools';
            }
            $this->alternative = $record->load($with);
        } catch (\Throwable) {
            $this->alternative = $record->load(['proprietaryTool', 'repoMetric']);
        }

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

        try {
            $this->isWatching = app(WatchlistService::class)->isWatching($this->alternative);
        } catch (\Throwable) {
            $this->isWatching = false;
        }

        $this->refreshCompareState();
    }

    protected function refreshCompareState(): void
    {
        try {
            $basket = app(CompareBasket::class);
            $this->inCompare = $basket->has($this->alternative->slug);
            $state = $basket->state();
            $this->compareUrl = $state['url'];
            $this->compareMessage = $state['count'] === 1 && $this->inCompare
                ? 'Pick one more alternative to compare'
                : '';
        } catch (\Throwable) {
            $this->inCompare = false;
            $this->compareUrl = null;
            $this->compareMessage = '';
        }
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

        $voterKey = app(VoteService::class)->voterKey(session()->getId(), request()->ip());
        $result = app(VoteService::class)->toggle($this->alternative, $voterKey, request()->ip());
        $this->hasVoted = $result['voted'];
        $this->votesCount = $result['count'];
        $this->voteMessage = $result['message'] ?? ($this->hasVoted ? 'Thanks for the vote.' : 'Vote removed.');
    }

    public function toggleFavorite(): void
    {
        $on = app(FavoriteService::class)->toggle($this->alternative);
        $this->isFavorited = $on;
        $this->favoriteMessage = $on ? 'Saved to favorites.' : 'Removed from favorites.';
    }

    public function toggleWatch(): void
    {
        if (! Auth::check()) {
            $this->watchMessage = 'Sign in to watch health changes.';

            return;
        }

        $on = app(WatchlistService::class)->toggle($this->alternative);
        $this->isWatching = $on;
        $this->watchMessage = $on ? 'Watching for health drops.' : 'Removed from watchlist.';
    }

    public function render()
    {
        $alt = $this->alternative;
        $prop = $alt->proprietaryTool;

        $propTools = collect();
        try {
            if ($alt->relationLoaded('proprietaryTools') && $alt->proprietaryTools->isNotEmpty()) {
                $propTools = $alt->proprietaryTools;
            } elseif ($prop) {
                $propTools = collect([$prop]);
            }
        } catch (\Throwable) {
            if ($prop) {
                $propTools = collect([$prop]);
            }
        }

        $propName = $propTools->pluck('name')->join(', ') ?: 'proprietary tools';
        $seo = app(SeoManager::class);

        $title = $seo->alternativeTitle($alt);
        $description = $seo->alternativeDescription($alt);
        $canonical = $alt->canonical_url ?: route('alternatives.show', $alt);

        $related = collect();
        try {
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
                $categoryNames = $alt->relationLoaded('tags')
                    ? $alt->tags->where('type', 'category')->pluck('name')->all()
                    : [];
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
        } catch (\Throwable) {
        }

        $recent = collect();
        try {
            $recent = app(RecentlyViewedService::class)->list($alt->id);
        } catch (\Throwable) {
        }

        $badgeUrl = url('/badge/'.$alt->slug.'.svg');

        $healthSeries = collect();
        $healthPoints = '';
        $healthTrend = 'flat';
        try {
            $history = app(HealthHistoryService::class);
            $healthSeries = $history->series($alt, 30);
            $healthPoints = $history->sparklinePoints($healthSeries);
            $healthTrend = $history->trend($healthSeries);
        } catch (\Throwable) {
        }

        $editorial = app(AlternativePageCopy::class)->build($alt);

        if (! filled($alt->meta_description) && ! empty($editorial['meta_description'])) {
            $description = $editorial['meta_description'];
        }

        $social = $seo->alternativeSocial($alt);
        if (! filled($alt->og_description) && ! empty($editorial['meta_description'])) {
            $social['description'] = $editorial['meta_description'];
        }

        return view('livewire.alternative-detail', [
            'schemas' => $this->buildSchemas($alt, $prop, $canonical),
            'editorial' => $editorial,
            'proprietary' => $prop,
            'proprietaryTools' => $propTools,
            'metric' => $alt->repoMetric,
            'heading' => $alt->name,
            'subheading' => 'The open-source alternative to '.$propName,
            'propName' => $propName,
            'related' => $related,
            'recent' => $recent,
            'badgeUrl' => $badgeUrl,
            'healthSeries' => $healthSeries,
            'healthPoints' => $healthPoints,
            'healthTrend' => $healthTrend,
            'isLoggedIn' => Auth::check(),
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $alt->robots_meta ?: null,
            'ogType' => $social['type'],
            'ogTitle' => $social['title'],
            'ogDescription' => $social['description'],
            'ogImage' => $social['image'],
        ]);
    }

    protected function buildSchemas(OpenSourceAlternative $alt, $prop, string $canonical): array
    {
        $software = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $alt->name,
            'description' => strip_tags((string) $alt->description),
            'url' => $canonical,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Cross-platform',
            'softwareLicense' => $alt->license_type,
        ];

        if ($alt->website_url) {
            $software['sameAs'] = [$alt->website_url];
        }

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Alternatives', 'item' => route('finder')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $alt->name, 'item' => $canonical],
            ],
        ];

        return [$software, $breadcrumb];
    }
}
