<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DomainSearchLogResource\Pages;
use App\Models\DomainSearchLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class DomainSearchLogResource extends Resource
{
    protected static ?string $model = DomainSearchLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?string $navigationLabel = 'Domain analytics';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('seed_keywords')->disabled()->formatStateUsing(
                fn ($state) => is_array($state) ? implode(', ', $state) : $state
            ),
            Forms\Components\Textarea::make('selected_tlds')->disabled()->formatStateUsing(
                fn ($state) => is_array($state) ? implode(', ', $state) : $state
            ),
            Forms\Components\TextInput::make('domain_generated_count')->disabled(),
            Forms\Components\TextInput::make('available_count')->disabled(),
            Forms\Components\TextInput::make('ip_address')->disabled(),
            Forms\Components\DateTimePicker::make('created_at')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('When'),
                Tables\Columns\TextColumn::make('seed_keywords')
                    ->label('Keywords')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->searchable()
                    ->wrap()
                    ->limit(40),
                Tables\Columns\TextColumn::make('selected_tlds')
                    ->label('TLDs')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('domain_generated_count')->label('Generated')->sortable(),
                Tables\Columns\TextColumn::make('available_count')->label('Available')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDomainSearchLogs::route('/'),
            'view' => Pages\ViewDomainSearchLog::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        try {
            return Schema::hasTable('domain_search_logs');
        } catch (\Throwable) {
            return false;
        }
    }
}
