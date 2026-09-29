<?php

namespace App\Livewire;

use App\Services\FavoriteService;
use App\Services\SavedDomainService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AccountPage extends Component
{
    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function removeFavorite(int $id): void
    {
        $alt = \App\Models\OpenSourceAlternative::query()->find($id);
        if ($alt) {
            app(FavoriteService::class)->toggle($alt);
        }
    }

    public function removeDomain(int $id): void
    {
        $row = \App\Models\SavedDomain::query()->find($id);
        if ($row && (int) $row->user_id === (int) Auth::id()) {
            $row->delete();
        }
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.account-page', [
            'user' => $user,
            'favorites' => app(FavoriteService::class)->list(),
            'domains' => app(SavedDomainService::class)->list(),
        ])->layout('layouts.app', [
            'title' => 'Your account | Alternova',
            'description' => 'Manage favorites and saved domains.',
            'robots' => 'noindex,follow',
        ]);
    }
}
