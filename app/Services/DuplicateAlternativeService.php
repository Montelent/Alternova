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
     * @return array{same_repo: int, similar_name: int, total: int}
     */
    public function summaryCounts(): array
    {
        $groups = $this->sameRepoGroups();
        $pairs = $this->similarNamePairs();

        $sameRepoIds = [];
        foreach ($groups as $group) {
            foreach ($group as $alt) {
                $sameRepoIds[(int) $alt->id] = true;
            }
        }

        $similarIds = [];
        foreach ($pairs as $pair) {
            $similarIds[(int) $pair['a']->id] = true;
            $similarIds[(int) $pair['b']->id] = true;
        }

        $sameRepo = count($sameRepoIds);
        $similarName = count($similarIds);
        $all = $sameRepoIds + $similarIds;

        return [
            'same_repo' => $sameRepo,
            'similar_name' => $similarName,
            'total' => count($all),
        ];
    }

    public function totalSuspects(): int
    {
        return (int) ($this->summaryCounts()['total'] ?? 0);
    }

    /**
     * @return Collection<string, Collection<int, OpenSourceAlternative>>
     */
    public function sameRepoGroups(): Collection
    {
        try {
            $alts = OpenSourceAlternative::query()
                ->with(['proprietaryTool'])
                ->whereNotNull('repo_url')
                ->where('repo_url', '!=', '')
                ->orderBy('name')
                ->get();
        } catch (\Throwable) {
            return collect();
        }

        return $alts
            ->groupBy(fn (OpenSourceAlternative $a) => $this->normalizeRepo((string) $a->repo_url))
            ->filter(function ($g, $key) {
                if (! is_string($key) || trim($key) === '') {
                    return false;
                }

                return $g instanceof Collection && $g->count() > 1;
            })
            ->sortByDesc(fn (Collection $g) => $g->count())
            ->values()
            ->mapWithKeys(function (Collection $g) {
                $first = $g->first();
                $key = $first ? $this->normalizeRepo((string) $first->repo_url) : uniqid('repo_', true);

                return [$key => $g->values()];
            });
    }

    /**
     * @return Collection<int, array{a: OpenSourceAlternative, b: OpenSourceAlternative, score: float}>
     */
    public function similarNamePairs(float $threshold = 85.0): Collection
    {
        try {
            $alts = OpenSourceAlternative::query()
                ->with(['proprietaryTool'])
                ->orderBy('name')
                ->get();
        } catch (\Throwable) {
            return collect();
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

                $repoA = $this->normalizeRepo((string) ($a->repo_url ?? ''));
                $repoB = $this->normalizeRepo((string) ($b->repo_url ?? ''));
                if ($repoA !== '' && $repoA === $repoB) {
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

    /**
     * @return list<array{repo: string, items: list<array{id: int, name: string, published: bool, tool: string|null}>}>
     */
    public function sameRepoGroupsForView(): array
    {
        $out = [];
        foreach ($this->sameRepoGroups() as $repo => $group) {
            $items = [];
            foreach ($group as $alt) {
                $items[] = [
                    'id' => (int) $alt->id,
                    'name' => (string) $alt->name,
                    'published' => (bool) $alt->is_published,
                    'tool' => $alt->proprietaryTool?->name,
                ];
            }
            if (count($items) > 1) {
                $out[] = [
                    'repo' => (string) $repo,
                    'items' => $items,
                ];
            }
        }

        return $out;
    }

    /**
     * @return list<array{score: float, a: array{id: int, name: string}, b: array{id: int, name: string}}>
     */
    public function similarNamePairsForView(float $threshold = 85.0): array
    {
        $out = [];
        foreach ($this->similarNamePairs($threshold) as $pair) {
            $out[] = [
                'score' => (float) $pair['score'],
                'a' => [
                    'id' => (int) $pair['a']->id,
                    'name' => (string) $pair['a']->name,
                ],
                'b' => [
                    'id' => (int) $pair['b']->id,
                    'name' => (string) $pair['b']->name,
                ],
            ];
        }

        return $out;
    }

    public function normalizeRepo(?string $url): string
    {
        if ($url === null || trim($url) === '') {
            return '';
        }

        $url = strtolower(trim($url));
        $url = preg_replace('#^git\+#', '', $url) ?? $url;
        $url = preg_replace('#\.git$#', '', $url) ?? $url;

        if (preg_match('#^git@([^:]+):(.+)$#', $url, $m)) {
            $host = $m[1];
            $path = trim($m[2], '/');
            $url = $host.'/'.$path;
        } else {
            $url = preg_replace('#^https?://(www\.)?#', '', $url) ?? $url;
        }

        $url = rtrim($url, '/');
        $url = preg_replace('#[?#].*$#', '', $url) ?? $url;

        if (str_starts_with($url, 'github.com/') || str_starts_with($url, 'www.github.com/')) {
            $url = preg_replace('#^www\.#', '', $url) ?? $url;
            $parts = explode('/', $url);
            if (count($parts) >= 3 && $parts[1] !== '' && $parts[2] !== '') {
                return 'github.com/'.$parts[1].'/'.$parts[2];
            }
        }

        foreach (['gitlab.com/', 'bitbucket.org/', 'codeberg.org/'] as $prefix) {
            if (str_starts_with($url, $prefix)) {
                $parts = explode('/', $url);
                if (count($parts) >= 3 && $parts[1] !== '' && $parts[2] !== '') {
                    return $parts[0].'/'.$parts[1].'/'.$parts[2];
                }
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
