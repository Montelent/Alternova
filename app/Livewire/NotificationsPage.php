<?php

namespace App\Livewire;

use App\Services\UserNotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsPage extends Component
{
    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function markRead(int $id): void
    {
        app(UserNotificationService::class)->markRead($id, Auth::user());
    }

    public function markAllRead(): void
    {
        app(UserNotificationService::class)->markAllRead(Auth::user());
    }

    public function render()
    {
        $user = Auth::user();
        $service = app(UserNotificationService::class);

        return view('livewire.notifications-page', [
            'notifications' => $service->listFor($user),
            'unread' => $service->unreadCount($user),
        ])->layout('layouts.app', [
            'title' => 'Notifications | Alternova',
            'description' => 'Comment replies and health-drop alerts.',
            'robots' => 'noindex,follow',
        ]);
    }
}
