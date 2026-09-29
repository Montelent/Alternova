<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use App\Services\CompareBasket;
use App\Services\OgImageService;
use Livewire\Attributes\Url;
use Livewire\Component;

class CompareAlternatives extends Component
{
    #[Url(as: 'a', history: true, keep: true)]
    public string $leftSlug = '';

    #[Url(as: 'b', history: true, keep: true)]
    public string $rightSlug = '';

    public string $shareCopied = '';

    public function mount(): void
    {
        // Support ?tools=slug1,slug2 as an alternate share format
        $tools = request()->query('tools');
        if (is_string($tools) && $tools !== '' && $this->leftSlug === '' && $this->rightSlug === '') {
            $parts = array_values(array_filter(array_map('trim', explode(',', $tools))));
            if (isset($parts[0])) {
                $this->leftSlug = $parts[0];
            }
            if (isset($parts[1])) {
                $this->rightSlug = $parts[1];
            }
        }

        // Hydrate from session basket if URL empty
        if ($this->leftSlug === '' && $this->rightSlug === '') {
            $basket = app(CompareBasket::class)->all();
            if (isset($basket[0])) {
                $this->leftSlug = $basket[0];
            }
            if (isset($basket[1])) {
                $this->rightSlug = $basket[1];
            }
        }

        $this->syncBasket();
    }

    public function updatedLeftSlug(): void
    {
        $this->shareCopied = '';
        $this->syncBasket();
    }

    public function updatedRightSlug(): void
    {
        $this->shareCopied = '';
        $this->syncBasket();
    }

    protected function syncBasket(): void
    {
        $slugs = array_values(array_filter([$this->leftSlug, $this->rightSlug]));
        if ($slugs !== []) {
            app(CompareBasket::class)->set($slugs);
        }
    }

    public function swap(): void
    {
        [$this->leftSlug, $this->rightSlug] = [$this->rightSlug, $this->leftSlug];
        $this->shareCopied = '';
        $this->syncBasket();
    }

    public function markCopied(): void
    {
        $this->shareCopied = 'Link copied';
    }

    public function shareUrl(): ?string
    {
        if ($this->leftSlug === '' || $this->rightSlug === '') {
            return null;
        }

        return CompareBasket::urlFor($this->leftSlug, $this->rightSlug);
    }

    public function render()
    {
        $left = $this->resolve($this->leftSlug);
        $right = $this->resolve($this->rightSlug);

        $options = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $title = 'Compare open-source alternatives | Alternova';
        $description = 'Compare self-hostable open-source tools side by side — license, health score, stars, and difficulty.';
        $ogImage = null;
        $shareUrl = $this->shareUrl();

        if ($left && $right) {
            $title = $left->name.' vs '.$right->name.' — Compare | Alternova';
            $description = 'Side-by-side comparison of '.$left->name.' and '.$right->name
                .': health '.number_format($left->overall_health_score, 1).' vs '.number_format($right->overall_health_score, 1)
                .', license, GitHub metrics, and self-host difficulty.';
            try {
                $ogImage = app(OgImageService::class)->alternativeUrl($left);
            } catch (\Throwable) {
            }
        }

        $canonical = $shareUrl ?: route('alternatives.compare');

        return view('livewire.compare-alternatives', [
            'left' => $left,
            'right' => $right,
            'options' => $options,
            'rows' => $this->comparisonRows($left, $right),
            'shareUrl' => $shareUrl,
            'schema' => $this->buildSchema($left, $right, $canonical),
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'ogType' => 'website',
            'ogTitle' => $title,
            'ogDescription' => $description,
            'ogImage' => $ogImage,
        ]);
    }

    protected function resolve(string $slug): ?OpenSourceAlternative
    {
        if ($slug === '') {
            return null;
        }

        return OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true)
            ->where('slug', $slug)
            ->first();
    }

    protected function buildSchema(?OpenSourceAlternative $left, ?OpenSourceAlternative $right, string $canonical): ?array
    {
        if (! $left || ! $right) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $left->name.' vs '.$right->name,
            'description' => 'Comparison of '.$left->name.' and '.$right->name,
            'url' => $canonical,
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => $left->name,
                        'url' => route('alternatives.show', $left),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => $right->name,
                        'url' => route('alternatives.show', $right),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<array{label: string, left: string, right: string, winner: ?string}>
     */
    protected function comparisonRows(?OpenSourceAlternative $left, ?OpenSourceAlternative $right): array
    {
        if (! $left || ! $right) {
            return [];
        }

        $lStars = (int) ($left->repoMetric?->github_stars ?? 0);
        $rStars = (int) ($right->repoMetric?->github_stars ?? 0);
        $lForks = (int) ($left->repoMetric?->github_forks ?? 0);
        $rForks = (int) ($right->repoMetric?->github_forks ?? 0);
        $lIssues = (int) ($left->repoMetric?->open_issues ?? 0);
        $rIssues = (int) ($right->repoMetric?->open_issues ?? 0);

        return [
            [
                'label' => 'Replaces',
                'left' => $left->proprietaryTool?->name ?? '—',
                'right' => $right->proprietaryTool?->name ?? '—',
                'winner' => null,
            ],
            [
                'label' => 'License',
                'left' => $left->license_type ?? '—',
                'right' => $right->license_type ?? '—',
                'winner' => null,
            ],
            [
                'label' => 'Primary language',
                'left' => $left->primary_language ?? '—',
                'right' => $right->primary_language ?? '—',
                'winner' => null,
            ],
            [
                'label' => 'Health score',
                'left' => number_format($left->overall_health_score, 1).'/100',
                'right' => number_format($right->overall_health_score, 1).'/100',
                'winner' => $this->winnerHigher($left->overall_health_score, $right->overall_health_score),
            ],
            [
                'label' => 'GitHub stars',
                'left' => number_format($lStars),
                'right' => number_format($rStars),
                'winner' => $this->winnerHigher($lStars, $rStars),
            ],
            [
                'label' => 'Forks',
                'left' => number_format($lForks),
                'right' => number_format($rForks),
                'winner' => $this->winnerHigher($lForks, $rForks),
            ],
            [
                'label' => 'Open issues',
                'left' => number_format($lIssues),
                'right' => number_format($rIssues),
                'winner' => $this->winnerLower($lIssues, $rIssues),
            ],
            [
                'label' => 'Self-host difficulty',
                'left' => $left->self_host_difficulty.'/5',
                'right' => $right->self_host_difficulty.'/5',
                'winner' => $this->winnerLower($left->self_host_difficulty, $right->self_host_difficulty),
            ],
            [
                'label' => 'Website',
                'left' => $left->website_url ? 'Yes' : '—',
                'right' => $right->website_url ? 'Yes' : '—',
                'winner' => null,
            ],
        ];
    }

    protected function winnerHigher(float|int $a, float|int $b): ?string
    {
        if ($a == $b) {
            return 'tie';
        }

        return $a > $b ? 'left' : 'right';
    }

    protected function winnerLower(float|int $a, float|int $b): ?string
    {
        if ($a == $b) {
            return 'tie';
        }

        return $a < $b ? 'left' : 'right';
    }
}
