<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Support\CategoryCatalog;
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

    public function updatingToolSlug(): void
    {
        $this->resetPage();
    }

    public function toggleCategory(string $name): void
    {
        if (in_array($name, $this->categories, true)) {
            $this->categories = array_values(array_filter(
                $this->categories,
                fn ($c) => $c !== $name
            ));
        } else {
            $this->categories[] = $name;
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'licenses', 'difficulties', 'categories', 'toolSlug']);
        $this->sort = 'health';
        $this->resetPage();
    }

    #[Computed]
    public function alternatives()
    {
        $query = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric', 'tags'])
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

        if (! empty($this->difficulties)) {
            $query->whereIn('self_host_difficulty', $this->difficulties);
        }

        if (! empty($this->categories)) {
            $query->withAnyTags($this->categories, 'category');
        }

        if ($this->toolSlug !== '') {
            $query->whereHas('proprietaryTool', fn ($q) => $q->where('slug', $this->toolSlug));
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
        $usedCategories = [];
        try {
            $usedCategories = Tag::query()->where('type', 'category')->orderBy('name')->pluck('name')->all();
        } catch (\Throwable) {
        }

        $tools = ProprietaryTool::query()
            ->where('is_published', true)
            ->whereHas('publishedAlternatives')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('livewire.open-source-finder', [
            'alternatives' => $this->alternatives,
            'availableLicenses' => ['MIT', 'Apache-2.0', 'AGPL-3.0', 'GPL-3.0', 'BSD-3-Clause', 'MPL-2.0', 'BSL-1.1'],
            'difficultyLabels' => [
                1 => 'Very Easy',
                2 => 'Easy',
                3 => 'Moderate',
                4 => 'Hard',
                5 => 'Expert',
            ],
            'chipCategories' => $usedCategories ?: CategoryCatalog::names(),
            'tools' => $tools,
        ])->layout('layouts.app', [
            'title' => 'Open Source Alternative Finder | Alternova',
            'description' => 'Discover high-quality, self-hostable open-source alternatives. Filter by license, difficulty, and category.',
        ]);
    }
}
