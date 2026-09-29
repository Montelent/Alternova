<?php

namespace App\Livewire;

use App\Services\FavoriteService;
use Illuminate\Support\Facades\Auth;
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
        $synced = Auth::check();

        return view('livewire.favorites-page', [
            'favorites' => $favorites,
            'synced' => $synced,
        ])->layout('layouts.app', [
            'title' => 'Your favorites | Alternova',
            'description' => $synced
                ? 'Open-source alternatives saved to your Alternova account.'
                : 'Open-source alternatives saved on this device. Sign in to sync across devices.',
            'robots' => 'noindex,follow',
        ]);
    }
}
