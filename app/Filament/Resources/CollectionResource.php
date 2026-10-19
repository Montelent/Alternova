<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TinyEditor;
use App\Filament\Forms\SeoForm;
use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                    ->helperText('Short plain-text blurb on cards and under the title (not HTML).'),
                TinyEditor::make('intro_html')
                    ->label('Intro')
                    ->height(360)
                    ->columnSpanFull()
                    ->helperText('Rich intro shown on the public collection page.'),
                Forms\Components\FileUpload::make('cover_path')
                    ->label('Cover image (upload)')
                    ->image()
                    ->imageEditor()
                    ->directory('covers')
                    ->visibility('public')
                    ->maxSize(5120)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('cover_image_url')
                    ->label('Cover image URL')
                    ->url()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')->label('Published')->default(false),
                Forms\Components\Toggle::make('is_featured')->label('Featured')->default(false),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->nullable()
                    ->placeholder('Auto')
                    ->helperText('Leave empty for next number. Setting a used number swaps with that item.'),
            ])->columns(2),

            Forms\Components\Section::make('Items')
                ->description('Add open-source alternatives. Drag rows to set order (position is saved automatically).')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('open_source_alternative_id')
                                ->label('Open-source alternative')
                                ->options(function (Get $get) {
                                    return OpenSourceAlternative::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all();
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->distinct()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('note')
                                ->label('Note (optional)')
                                ->rows(2)
                                ->maxLength(500)
                                ->columnSpanFull(),
                        ])
                        ->orderColumn('position')
                        ->reorderable()
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->cloneable()
                        ->itemLabel(function (array $state): ?string {
                            $id = $state['open_source_alternative_id'] ?? null;
                            if (! $id) {
                                return 'New item';
                            }
                            static $cache = [];
                            if (! isset($cache[$id])) {
                                $cache[$id] = OpenSourceAlternative::query()->whereKey($id)->value('name') ?: ('#'.$id);
                            }

                            return $cache[$id];
                        })
                        ->defaultItems(0)
                        ->addActionLabel('Add alternative')
                        ->columnSpanFull(),
                ]),

            ...SeoForm::schema('collection', 'slug', 'name', 'description'),
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
                    ->url(fn (Collection $record) => route('collections.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Collection $record) => (bool) $record->is_published),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
