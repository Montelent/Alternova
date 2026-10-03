<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TinyEditor;
use App\Filament\Forms\ImageField;
use App\Filament\Forms\SeoForm;
use App\Filament\Forms\TagsField;
use App\Filament\Resources\OpenSourceAlternativeResource\Pages;
use App\Jobs\SyncGitHubMetricsJob;
use App\Models\LicenseType;
use App\Models\OpenSourceAlternative;
use App\Models\OssCategory;
use App\Models\ProprietaryTool;
use App\Services\DescriptionGeneratorService;
use App\Services\LinkHealthService;
use App\Support\CategoryCatalog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OpenSourceAlternativeResource extends Resource
{
    protected static ?string $model = OpenSourceAlternative::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'repo_url', 'description', 'license_type', 'primary_language', 'focus_keyword'];
    }

    public static function form(Form $form): Form
    {
        $categoryOptions = array_combine(
            ($names = OssCategory::activeNames()) ?: CategoryCatalog::names(),
            $names ?: CategoryCatalog::names()
        );

        $licenseOptions = [];
        foreach (LicenseType::activeNames() as $lic) {
            $licenseOptions[$lic] = $lic;
        }

        $hasPivot = false;
        try {
            $hasPivot = Schema::hasTable('alternative_proprietary_tool');
        } catch (\Throwable) {
        }

        $toolFields = [];
        if ($hasPivot) {
            $toolFields[] = Forms\Components\Select::make('proprietaryTools')
                ->label('Proprietary tools (up to 5)')
                ->relationship('proprietaryTools', 'name')
                ->multiple()
                ->maxItems(5)
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull()
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    $ids = is_array($state) ? array_values($state) : [];
                    $set('proprietary_tool_id', $ids[0] ?? null);
                });
            $toolFields[] = Forms\Components\Hidden::make('proprietary_tool_id');
        } else {
            $toolFields[] = Forms\Components\Select::make('proprietary_tool_id')
                ->label('Proprietary tool')
                ->relationship('proprietaryTool', 'name')
                ->required()
                ->searchable()
                ->preload();
        }

        return $form->schema([
            Forms\Components\Section::make('Core')->schema([
                ...$toolFields,
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('repo_url')->url()->required(),
                Forms\Components\TextInput::make('website_url')->url(),
                TinyEditor::make('description')->label('Description')->height(320)->columnSpanFull(),
                Forms\Components\Select::make('category_tags')
                    ->label('Categories')
                    ->multiple()
                    ->options($categoryOptions)
                    ->searchable()
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Logo')->schema([
                ...ImageField::make('logo_path', 'Logo', 'logos', true, 'external_logo_url'),
            ]),

            Forms\Components\Section::make('Publishing')->schema([
                Forms\Components\Toggle::make('is_published')->label('Published')->default(false),
                Forms\Components\Toggle::make('is_featured')->label('Featured')->default(false),
                Forms\Components\Toggle::make('is_sponsored')->label('Sponsored')->default(false),
            ])->columns(3),

            Forms\Components\Section::make('Technical')->schema([
                Forms\Components\Select::make('license_type')->options($licenseOptions)->searchable(),
                Forms\Components\Select::make('self_host_difficulty')
                    ->options([1 => '1 - Very Easy', 2 => '2 - Easy', 3 => '3 - Moderate', 4 => '4 - Hard', 5 => '5 - Expert'])
                    ->default(3),
                Forms\Components\TextInput::make('primary_language'),
                Forms\Components\TextInput::make('overall_health_score')->numeric()->disabled(),
                TagsField::make('pros', 'Pros')->columnSpanFull(),
                TagsField::make('cons', 'Cons')->columnSpanFull(),
                TinyEditor::make('editor_note')->label('Editor note')->height(220)->columnSpanFull(),
            ])->columns(2),

            ...SeoForm::schema('open-source alternative', 'slug'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->getStateUsing(fn ($record) => $record->logo_url)
                    ->circular()
                    ->height(40)
                    ->width(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('proprietaryTool.name')->label('Primary tool')->toggleable(),
                Tables\Columns\TextColumn::make('license_type')->toggleable(),
                Tables\Columns\TextColumn::make('overall_health_score')->sortable()->label('Health'),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Published'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured')->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->label('Updated')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Created')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => true])),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => false])),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOpenSourceAlternatives::route('/'),
            'create' => Pages\CreateOpenSourceAlternative::route('/create'),
            'edit' => Pages\EditOpenSourceAlternative::route('/{record}/edit'),
        ];
    }
}
