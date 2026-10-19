<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TinyEditor;
use App\Filament\Forms\ImageField;
use App\Filament\Forms\SeoForm;
use App\Filament\Forms\TagsField;
use App\Filament\Resources\ProprietaryToolResource\Pages;
use App\Models\ProprietaryTool;
use App\Services\DescriptionGeneratorService;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProprietaryToolResource extends Resource
{
    protected static ?string $model = ProprietaryTool::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description', 'target_audience', 'focus_keyword'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic information')->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('website_url')->url()->columnSpanFull(),
                    ...ImageField::make('logo_path', 'Logo', 'logos', withUrlFallback: false),
                    TinyEditor::make('description')
                        ->label('Description')
                        ->height(300)
                        ->columnSpanFull(),
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('generateDescription')
                            ->label('Generate from website')
                            ->icon('heroicon-o-sparkles')
                            ->color('gray')
                            ->action(function (Get $get, Set $set) {
                                $url = $get('website_url');
                                if (! $url) {
                                    Notification::make()->title('Add a website URL first')->warning()->send();

                                    return;
                                }
                                $result = app(DescriptionGeneratorService::class)->fromUrl($url);
                                if ($result['description'] ?? null) {
                                    $set('description', $result['description']);
                                }
                                Notification::make()
                                    ->title(($result['success'] ?? false) ? 'Description generated' : 'Partial result')
                                    ->body($result['message'] ?? '')
                                    ->{($result['success'] ?? false) ? 'success' : 'warning'}()
                                    ->send();
                            }),
                    ])->columnSpanFull(),
                    Forms\Components\TextInput::make('target_audience')->maxLength(120),
                    TagsField::make('category_tags', 'Categories')->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('Publishing')->schema([
                    Forms\Components\Toggle::make('is_published')->label('Published')->default(false),
                ]),

                ...SeoForm::schema('proprietary tool', 'slug'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->getStateUsing(fn ($record) => $record->logo_url ?? MediaUrl::to($record->logo_path))
                    ->circular()
                    ->height(40)
                    ->width(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('alternatives_total_count')
                    ->label('Alternatives')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip('Includes multi-select links (pivot), not only the primary tool FK'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->label('Updated')->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewSite')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ProprietaryTool $r) => route('alternativesto.show', $r->slug), shouldOpenInNewTab: true)
                    ->visible(fn (ProprietaryTool $r) => $r->is_published && ! $r->trashed()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publishSelected')
                        ->label('Publish')
                        ->action(fn ($records) => $records->each->update(['is_published' => true])),
                    Tables\Actions\BulkAction::make('unpublishSelected')
                        ->label('Unpublish')
                        ->action(fn ($records) => $records->each->update(['is_published' => false])),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return ProprietaryTool::query()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->withAlternativesTotalCount();
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
