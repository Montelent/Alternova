<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;

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
    public string $sort = 'health';

    public int $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'licenses' => ['except' => []],
        'difficulties' => ['except' => []],
        'categories' => ['except' => []],
        'sort' => ['except' => 'health'],
    ];

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

    #[Computed]
    public function alternatives()
    {
        $query = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric', 'tags'])
            ->where('is_published', true);

        // Scout / Meilisearch full-text search when query present
        if (strlen($this->search) >= 2) {
            $searchResults = OpenSourceAlternative::search($this->search)
                ->where('is_published', true)
                ->take(200)
                ->keys();

            $query->whereIn('id', $searchResults);
        }

        if (!empty($this->licenses)) {
            $query->whereIn('license_type', $this->licenses);
        }

        if (!empty($this->difficulties)) {
            $query->whereIn('self_host_difficulty', $this->difficulties);
        }

        if (!empty($this->categories)) {
            $query->withAnyTags($this->categories, 'category');
        }

        $query = match ($this->sort) {
            'stars' => $query->join('repo_metrics', 'open_source_alternatives.id', '=', 'repo_metrics.open_source_alternative_id')
                ->orderByDesc('repo_metrics.github_stars')
                ->select('open_source_alternatives.*'),
            'newest' => $query->orderByDesc('created_at'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('overall_health_score'),
        };

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.open-source-finder', [
            'alternatives' => $this->alternatives,
            'availableLicenses' => ['MIT', 'Apache-2.0', 'AGPL-3.0', 'GPL-3.0', 'BSD-3-Clause', 'MPL-2.0'],
            'difficultyLabels' => [
                1 => 'Very Easy',
                2 => 'Easy',
                3 => 'Moderate',
                4 => 'Hard',
                5 => 'Expert',
            ],
        ])->layout('layouts.app', ['title' => 'Open Source Alternative Finder']);
    }
}
