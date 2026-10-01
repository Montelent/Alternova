<?php

namespace App\Livewire;

use App\Models\LicenseType;
use App\Models\OpenSourceAlternative;
use App\Models\OssCategory;
use App\Models\ProprietaryTool;
use App\Services\SeoManager;
use App\Support\CategoryCatalog;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Tags\Tag;

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
            ->with(['proprietaryTool', 'proprietaryTools', 'repoMetric', 'tags'])
            ->where('is_published', true);

        if (strlen($this->search) >= 2) {
            try {
                $searchResults = OpenSourceAlternative::search($this->search)->take(200)->keys();
                $query->whereIn('id', $searchResults);
            } catch (\Throwable) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
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
                try {
                    if (Schema::hasTable('alternative_proprietary_tool')) {
                        $q->orWhereHas('proprietaryTools', fn ($qq) => $qq->where('slug', $this->toolSlug));
                    }
                } catch (\Throwable) {
                }
            });
        }

        $hasSponsored = Schema::hasColumn('open_source_alternatives', 'is_sponsored');
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
        $chipCategories = OssCategory::activeNames();
        if ($chipCategories === []) {
            try {
                $chipCategories = Tag::query()->where('type', 'category')->orderBy('name')->pluck('name')->all();
            } catch (\Throwable) {
                $chipCategories = CategoryCatalog::names();
            }
        }

        $availableLicenses = LicenseType::activeNames();

        $availableLanguages = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->whereNotNull('primary_language')
            ->where('primary_language', '!=', '')
            ->distinct()
            ->orderBy('primary_language')
            ->pluck('primary_language')
            ->all();

        $tools = ProprietaryTool::query()
            ->where('is_published', true)
            ->whereHas('publishedAlternatives')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $seo = app(SeoManager::class);

        return view('livewire.open-source-finder', [
            'alternatives' => $this->alternatives,
            'availableLicenses' => $availableLicenses,
            'availableLanguages' => $availableLanguages,
            'difficultyLabels' => [
                1 => 'Very Easy',
                2 => 'Easy',
                3 => 'Moderate',
                4 => 'Hard',
                5 => 'Expert',
            ],
            'chipCategories' => $chipCategories,
            'tools' => $tools,
        ])->layout('layouts.app', [
            'title' => $seo->pageTitle('finder', 'Open Source Alternatives Finder'),
            'description' => 'Discover high-quality, self-hostable open-source alternatives. Filter by category, language, and license.',
            'canonical' => route('finder'),
        ]);
    }
}
