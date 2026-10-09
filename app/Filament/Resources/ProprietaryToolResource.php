<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TinyEditor;
use App\Filament\Forms\ImageField;
use App\Filament\Forms\SeoForm;
use App\Filament\Forms\TagsField;
use App\Filament\Resources\ProprietaryToolResource\Pages;
use App\Models\ProprietaryTool;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProprietaryToolResource extends Resource
{
    protected static ?string $model = ProprietaryTool::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withAlternativesTotalCount();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tool')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(160)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, ?string $old) {
                        if ($state && (! $old || $old === '')) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(180)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('website_url')->url()->columnSpanFull(),
                ImageField::logo('logo_path', 'Logo'),
                TinyEditor::make('description')
                    ->label('Description')
                    ->height(280)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('target_audience')
                    ->rows(2)
                    ->columnSpanFull(),
                TagsField::make('key_features', 'Key features')->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')->label('Published')->default(true),
            ])->columns(2),

            ...SeoForm::schema('proprietary tool', 'slug', 'name', 'description'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('uploads')
                    ->height(32)
                    ->width(32)
                    ->circular()
                    ->defaultImageUrl(null),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->toggleable(),
                Tables\Columns\TextColumn::make('focus_keyword')->label('Keyphrase')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('alternatives_total_count')
                    ->label('Alternatives')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip('Count includes multi-select links (not only the primary FK)'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->label('Updated')->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('viewFront')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ProprietaryTool $r) => route('alternativesto.show', $r->slug), shouldOpenInNewTab: true),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
