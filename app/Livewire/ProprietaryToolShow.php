<?php

namespace App\Livewire;

use App\Models\ProprietaryTool;
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
        $title = ($tool->meta_title ?: $tool->name.' open-source alternatives').' | Alternova';
        $description = $tool->meta_description
            ?: str('Discover free, self-hostable open-source alternatives to '.$tool->name.'. '.($tool->description ?? ''))->limit(155)->toString();

        return view('livewire.proprietary-tool-show', [
            'alternatives' => $tool->publishedAlternatives,
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => route('tools.show', $tool),
        ]);
    }
}
