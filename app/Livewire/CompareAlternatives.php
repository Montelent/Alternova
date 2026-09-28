<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use Livewire\Attributes\Url;
use Livewire\Component;

class CompareAlternatives extends Component
{
    #[Url(as: 'a')]
    public string $leftSlug = '';

    #[Url(as: 'b')]
    public string $rightSlug = '';

    public function mount(?string $a = null, ?string $b = null): void
    {
        if ($a) {
            $this->leftSlug = $a;
        }
        if ($b) {
            $this->rightSlug = $b;
        }
    }

    public function swap(): void
    {
        [$this->leftSlug, $this->rightSlug] = [$this->rightSlug, $this->leftSlug];
    }

    public function render()
    {
        $left = $this->resolve($this->leftSlug);
        $right = $this->resolve($this->rightSlug);

        $options = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $title = 'Compare open-source alternatives';
        $description = 'Compare self-hostable open-source tools side by side — license, health score, stars, and difficulty.';

        if ($left && $right) {
            $title = $left->name.' vs '.$right->name.' — Compare | Alternova';
            $description = 'Side-by-side comparison of '.$left->name.' and '.$right->name.': license, GitHub metrics, health score, and self-host difficulty.';
        }

        return view('livewire.compare-alternatives', [
            'left' => $left,
            'right' => $right,
            'options' => $options,
            'rows' => $this->comparisonRows($left, $right),
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => route('alternatives.compare', array_filter([
                'a' => $this->leftSlug ?: null,
                'b' => $this->rightSlug ?: null,
            ])),
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
