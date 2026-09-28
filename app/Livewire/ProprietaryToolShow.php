<?php

namespace App\Livewire;

use App\Models\ProprietaryTool;
use App\Services\SeoManager;
use Livewire\Component;

class ProprietaryToolShow extends Component
{
    public ProprietaryTool $tool;

    public function mount(ProprietaryTool $tool): void
    {
        abort_unless($tool->is_published, 404);

        $this->tool = $tool->load([
            'publishedAlternatives' => fn ($q) => $q
                ->with(['repoMetric', 'tags'])
                ->orderByDesc('overall_health_score'),
        ]);
    }

    public function render()
    {
        $tool = $this->tool;
        $seo = app(SeoManager::class);

        return view('livewire.proprietary-tool-show', [
            'alternatives' => $tool->publishedAlternatives,
        ])->layout('layouts.app', [
            'title' => $seo->toolTitle($tool),
            'description' => $seo->toolDescription($tool),
            'canonical' => route('tools.show', $tool),
            'ogType' => 'article',
        ]);
    }
}
