<?php

namespace App\Livewire;

use App\Services\FavoriteService;
use Livewire\Component;

class FavoritesPage extends Component
{
    public function clearAll(): void
    {
        app(FavoriteService::class)->clear();
    }

    public function remove(int $id): void
    {
        $alt = \App\Models\OpenSourceAlternative::query()->find($id);
        if ($alt) {
            app(FavoriteService::class)->toggle($alt);
        }
    }

    public function render()
    {
        $favorites = app(FavoriteService::class)->list();

        return view('livewire.favorites-page', [
            'favorites' => $favorites,
        ])->layout('layouts.app', [
            'title' => 'Your favorites | Alternova',
            'description' => 'Open-source alternatives you saved on this device.',
            'robots' => 'noindex,follow',
        ]);
    }
}
