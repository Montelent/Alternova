<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlternativeProsConResource\Pages;
use App\Models\AlternativeProsCon;
use App\Models\OpenSourceAlternative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class AlternativeProsConResource extends Resource
{
    protected static ?string $model = AlternativeProsCon::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?string $navigationLabel = 'Pros & Cons';

    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        try {
            return Schema::hasTable('alternative_pros_cons');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('open_source_alternative_id')
                ->label('Alternative')
                ->options(fn () => OpenSourceAlternative::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\Select::make('type')
                ->options(['pro' => 'Pro', 'con' => 'Con'])
                ->required(),
            Forms\Components\Textarea::make('body')
                ->required()
                ->maxLength(280)
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('author_name')->maxLength(80),
            Forms\Components\Toggle::make('is_approved')->label('Approved')->default(true),
            Forms\Components\Toggle::make('is_featured')->label('Featured'),
            Forms\Components\TextInput::make('votes_count')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('alternative.name')->label('Alternative')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors(['success' => 'pro', 'danger' => 'con']),
                Tables\Columns\TextColumn::make('body')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('votes_count')->label('Votes')->sortable(),
                Tables\Columns\IconColumn::make('is_approved')->boolean()->label('OK'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('★'),
                Tables\Columns\TextColumn::make('author_name')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(['pro' => 'Pro', 'con' => 'Con']),
                Tables\Filters\Filter::make('pending')
                    ->label('Pending approval')
                    ->query(fn (Builder $query) => $query->where('is_approved', false))
                    ->default(),
                Tables\Filters\TernaryFilter::make('is_approved'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (AlternativeProsCon $record) => ! $record->is_approved)
                    ->action(fn (AlternativeProsCon $record) => $record->update(['is_approved' => true])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approveSelected')
                        ->label('Approve')
                        ->action(fn ($records) => $records->each->update(['is_approved' => true])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlternativeProsCons::route('/'),
            'create' => Pages\CreateAlternativeProsCon::route('/create'),
            'edit' => Pages\EditAlternativeProsCon::route('/{record}/edit'),
        ];
    }
}
