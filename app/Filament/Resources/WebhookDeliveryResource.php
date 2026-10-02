<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookDeliveryResource\Pages;
use App\Models\WebhookDelivery;
use App\Services\WebhookDispatcher;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class WebhookDeliveryResource extends Resource
{
    protected static ?string $model = WebhookDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Webhook log';

    protected static ?int $navigationSort = 16;

    public static function tableReady(): bool
    {
        try {
            return Schema::hasTable('webhook_deliveries');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::tableReady() && (auth()->user()?->canManageSystem() ?? false);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('When')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('webhook.name')->label('Webhook')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('event')->badge()->searchable(),
                Tables\Columns\IconColumn::make('success')->boolean()->label('OK'),
                Tables\Columns\TextColumn::make('status_code')->label('HTTP')->placeholder('—'),
                Tables\Columns\TextColumn::make('error')->limit(40)->toggleable()->placeholder('—'),
                Tables\Columns\TextColumn::make('response_body')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('success')->label('Successful'),
                Tables\Filters\Filter::make('failed')
                    ->label('Failed only')
                    ->query(fn ($q) => $q->where('success', false)),
            ])
            ->actions([
                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Retry this delivery?')
                    ->modalDescription('Sends the stored payload again to the same webhook URL.')
                    ->action(function (WebhookDelivery $record) {
                        try {
                            $updated = app(WebhookDispatcher::class)->retry($record);
                            Notification::make()
                                ->title($updated->success ? 'Retry succeeded' : 'Retry failed')
                                ->body($updated->error ?: ('HTTP '.($updated->status_code ?? '—')))
                                ->{$updated->success ? 'success' : 'danger'}()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Retry error')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('retryFailed')
                        ->label('Retry selected')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $ok = 0;
                            $fail = 0;
                            $dispatcher = app(WebhookDispatcher::class);
                            foreach ($records as $record) {
                                try {
                                    $updated = $dispatcher->retry($record);
                                    $updated->success ? $ok++ : $fail++;
                                } catch (\Throwable) {
                                    $fail++;
                                }
                            }
                            Notification::make()
                                ->title("Retries: {$ok} ok, {$fail} failed")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhookDeliveries::route('/'),
            'view' => Pages\ViewWebhookDelivery::route('/{record}'),
        ];
    }
}
