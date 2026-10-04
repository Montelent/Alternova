<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Services\SeoManager;
use App\Support\QueryCache;
use App\Support\SchemaCache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OpenSourceFinder extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public array $licenses = [];

    #[Url]
    public array $difficulties = [];

    #[Url]
    public array $categories = [];

    #[Url]
    public array $languages = [];

    #[Url(as: 'tool')]
    public string $toolSlug = '';

    #[Url]
    public string $sort = 'health';

    public int $perPage = 12;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLicenses(): void
    {
        $this->resetPage();
    }

    public function updatingDifficulties(): void
    {
        $this->resetPage();
    }

    public function updatingCategories(): void
    {
        $this->resetPage();
    }

    public function updatingLanguages(): void
    {
        $this->resetPage();
    }

    public function updatingToolSlug(): void
    {
        $this->resetPage();
    }

    public function toggleCategory(string $name): void
    {
        if (in_array($name, $this->categories, true)) {
            $this->categories = array_values(array_filter($this->categories, fn ($c) => $c !== $name));
        } else {
            $this->categories[] = $name;
        }
        $this->resetPage();
    }

    public function toggleLicense(string $name): void
    {
        if (in_array($name, $this->licenses, true)) {
            $this->licenses = array_values(array_filter($this->licenses, fn ($c) => $c !== $name));
        } else {
            $this->licenses[] = $name;
        }
        $this->resetPage();
    }

    public function toggleLanguage(string $name): void
    {
        if (in_array($name, $this->languages, true)) {
            $this->languages = array_values(array_filter($this->languages, fn ($c) => $c !== $name));
        } else {
            $this->languages[] = $name;
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'licenses', 'difficulties', 'categories', 'languages', 'toolSlug']);
        $this->sort = 'health';
        $this->resetPage();
    }

    #[Computed]
    public function alternatives()
    {
        $query = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric', 'tags'])
            ->where('is_published', true);

        // Only load multi-tool relation when the pivot exists (cached schema check)
        if (SchemaCache::hasTable('alternative_proprietary_tool')) {
            $query->with('proprietaryTools');
        }

        if (strlen($this->search) >= 2) {
            try {
                $searchResults = OpenSourceAlternative::search($this->search)->take(200)->keys();
                $query->whereIn('id', $searchResults);
            } catch (\Throwable) {
                $term = str_replace(['%', '_'], ['\\%', '\\_'], $this->search);
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%'.$term.'%')
                        ->orWhere('description', 'like', '%'.$term.'%');
                });
            }
        }

        if (! empty($this->licenses)) {
            $query->whereIn('license_type', $this->licenses);
        }

        if (! empty($this->languages)) {
            $query->whereIn('primary_language', $this->languages);
        }

        if (! empty($this->difficulties)) {
            $query->whereIn('self_host_difficulty', $this->difficulties);
        }

        if (! empty($this->categories)) {
            $query->withAnyTags($this->categories, 'category');
        }

        if ($this->toolSlug !== '') {
            $query->where(function ($q) {
                $q->whereHas('proprietaryTool', fn ($qq) => $qq->where('slug', $this->toolSlug));
                if (SchemaCache::hasTable('alternative_proprietary_tool')) {
                    $q->orWhereHas('proprietaryTools', fn ($qq) => $qq->where('slug', $this->toolSlug));
                }
            });
        }

        $hasSponsored = SchemaCache::hasColumn('open_source_alternatives', 'is_sponsored');
        if ($hasSponsored && in_array($this->sort, ['health', 'votes'], true)) {
            $query->orderByRaw(
                'CASE WHEN is_sponsored = 1 AND (sponsored_until IS NULL OR sponsored_until > ?) THEN 0 ELSE 1 END',
                [now()]
            );
        }

        $query = match ($this->sort) {
            'stars' => $query->leftJoin('repo_metrics', 'open_source_alternatives.id', '=', 'repo_metrics.open_source_alternative_id')
                ->orderByDesc('repo_metrics.github_stars')
                ->select('open_source_alternatives.*'),
            'votes' => $query->orderByDesc('votes_count')->orderByDesc('overall_health_score'),
            'newest' => $query->orderByDesc('created_at'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('overall_health_score'),
        };

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        $tools = collect(QueryCache::toolsWithAlternatives())
            ->map(fn ($row) => (object) $row);

        $seo = app(SeoManager::class);

        return view('livewire.open-source-finder', [
            'alternatives' => $this->alternatives,
            'availableLicenses' => QueryCache::licenseNames(),
            'availableLanguages' => QueryCache::publishedLanguages(),
            'difficultyLabels' => [
                1 => 'Very Easy',
                2 => 'Easy',
                3 => 'Moderate',
                4 => 'Hard',
                5 => 'Expert',
            ],
            'chipCategories' => QueryCache::categoryNames(),
            'tools' => $tools,
        ])->layout('layouts.app', [
            'title' => $seo->pageTitle('finder', 'Open Source Alternatives Finder'),
            'description' => 'Discover high-quality, self-hostable open-source alternatives. Filter by category, language, and license.',
            'canonical' => route('finder'),
        ]);
    }
}
