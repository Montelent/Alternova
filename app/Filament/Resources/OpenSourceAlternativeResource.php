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

    public static function getGlobalSearchResultDetails($record): array
    {
        return array_filter([
            'Proprietary' => $record->proprietaryTool?->name,
            'Health' => $record->overall_health_score,
            'Published' => $record->is_published ? 'Yes' : 'Draft',
        ]);
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
                ->helperText('Select 1–5 products this open-source project can replace. First becomes the primary.')
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

        return $form
            ->schema([
                Forms\Components\Section::make('Core')->schema([
                    ...$toolFields,
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('repo_url')
                        ->url()
                        ->required()
                        ->helperText('https://github.com/owner/repo'),
                    Forms\Components\TextInput::make('website_url')->url(),
                    TinyEditor::make('description')
                        ->label('Description')
                        ->height(320)
                        ->columnSpanFull(),
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('generateDescription')
                            ->label('Generate from GitHub')
                            ->icon('heroicon-o-sparkles')
                            ->color('gray')
                            ->action(function (Get $get, Set $set) {
                                $repo = $get('repo_url');
                                if (! $repo) {
                                    Notification::make()->title('Add a repo URL first')->warning()->send();

                                    return;
                                }
                                $propName = null;
                                if ($id = $get('proprietary_tool_id')) {
                                    $propName = ProprietaryTool::query()->find($id)?->name;
                                }
                                $result = app(DescriptionGeneratorService::class)->fromGitHubRepo($repo, $propName);
                                if ($result['description']) {
                                    $set('description', $result['description']);
                                }
                                if ($result['primary_language'] && ! $get('primary_language')) {
                                    $set('primary_language', $result['primary_language']);
                                }
                                if ($result['license_type'] && ! $get('license_type')) {
                                    $set('license_type', $result['license_type']);
                                }
                                if ($result['website_url'] && ! $get('website_url')) {
                                    $set('website_url', $result['website_url']);
                                }
                                Notification::make()
                                    ->title($result['success'] ? 'Description generated' : 'Partial result')
                                    ->body($result['message'])
                                    ->{$result['success'] ? 'success' : 'warning'}()
                                    ->send();
                            }),
                    ])->columnSpanFull(),
                    Forms\Components\Select::make('category_tags')
                        ->label('Categories')
                        ->multiple()
                        ->options($categoryOptions)
                        ->searchable()
                        ->helperText('Managed under Open Source Finder → Categories.')
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('Publishing')->schema([
                    Forms\Components\Toggle::make('is_published')->label('Published (visible on site)')->default(false),
                    Forms\Components\Toggle::make('is_featured')->label('Editor featured')->default(false),
                ])->columns(2),

                Forms\Components\Section::make('Sponsored placement')
                    ->schema([
                        Forms\Components\Toggle::make('is_sponsored')->label('Sponsored')->live()->default(false),
                        Forms\Components\DateTimePicker::make('sponsored_until')->native(false)
                            ->visible(fn (Get $get) => (bool) $get('is_sponsored')),
                        Forms\Components\TextInput::make('sponsor_label')->maxLength(40)
                            ->visible(fn (Get $get) => (bool) $get('is_sponsored')),
                    ])->columns(2)->collapsed(),

                Forms\Components\Section::make('Technical')->schema([
                    Forms\Components\Select::make('license_type')
                        ->options($licenseOptions)
                        ->searchable()
                        ->helperText('Managed under Open Source Finder → License types.'),
                    Forms\Components\Select::make('self_host_difficulty')
                        ->options([
                            1 => '1 - Very Easy',
                            2 => '2 - Easy',
                            3 => '3 - Moderate',
                            4 => '4 - Hard',
                            5 => '5 - Expert',
                        ])
                        ->default(3),
                    Forms\Components\TextInput::make('primary_language')
                        ->datalist(['PHP', 'JavaScript', 'TypeScript', 'Python', 'Go', 'Rust', 'Java', 'Ruby', 'C#', 'Swift', 'Kotlin']),
                    Forms\Components\TextInput::make('overall_health_score')->numeric()->disabled(),
                    Forms\Components\Textarea::make('docker_compose_blueprint')->rows(10)->columnSpanFull(),
                    TagsField::make('pros', 'Pros')->columnSpanFull(),
                    TagsField::make('cons', 'Cons')->columnSpanFull(),
                    TinyEditor::make('editor_note')->label('Editor note')->height(220)->columnSpanFull(),
                    TinyEditor::make('changelog')->label('Public changelog / notes')->height(280)->columnSpanFull(),
                    ImageField::multiple('gallery_paths', 'Screenshots (upload)', 'gallery', 12),
                    TagsField::make('gallery_urls', 'Extra screenshot URLs')
                        ->placeholder('https://example.com/shot.png')
                        ->helperText('Paste multiple image URLs separated by commas')
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('Link health')->schema([
                    Forms\Components\Placeholder::make('link_status')
                        ->label('Status')
                        ->content(function (?OpenSourceAlternative $record) {
                            if (! $record || ! $record->links_checked_at) {
                                return 'Not checked yet.';
                            }
                            $repo = $record->repo_reachable === null ? '—' : ($record->repo_reachable ? 'OK' : 'BROKEN');
                            $site = $record->website_reachable === null ? '—' : ($record->website_reachable ? 'OK' : 'BROKEN');

                            return 'Repo: '.$repo.' · Website: '.$site.' · '.$record->links_checked_at->diffForHumans();
                        }),
                ])->collapsed(),

                ...SeoForm::schema('open-source alternative', 'slug'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('proprietaryTool.name')->label('Primary tool')->toggleable(),
                Tables\Columns\TextColumn::make('proprietaryTools.name')
                    ->label('All tools')
                    ->badge()
                    ->separator(',')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('license_type')->toggleable(),
                Tables\Columns\TextColumn::make('primary_language')->label('Lang')->toggleable(),
                Tables\Columns\TextColumn::make('tags.name')->label('Categories')->badge()->separator(',')->toggleable(),
                Tables\Columns\TextColumn::make('overall_health_score')->sortable()->label('Health'),
                Tables\Columns\TextColumn::make('votes_count')->label('Votes')->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Published'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured')->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\TernaryFilter::make('is_sponsored'),
                Tables\Filters\Filter::make('broken_links')
                    ->label('Broken links only')
                    ->query(fn (Builder $q) => $q->where(function (Builder $inner) {
                        $inner->where('repo_reachable', false)->orWhere('website_reachable', false);
                    })),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('viewSite')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (OpenSourceAlternative $r) => route('alternatives.show', $r->slug), shouldOpenInNewTab: true)
                    ->visible(fn (OpenSourceAlternative $r) => $r->is_published && ! $r->trashed()),
                Tables\Actions\Action::make('syncMetrics')
                    ->label('Sync GitHub')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (OpenSourceAlternative $r) => ! $r->trashed())
                    ->action(function (OpenSourceAlternative $record) {
                        try {
                            SyncGitHubMetricsJob::dispatchSync($record);
                            $fresh = $record->fresh(['repoMetric']);
                            Notification::make()->title('Metrics updated')
                                ->body('Stars: '.($fresh->repoMetric?->github_stars ?? 0).' · Health: '.$fresh->overall_health_score)
                                ->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Sync failed')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('checkLinks')
                    ->label('Check links')
                    ->icon('heroicon-o-link')
                    ->visible(fn (OpenSourceAlternative $r) => ! $r->trashed())
                    ->action(function (OpenSourceAlternative $record) {
                        app(LinkHealthService::class)->checkAlternative($record);
                        Notification::make()->title('Link check done')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('Mark featured')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->label('Remove featured')
                        ->icon('heroicon-o-star')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('syncMetrics')
                        ->label('Sync GitHub metrics')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $ok = 0;
                            $fail = 0;
                            foreach ($records as $record) {
                                if ($record->trashed()) {
                                    continue;
                                }
                                try {
                                    SyncGitHubMetricsJob::dispatchSync($record);
                                    $ok++;
                                } catch (\Throwable) {
                                    $fail++;
                                }
                            }
                            Notification::make()
                                ->title('Bulk metrics sync')
                                ->body("{$ok} updated, {$fail} failed.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('checkLinks')
                        ->label('Check links')
                        ->icon('heroicon-o-link')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $n = 0;
                            foreach ($records as $record) {
                                if ($record->trashed()) {
                                    continue;
                                }
                                try {
                                    app(LinkHealthService::class)->checkAlternative($record);
                                    $n++;
                                } catch (\Throwable) {
                                }
                            }
                            Notification::make()
                                ->title('Bulk link check')
                                ->body("Checked {$n} alternative(s).")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListOpenSourceAlternatives::route('/'),
            'create' => Pages\CreateOpenSourceAlternative::route('/create'),
            'edit' => Pages\EditOpenSourceAlternative::route('/{record}/edit'),
        ];
    }
}
