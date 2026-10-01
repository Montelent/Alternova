<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TinyEditor;
use App\Filament\Forms\SeoForm;
use App\Filament\Resources\ProprietaryToolResource\Pages;
use App\Models\ProprietaryTool;
use App\Services\DescriptionGeneratorService;
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
                    Forms\Components\FileUpload::make('logo_path')
                        ->image()
                        ->directory('logos')
                        ->columnSpanFull(),
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
                                $name = $get('name') ?? '';
                                if (! $url) {
                                    Notification::make()->title('Add a website URL first')->warning()->send();

                                    return;
                                }
                                $result = app(DescriptionGeneratorService::class)->fromWebsite($url, $name);
                                if ($result['description']) {
                                    $set('description', $result['description']);
                                }
                                Notification::make()
                                    ->title($result['success'] ? 'Description filled' : 'Template used')
                                    ->body($result['message'])
                                    ->{$result['success'] ? 'success' : 'warning'}()
                                    ->send();
                            }),
                    ])->columnSpanFull(),
                    Forms\Components\TagsInput::make('key_features')->columnSpanFull(),
                    Forms\Components\TextInput::make('target_audience'),
                    Forms\Components\Toggle::make('is_published')
                        ->label('Published')
                        ->default(true),
                ])->columns(2),

                ...SeoForm::schema('proprietary tool', 'slug'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->toggleable(),
                Tables\Columns\TextColumn::make('focus_keyword')->label('Keyphrase')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('open_source_alternatives_count')
                    ->counts('openSourceAlternatives')
                    ->label('Alternatives'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('viewSite')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ProprietaryTool $r) => route('tools.show', $r->slug), shouldOpenInNewTab: true)
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
