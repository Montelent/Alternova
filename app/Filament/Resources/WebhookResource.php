<?php

namespace App\\Filament\\Resources;

use App\\Filament\\Resources\\WebhookResource\\Pages;
use App\\Models\\Webhook;
use App\\Services\\WebhookDispatcher;
use Filament\\Forms;
use Filament\\Forms\\Form;
use Filament\\Notifications\\Notification;
use Filament\\Resources\\Resource;
use Filament\\Tables;
use Filament\\Tables\\Table;
use Illuminate\\Support\\Facades\\Schema;
use Illuminate\\Support\\Str;

class WebhookResource extends Resource
{
    protected static ?string $model = Webhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Webhooks';

    protected static ?int $navigationSort = 20;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (! (auth()->user()?->canManageSystem() ?? false)) {
            return false;
        }

        try {
            return Schema::hasTable('webhooks');
        } catch (\\Throwable) {
            return false;
        }
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\\Components\\TextInput::make('name')->required()->maxLength(120),
            Forms\\Components\\TextInput::make('url')
                ->label('Endpoint URL')
                ->url()
                ->required()
                ->maxLength(500)
                ->columnSpanFull()
                ->helperText('POST JSON. Signature header X-Alternova-Signature: sha256=… when secret is set.'),
            Forms\\Components\\TextInput::make('secret')
                ->password()
                ->maxLength(120)
                ->formatStateUsing(fn () => null)
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText('Optional HMAC secret. Leave blank to keep the current secret; enter a new value to rotate it.')
                ->columnSpanFull(),
            Forms\\Components\\CheckboxList::make('events')
                ->options(Webhook::eventOptions())
                ->required()
                ->columns(1),
            Forms\\Components\\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\\Columns\\TextColumn::make('name')->searchable()->sortable(),
                Tables\\Columns\\TextColumn::make('url')->limit(40)->toggleable(),
                Tables\\Columns\\IconColumn::make('is_active')->boolean(),
                Tables\\Columns\\TextColumn::make('success_count')->label('OK'),
                Tables\\Columns\\TextColumn::make('failure_count')->label('Fail'),
                Tables\\Columns\\TextColumn::make('last_triggered_at')->dateTime()->sortable()->toggleable(),
                Tables\\Columns\\TextColumn::make('last_error')->limit(30)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\\Actions\\Action::make('test')
                    ->label('Send test')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function (Webhook $record) {
                        // Send directly to this record. Do not temporarily mutate
                        // active/events state: queued dispatch would run after the
                        // state had already been restored and silently skip the test.
                        $delivery = app(WebhookDispatcher::class)->deliver($record, 'ping', [
                            'message' => 'Alternova webhook test',
                            'webhook' => $record->name,
                            'id' => Str::uuid()->toString(),
                        ]);

                        $notification = Notification::make()
                            ->title($delivery->success ? 'Test payload delivered' : 'Test delivery failed')
                            ->body($delivery->error ?: ('HTTP '.($delivery->status_code ?? 'unknown')));

                        if ($delivery->success) {
                            $notification->success();
                        } else {
                            $notification->warning();
                        }

                        $notification->send();
                    }),
                Tables\\Actions\\EditAction::make(),
                Tables\\Actions\\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\\Actions\\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\\ListWebhooks::route('/'),
            'create' => Pages\\CreateWebhook::route('/create'),
            'edit' => Pages\\EditWebhook::route('/{record}/edit'),
        ];
    }
}
