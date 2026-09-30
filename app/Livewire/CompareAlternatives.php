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

    #[Url(as: 'c', history: true, keep: true)]
    public string $thirdSlug = '';

    public string $shareCopied = '';

    public function mount(): void
    {
        $tools = request()->query('tools');
        if (is_string($tools) && $tools !== '' && $this->leftSlug === '' && $this->rightSlug === '') {
            $parts = array_values(array_filter(array_map('trim', explode(',', $tools))));
            if (isset($parts[0])) {
                $this->leftSlug = $parts[0];
            }
            if (isset($parts[1])) {
                $this->rightSlug = $parts[1];
            }
            if (isset($parts[2])) {
                $this->thirdSlug = $parts[2];
            }
        }

        if ($this->leftSlug === '' && $this->rightSlug === '' && $this->thirdSlug === '') {
            $basket = app(CompareBasket::class)->all();
            if (isset($basket[0])) {
                $this->leftSlug = $basket[0];
            }
            if (isset($basket[1])) {
                $this->rightSlug = $basket[1];
            }
            if (isset($basket[2])) {
                $this->thirdSlug = $basket[2];
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

    public function updatedThirdSlug(): void
    {
        $this->shareCopied = '';
        $this->syncBasket();
    }

    protected function syncBasket(): void
    {
        $slugs = array_values(array_filter([$this->leftSlug, $this->rightSlug, $this->thirdSlug]));
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
        $slugs = array_values(array_filter([$this->leftSlug, $this->rightSlug, $this->thirdSlug]));
        if (count($slugs) < 2) {
            return null;
        }

        return CompareBasket::urlFor(...$slugs);
    }

    public function render()
    {
        $left = $this->resolve($this->leftSlug);
        $right = $this->resolve($this->rightSlug);
        $third = $this->resolve($this->thirdSlug);

        $options = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $sides = array_values(array_filter([$left, $right, $third]));

        $title = 'Compare open-source alternatives | Alternova';
        $description = 'Compare up to three self-hostable open-source tools — license, health score, stars, and difficulty.';
        $ogImage = null;
        $shareUrl = $this->shareUrl();

        if (count($sides) >= 2) {
            $names = collect($sides)->pluck('name')->implode(' vs ');
            $title = $names.' — Compare | Alternova';
            $description = 'Side-by-side comparison of '.$names
                .': health, license, GitHub metrics, and self-host difficulty.';
            if ($left && $right) {
                try {
                    $ogImage = app(OgImageService::class)->compareUrl($left->slug, $right->slug);
                } catch (\Throwable) {
                }
            }
        }

        $canonical = $shareUrl ?: route('alternatives.compare');

        return view('livewire.compare-alternatives', [
            'left' => $left,
            'right' => $right,
            'third' => $third,
            'sides' => $sides,
            'options' => $options,
            'rows' => $this->comparisonRows($sides),
            'shareUrl' => $shareUrl,
            'schema' => $this->buildSchema($sides, $canonical),
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

    protected function buildSchema(array $sides, string $canonical): ?array
    {
        if (count($sides) < 2) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => collect($sides)->pluck('name')->implode(' vs '),
            'description' => 'Comparison of open-source alternatives',
            'url' => $canonical,
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => collect($sides)->values()->map(function ($alt, $i) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'name' => $alt->name,
                        'url' => route('alternatives.show', $alt),
                    ];
                })->all(),
            ],
        ];
    }

    /**
     * @param  list<OpenSourceAlternative>  $sides
     * @return list<array{label: string, values: list<string>, winners: list<bool>}>
     */
    protected function comparisonRows(array $sides): array
    {
        if (count($sides) < 2) {
            return [];
        }

        $metrics = [
            [
                'label' => 'Replaces',
                'values' => array_map(fn ($a) => $a->proprietaryTool?->name ?? '—', $sides),
                'mode' => null,
            ],
            [
                'label' => 'License',
                'values' => array_map(fn ($a) => $a->license_type ?? '—', $sides),
                'mode' => null,
            ],
            [
                'label' => 'Primary language',
                'values' => array_map(fn ($a) => $a->primary_language ?? '—', $sides),
                'mode' => null,
            ],
            [
                'label' => 'Health score',
                'values' => array_map(fn ($a) => number_format($a->overall_health_score, 1).'/100', $sides),
                'nums' => array_map(fn ($a) => (float) $a->overall_health_score, $sides),
                'mode' => 'higher',
            ],
            [
                'label' => 'GitHub stars',
                'values' => array_map(fn ($a) => number_format((int) ($a->repoMetric?->github_stars ?? 0)), $sides),
                'nums' => array_map(fn ($a) => (int) ($a->repoMetric?->github_stars ?? 0), $sides),
                'mode' => 'higher',
            ],
            [
                'label' => 'Forks',
                'values' => array_map(fn ($a) => number_format((int) ($a->repoMetric?->github_forks ?? 0)), $sides),
                'nums' => array_map(fn ($a) => (int) ($a->repoMetric?->github_forks ?? 0), $sides),
                'mode' => 'higher',
            ],
            [
                'label' => 'Open issues',
                'values' => array_map(fn ($a) => number_format((int) ($a->repoMetric?->open_issues ?? 0)), $sides),
                'nums' => array_map(fn ($a) => (int) ($a->repoMetric?->open_issues ?? 0), $sides),
                'mode' => 'lower',
            ],
            [
                'label' => 'Self-host difficulty',
                'values' => array_map(fn ($a) => $a->self_host_difficulty.'/5', $sides),
                'nums' => array_map(fn ($a) => (int) $a->self_host_difficulty, $sides),
                'mode' => 'lower',
            ],
            [
                'label' => 'Website',
                'values' => array_map(fn ($a) => $a->website_url ? 'Yes' : '—', $sides),
                'mode' => null,
            ],
        ];

        $rows = [];
        foreach ($metrics as $m) {
            $winners = array_fill(0, count($sides), false);
            if (($m['mode'] ?? null) && isset($m['nums'])) {
                $nums = $m['nums'];
                $best = $m['mode'] === 'higher' ? max($nums) : min($nums);
                $allSame = count(array_unique($nums)) === 1;
                if (! $allSame) {
                    foreach ($nums as $i => $n) {
                        $winners[$i] = $n == $best;
                    }
                }
            }
            $rows[] = [
                'label' => $m['label'],
                'values' => $m['values'],
                'winners' => $winners,
            ];
        }

        return $rows;
    }
}
