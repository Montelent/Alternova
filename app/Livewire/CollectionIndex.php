<?php

namespace App\Livewire;

use App\Models\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class CollectionIndex extends Component
{
    public function render()
    {
        $collections = collect();

        try {
            if (Schema::hasTable('collections')) {
                $collections = Collection::query()
                    ->where('is_published', true)
                    ->withCount(['items'])
                    ->orderByDesc('is_featured')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
            }
        } catch (\Throwable) {
        }

        return view('livewire.collection-index', [
            'collections' => $collections,
        ])->layout('layouts.app', [
            'title' => 'Curated open-source collections | Alternova',
            'description' => 'Editor-curated lists of self-hostable open-source alternatives — best picks by category and use case.',
            'canonical' => route('collections.index'),
        ]);
    }
}
