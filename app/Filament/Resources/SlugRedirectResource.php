<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlugRedirectResource\Pages;
use App\Models\SlugRedirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class SpugRedirectResource extends Resource
{
    // typo guard — real class below
}

class SlugRedirectResource extends Resource
{
    protected static ?string $model = SlugRedirect::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-right';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'URL redirects';

    protected static ?int $navigationSort = 14;

    public static function tableReady(): bool
    {
        try {
            return Schema::hasTable('slug_redirects');
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('301 redirect')
                ->description('When someone visits /alternatives/{old_slug}, they are sent to the current page.')
                ->schema([
                    Forms\Components\TextInput::make('old_slug')
                        ->label('Old slug')
                        ->required()
                        ->maxLength(190)
                        ->helperText('Example: appflowy-old'),
                    Forms\Components\TextInput::make('new_slug')
                        ->label('New slug')
                        ->required()
                        ->maxLength(190)
                        ->helperText('Example: appflowy'),
                    Forms\Components\Select::make('model_type')
                        ->label('Applies to')
                        ->options([
                            'alternative' => 'Open-source alternative',
                            'tool' => 'Proprietary tool',
                        ])
                        ->default('alternative')
                        ->required(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('old_slug')->searchable()->sortable()->label('From'),
                Tables\Columns\TextColumn::make('new_slug')->searchable()->sortable()->label('To'),
                Tables\Columns\TextColumn::make('model_type')->badge()->label('Type'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('model_type')
                    ->options([
                        'alternative' => 'Alternative',
                        'tool' => 'Tool',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSlugRedirects::route('/'),
            'create' => Pages\CreateSlugRedirect::route('/create'),
            'edit' => Pages\EditSlugRedirect::route('/{record}/edit'),
        ];
    }
}
