<?php

namespace App\Livewire;

use App\Services\SeoManager;
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

    public function render()
    {
        $robots = app(SeoManager::class)->privateRobots();

        return view('livewire.account-page')
            ->layout('layouts.app', [
                'title' => 'Your account | Alternova',
                'description' => 'Manage your Alternova account, favorites, and watchlist.',
                'robots' => $robots,
                'canonical' => route('account'),
            ]);
    }
}
