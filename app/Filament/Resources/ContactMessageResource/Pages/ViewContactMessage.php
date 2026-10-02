<?php

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use App\Services\SupportTicketService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Schema;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($this->record->status ?? '') === 'unread') {
            $this->record->markRead();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openPublic')
                ->label('Public ticket link')
                ->url(fn () => $this->record->publicUrl())
                ->openUrlInNewTab()
                ->visible(fn () => filled($this->record->public_id)),
            Actions\Action::make('reply')
                ->label('Reply to user')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->form([
                    Forms\Components\Textarea::make('body')
                        ->label('Message')
                        ->required()
                        ->rows(6),
                    Forms\Components\Toggle::make('internal')
                        ->label('Internal note (not sent to user)')
                        ->default(false),
                ])
                ->action(function (array $data) {
                    $reply = app(SupportTicketService::class)->replyAsStaff(
                        $this->record,
                        $data['body'],
                        auth()->user(),
                        (bool) ($data['internal'] ?? false)
                    );
                    if ($reply) {
                        Notification::make()->title('Reply posted & user notified')->success()->send();
                        $this->refreshFormData(['status', 'ticket_status', 'last_reply_at']);
                    } else {
                        Notification::make()
                            ->title('Could not reply')
                            ->body('Run migrations so contact_message_replies exists.')
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('close')
                ->label('Close ticket')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function () {
                    app(SupportTicketService::class)->close($this->record);
                    Notification::make()->title('Ticket closed')->success()->send();
                }),
            Actions\EditAction::make(),
        ];
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        if (! Schema::hasTable('contact_message_replies')) {
            return null;
        }

        $replies = $this->record->replies()->with('user')->get();

        return view('filament.resources.contact-message-replies', [
            'ticket' => $this->record,
            'replies' => $replies,
        ]);
    }
}
