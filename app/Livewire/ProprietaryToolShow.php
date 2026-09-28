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
        $title = $seo->toolTitle($tool);
        $description = $seo->toolDescription($tool);
        $canonical = $tool->canonical_url ?: route('tools.show', $tool);

        return view('livewire.proprietary-tool-show', [
            'alternatives' => $tool->publishedAlternatives,
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $tool->robots_meta ?: null,
            'ogType' => 'article',
            'ogTitle' => $tool->og_title ?: $title,
            'ogDescription' => $tool->og_description ?: $description,
            'ogImage' => $tool->og_image_url
                ?: ($tool->logo_path ? url($tool->logo_path) : null),
        ]);
    }
}
