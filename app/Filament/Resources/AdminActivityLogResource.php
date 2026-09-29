<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminActivityLogResource\Pages;
use App\Models\AdminActivityLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class AdminActivityLogResource extends Resource
{
    protected static ?string $model = AdminActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Activity log';

    protected static ?int $navigationSort = 15;

    public static function tableReady(): bool
    {
        try {
            return Schema::hasTable('admin_activity_logs');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::tableReady();
    }

    public static function canAccess(): bool
    {
        return static::tableReady() && (auth()->user()?->canManageSystem() ?? false);
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
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('When'),
                Tables\Columns\TextColumn::make('user.name')->label('Admin')->placeholder('System'),
                Tables\Columns\TextColumn::make('action')->badge()->searchable(),
                Tables\Columns\TextColumn::make('subject_label')->label('Subject')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('ip_address')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                        'published' => 'Published',
                        'synced' => 'Synced',
                        'exported' => 'Exported',
                    ]),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminActivityLogs::route('/'),
        ];
    }
}
