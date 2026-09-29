<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->redirect(route('home'), navigate: true);
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <button type="button" wire:click="logout" class="text-sm text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                Sign out
            </button>
        </div>
        HTML;
    }
}
