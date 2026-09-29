<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlternativeCommentResource\Pages;
use App\Models\AlternativeComment;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class AlternativeCommentResource extends Resource
{
    protected static ?string $model = AlternativeComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Comments';

    protected static ?int $navigationSort = 8;

    public static function tableReady(): bool
    {
        try {
            return Schema::hasTable('alternative_comments');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::tableReady();
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            if (! static::tableReady()) {
                return null;
            }
            $n = AlternativeComment::query()->where('is_approved', false)->where('is_hidden', false)->count();

            return $n > 0 ? (string) $n : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
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
                Tables\Columns\TextColumn::make('alternative.name')->label('Alternative')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('author_name')->label('Author')->searchable(),
                Tables\Columns\TextColumn::make('body')->limit(60)->wrap(),
                Tables\Columns\TextColumn::make('parent_id')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state ? 'Reply' : 'Top-level')
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray'),
                Tables\Columns\TextColumn::make('parent.author_name')
                    ->label('In reply to')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_approved')->boolean()->label('Approved'),
                Tables\Columns\IconColumn::make('is_hidden')->boolean()->label('Hidden'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('pending')
                    ->label('Pending approval')
                    ->query(fn ($q) => $q->where('is_approved', false)->where('is_hidden', false))
                    ->default(),
                Tables\Filters\Filter::make('replies')
                    ->label('Replies only')
                    ->query(fn ($q) => $q->whereNotNull('parent_id')),
                Tables\Filters\Filter::make('top_level')
                    ->label('Top-level only')
                    ->query(fn ($q) => $q->whereNull('parent_id')),
                Tables\Filters\TernaryFilter::make('is_approved')->label('Approved'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (AlternativeComment $r) => ! $r->is_approved || $r->is_hidden)
                    ->action(function (AlternativeComment $record) {
                        $record->approve();
                        Notification::make()->title('Comment approved')->success()->send();
                    }),
                Tables\Actions\Action::make('hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn (AlternativeComment $r) => ! $r->is_hidden)
                    ->requiresConfirmation()
                    ->action(function (AlternativeComment $record) {
                        $record->hide();
                        Notification::make()->title('Comment hidden')->success()->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approveSelected')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $r) {
                                $r->approve();
                            }
                            Notification::make()->title('Comments approved')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('hideSelected')
                        ->label('Hide')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $r) {
                                $r->hide();
                            }
                            Notification::make()->title('Comments hidden')->success()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlternativeComments::route('/'),
        ];
    }
}
