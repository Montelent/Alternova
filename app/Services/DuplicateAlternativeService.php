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
     * Counts of alternatives involved (0 when clean).
     *
     * @return array{same_repo: int, similar_name: int}
     */
    public function summaryCounts(): array
    {
        $groups = $this->sameRepoGroups();
        $pairs = $this->similarNamePairs();

        // same_repo: total alternatives that share a repo with at least one other
        $sameRepoItems = (int) $groups->sum(fn (Collection $g) => $g->count());

        // similar_name: unique alternatives appearing in similar-name pairs
        $similarIds = [];
        foreach ($pairs as $pair) {
            $similarIds[$pair['a']->id] = true;
            $similarIds[$pair['b']->id] = true;
        }

        return [
            'same_repo' => $sameRepoItems,
            'similar_name' => count($similarIds),
        ];
    }

    /**
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
            foreach ($group->pluck('id') as $id) {
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
                    && $this->normalizeRepo((string) $a->repo_url) === $this->normalizeRepo((string) $b->repo_url)
                ) {
                    continue;
                }

                $score = $this->similarity((string) $a->name, (string) $b->name);
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
        $url = preg_replace('#^git\+#', '', $url) ?? $url;
        $url = preg_replace('#\.git$#', '', $url) ?? $url;
        $url = preg_replace('#^https?://(www\.)?#', '', $url) ?? $url;
        $url = rtrim($url, '/');

        if (str_starts_with($url, 'github.com/')) {
            $parts = explode('/', $url);
            if (count($parts) >= 3) {
                return 'github.com/'.$parts[1].'/'.$parts[2];
            }
        }

        return $url;
    }

    public function similarity(string $a, string $b): float
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
