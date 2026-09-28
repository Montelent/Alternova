<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use Illuminate\Support\Collection;

class RecentlyViewedService
{
    protected string $key = 'alternova_recent_alts';

    public function push(int $alternativeId): void
    {
        $ids = session()->get($this->key, []);
        $ids = array_values(array_filter($ids, fn ($id) => (int) $id !== $alternativeId));
        array_unshift($ids, $alternativeId);
        session()->put($this->key, array_slice($ids, 0, 12));
    }

    public function list(int $excludeId = 0): Collection
    {
        $ids = array_values(array_filter(
            session()->get($this->key, []),
            fn ($id) => (int) $id !== $excludeId
        ));

        if ($ids === []) {
            return collect();
        }

        $alts = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)->map(fn ($id) => $alts->get($id))->filter()->values();
    }
}
