<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseTypeResource\Pages;
use App\Models\LicenseType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LicenseTypeResource extends Resource
{
    protected static ?string $model = LicenseType::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?string $navigationLabel = 'License types';

    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        try {
            return Schema::hasTable('license_types');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(80)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug((string) $state))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('spdx_id')->label('SPDX id')->maxLength(80),
            Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
            Forms\Components\Toggle::make('is_osi_approved')->label('OSI approved')->default(true),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('spdx_id')->label('SPDX')->toggleable(),
                Tables\Columns\IconColumn::make('is_osi_approved')->boolean()->label('OSI'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
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
            'index' => Pages\ManageLicenseTypes::route('/'),
        ];
    }
}
