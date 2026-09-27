<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProprietaryToolResource\Pages;
use App\Models\ProprietaryTool;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProprietaryToolResource extends Resource
{
    protected static ?string $model = ProprietaryTool::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Open Source Finder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('website_url')->url()->columnSpanFull(),
                    Forms\Components\FileUpload::make('logo_path')
                        ->image()
                        ->directory('logos')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
                    Forms\Components\TagsInput::make('key_features')->columnSpanFull(),
                    Forms\Components\TextInput::make('target_audience'),
                    Forms\Components\Toggle::make('is_published')->default(false),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('open_source_alternatives_count')
                    ->counts('openSourceAlternatives')
                    ->label('Alternatives'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
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
            'index' => Pages\ListProprietaryTools::route('/'),
            'create' => Pages\CreateProprietaryTool::route('/create'),
            'edit' => Pages\EditProprietaryTool::route('/{record}/edit'),
        ];
    }
}
