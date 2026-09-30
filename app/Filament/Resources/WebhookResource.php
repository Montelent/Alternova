<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookResource\Pages;
use App\Models\Webhook;
use App\Services\WebhookDispatcher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WebhookResource extends Resource
{
    protected static ?string $model = Webhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Webhooks';

    protected static ?int $navigationSort = 20;

    public static function shouldRegisterNavigation(): bool
    {
        try {
            return Schema::hasTable('webhooks');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(120),
            Forms\Components\TextInput::make('url')
                ->label('Endpoint URL')
                ->url()
                ->required()
                ->maxLength(500)
                ->columnSpanFull()
                ->helperText('POST JSON. Signature header X-Alternova-Signature: sha256=… when secret is set.'),
            Forms\Components\TextInput::make('secret')
                ->password()
                ->revealable()
                ->helperText('Optional HMAC secret. Leave blank for unsigned payloads.')
                ->columnSpanFull(),
            Forms\Components\CheckboxList::make('events')
                ->options(Webhook::eventOptions())
                ->required()
                ->columns(1),
            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('url')->limit(40)->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('success_count')->label('OK'),
                Tables\Columns\TextColumn::make('failure_count')->label('Fail'),
                Tables\Columns\TextColumn::make('last_triggered_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('last_error')->limit(30)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('test')
                    ->label('Send test')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function (Webhook $record) {
                        $dispatcher = app(WebhookDispatcher::class);
                        $wasActive = $record->is_active;
                        $record->forceFill(['is_active' => true])->save();
                        // Temporarily only this hook: dispatch with a ping event that this hook may not listen to
                        // Force-deliver via temporary listen
                        $events = $record->events ?? [];
                        $record->forceFill(['events' => array_values(array_unique(array_merge($events, ['ping'])))])->save();
                        $dispatcher->dispatch('ping', [
                            'message' => 'Alternova webhook test',
                            'webhook' => $record->name,
                            'id' => Str::uuid()->toString(),
                        ]);
                        $record->forceFill([
                            'events' => $events,
                            'is_active' => $wasActive,
                        ])->save();

                        Notification::make()
                            ->title('Test payload sent')
                            ->body($record->fresh()->last_error ?: 'Check success/fail counters.')
                            ->{$record->fresh()->last_error ? 'warning' : 'success'}()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'edit' => Pages\EditWebhook::route('/{record}/edit'),
        ];
    }
}
