<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use App\Services\SupportTicketService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Support tickets';

    protected static ?string $modelLabel = 'Support ticket';

    protected static ?string $pluralModelLabel = 'Support tickets';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        try {
            if (! Schema::hasTable('contact_messages')) {
                return null;
            }
            $q = ContactMessage::query()->where('status', 'unread');
            if (Schema::hasColumn('contact_messages', 'ticket_status')) {
                $q->orWhereIn('ticket_status', ['open', 'awaiting_staff']);
            }
            $count = $q->count();

            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('public_id')->label('Ticket ID')->disabled(),
            Forms\Components\TextInput::make('name')->disabled(),
            Forms\Components\TextInput::make('email')->disabled(),
            Forms\Components\TextInput::make('subject')->disabled(),
            Forms\Components\Textarea::make('message')->rows(6)->disabled()->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->options([
                    'unread' => 'Unread',
                    'read' => 'Read',
                    'archived' => 'Archived',
                ]),
            Forms\Components\Select::make('ticket_status')
                ->label('Ticket status')
                ->options([
                    'open' => 'Open',
                    'awaiting_staff' => 'Awaiting staff',
                    'awaiting_user' => 'Awaiting user',
                    'closed' => 'Closed',
                ])
                ->visible(fn () => Schema::hasColumn('contact_messages', 'ticket_status')),
            Forms\Components\TextInput::make('ip_address')->disabled(),
            Forms\Components\DateTimePicker::make('created_at')->disabled(),
            Forms\Components\DateTimePicker::make('last_reply_at')->disabled()
                ->visible(fn () => Schema::hasColumn('contact_messages', 'last_reply_at')),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('public_id')->label('Ticket')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Received'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('subject')->limit(30)->searchable(),
                Tables\Columns\TextColumn::make('ticket_status')
                    ->label('Ticket')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'open' => 'warning',
                        'awaiting_staff' => 'danger',
                        'awaiting_user' => 'info',
                        'closed' => 'gray',
                        default => 'gray',
                    })
                    ->visible(fn () => Schema::hasColumn('contact_messages', 'ticket_status')),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unread' => 'warning',
                        'read' => 'success',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unread' => 'Unread',
                        'read' => 'Read',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('ticket_status')
                    ->label('Ticket status')
                    ->options([
                        'open' => 'Open',
                        'awaiting_staff' => 'Awaiting staff',
                        'awaiting_user' => 'Awaiting user',
                        'closed' => 'Closed',
                    ])
                    ->visible(fn () => Schema::hasColumn('contact_messages', 'ticket_status')),
            ])
            ->actions([
                Tables\Actions\Action::make('markRead')
                    ->icon('heroicon-o-check')
                    ->visible(fn (ContactMessage $r) => $r->status === 'unread')
                    ->action(fn (ContactMessage $r) => $r->markRead()),
                Tables\Actions\Action::make('reply')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->form([
                        Forms\Components\Textarea::make('body')
                            ->label('Reply to user')
                            ->required()
                            ->rows(5),
                        Forms\Components\Toggle::make('internal')
                            ->label('Internal note only (not emailed to user)')
                            ->default(false),
                    ])
                    ->action(function (ContactMessage $record, array $data) {
                        $reply = app(SupportTicketService::class)->replyAsStaff(
                            $record,
                            $data['body'],
                            auth()->user(),
                            (bool) ($data['internal'] ?? false)
                        );
                        if ($reply) {
                            Notification::make()->title('Reply posted')->success()->send();
                        } else {
                            Notification::make()->title('Reply failed — run migrations for contact_message_replies')->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('close')
                    ->icon('heroicon-o-lock-closed')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (ContactMessage $record) {
                        app(SupportTicketService::class)->close($record);
                        Notification::make()->title('Ticket closed')->success()->send();
                    }),
                Tables\Actions\ViewAction::make()
                    ->after(function (ContactMessage $record) {
                        if ($record->status === 'unread') {
                            $record->markRead();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markRead')
                        ->action(fn ($records) => $records->each->markRead()),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
            'edit' => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        try {
            return Schema::hasTable('contact_messages');
        } catch (\Throwable) {
            return true;
        }
    }
}
