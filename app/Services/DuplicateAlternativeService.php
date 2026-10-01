<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Detect likely duplicate or near-duplicate alternatives for admin review.
 */
class DuplicateAlternativeService
{
    /**
     * @return array{same_repo: int, similar_name: int}
     */
    public function summaryCounts(): array
    {
        $groups = $this->sameRepoGroups();
        $pairs = $this->similarNamePairs();

        return [
            'same_repo' => $groups->sum(fn (Collection $g) => $g->count()),
            'similar_name' => $pairs->count() * 2,
        ];
    }

    /**
     * Groups of alternatives sharing the same normalized GitHub/repo URL.
     *
     * @return Collection<string, Collection<int, OpenSourceAlternative>>
     */
    public function sameRepoGroups(): Collection
    {
        $alts = OpenSourceAlternative::query()
            ->with('proprietaryTool')
            ->whereNotNull('repo_url')
            ->where('repo_url', '!=', '')
            ->orderBy('name')
            ->get();

        return $alts
            ->groupBy(fn (OpenSourceAlternative $a) => $this->normalizeRepo($a->repo_url))
            ->filter(fn (Collection $g, $key) => $key !== '' && $g->count() > 1)
            ->sortByDesc(fn (Collection $g) => $g->count());
    }

    /**
     * Pairs with high name similarity (not already same-repo groups).
     *
     * @return Collection<int, array{a: OpenSourceAlternative, b: OpenSourceAlternative, score: float}>
     */
    public function similarNamePairs(float $threshold = 85.0): Collection
    {
        $alts = OpenSourceAlternative::query()
            ->with('proprietaryTool')
            ->orderBy('name')
            ->get();

        $sameRepoIds = [];
        foreach ($this->sameRepoGroups() as $group) {
            $ids = $group->pluck('id')->all();
            foreach ($ids as $id) {
                $sameRepoIds[$id] = true;
            }
        }

        $pairs = collect();
        $list = $alts->values();
        $n = $list->count();

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                /** @var OpenSourceAlternative $a */
                $a = $list[$i];
                /** @var OpenSourceAlternative $b */
                $b = $list[$j];

                if (isset($sameRepoIds[$a->id], $sameRepoIds[$b->id])
                    && $this->normalizeRepo($a->repo_url) === $this->normalizeRepo($b->repo_url)
                    && $this->normalizeRepo($a->repo_url) !== '') {
                    continue;
                }

                $score = $this->nameSimilarity($a->name, $b->name);
                if ($score >= $threshold) {
                    $pairs->push([
                        'a' => $a,
                        'b' => $b,
                        'score' => $score,
                    ]);
                }
            }
        }

        return $pairs->sortByDesc('score')->values();
    }

    public function normalizeRepo(?string $url): string
    {
        if (! $url) {
            return '';
        }

        $url = strtolower(trim($url));
        $url = preg_replace('#^https?://#', '', $url) ?? $url;
        $url = preg_replace('#^www\.#', '', $url) ?? $url;
        $url = rtrim($url, '/');
        $url = preg_replace('#\.git$#', '', $url) ?? $url;

        // github.com/owner/repo → owner/repo
        if (preg_match('#github\.com/([^/]+/[^/]+)#', $url, $m)) {
            return 'github.com/'.strtolower($m[1]);
        }

        return $url;
    }

    public function nameSimilarity(string $a, string $b): float
    {
        $a = Str::lower(trim($a));
        $b = Str::lower(trim($b));

        if ($a === '' || $b === '') {
            return 0.0;
        }

        if ($a === $b) {
            return 100.0;
        }

        similar_text($a, $b, $percent);

        return (float) $percent;
    }
}
