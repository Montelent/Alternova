<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'API keys';

    protected static ?int $navigationSort = 12;

    public static function tableReady(): bool
    {
        try {
            return Schema::hasTable('api_keys');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::tableReady();
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
                Tables\Columns\TextColumn::make('user.email')->label('User')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('key_prefix')->label('Prefix')->fontFamily('mono'),
                Tables\Columns\TextColumn::make('request_count')->label('Requests')->sortable(),
                Tables\Columns\TextColumn::make('last_used_at')->dateTime()->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('revoked_at')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Revoked' : 'Active')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Active only')
                    ->query(fn ($q) => $q->whereNull('revoked_at')),
                Tables\Filters\Filter::make('revoked')
                    ->label('Revoked only')
                    ->query(fn ($q) => $q->whereNotNull('revoked_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ApiKey $record) => $record->isActive())
                    ->action(function (ApiKey $record) {
                        $record->revoke();
                        Notification::make()->title('API key revoked')->success()->send();
                    }),
                Tables\Actions\Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ApiKey $record) => ! $record->isActive())
                    ->action(function (ApiKey $record) {
                        $record->forceFill(['revoked_at' => null])->save();
                        Notification::make()->title('API key reactivated')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('revokeSelected')
                        ->label('Revoke selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->isActive()) {
                                    $record->revoke();
                                }
                            }
                            Notification::make()->title('Selected keys revoked')->success()->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiKeys::route('/'),
        ];
    }
}
