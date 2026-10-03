<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TinyEditor;
use App\Filament\Forms\ImageField;
use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?string $navigationLabel = 'Collections';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function tableReady(): bool
    {
        try {
            return Schema::hasTable('collections');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::tableReady();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Collection')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(160)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug((string) $state))),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(180)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Short blurb shown on cards and under the title.'),
                TinyEditor::make('intro_html')
                    ->label('Intro')
                    ->height(360)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('cover_path')
                    ->label('Cover image (upload)')
                    ->image()
                    ->imageEditor()
                    ->directory('covers')
                    ->visibility('public')
                    ->maxSize(5120)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('cover_url')
                    ->label('Cover image URL')
                    ->url()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')->label('Published')->default(false),
                Forms\Components\Toggle::make('is_featured')->label('Featured')->default(false),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            ])->columns(2),

            Forms\Components\Section::make('Items')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('open_source_alternative_id')
                            ->label('Alternative')
                            ->options(fn () => OpenSourceAlternative::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                        Forms\Components\Textarea::make('note')->rows(2),
                    ])
                    ->orderColumn('sort_order')
                    ->collapsible()
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('SEO')->schema([
                Forms\Components\TextInput::make('meta_title')->maxLength(70),
                Forms\Components\Textarea::make('meta_description')->rows(2)->maxLength(160)->columnSpanFull(),
            ])->columns(2)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->toggleable(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Items'),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Published'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->label('Updated')->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('is_featured'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewSite')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Collection $r) => route('collections.show', $r->slug), shouldOpenInNewTab: true)
                    ->visible(fn (Collection $r) => $r->is_published),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->action(fn ($records) => $records->each->update(['is_published' => true])),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->action(fn ($records) => $records->each->update(['is_published' => false])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
