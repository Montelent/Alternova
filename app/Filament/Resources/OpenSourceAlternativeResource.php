<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpenSourceAlternativeResource\Pages;
use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Services\DescriptionGeneratorService;
use App\Services\LinkHealthService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OpenSourceAlternativeResource extends Resource
{
    protected static ?string $model = OpenSourceAlternative::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Core')->schema([
                    Forms\Components\Select::make('proprietary_tool_id')
                        ->label('Proprietary tool')
                        ->relationship('proprietaryTool', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live(),
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
                    Forms\Components\Textarea::make('description')
                        ->rows(5)
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
                ])->columns(2),

                Forms\Components\Section::make('Publishing')->schema([
                    Forms\Components\Toggle::make('is_published')
                        ->label('Published (visible on site)')
                        ->default(false),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured')
                        ->default(false),
                ])->columns(2),

                Forms\Components\Section::make('Technical')->schema([
                    Forms\Components\Select::make('license_type')
                        ->options([
                            'MIT' => 'MIT',
                            'Apache-2.0' => 'Apache-2.0',
                            'AGPL-3.0' => 'AGPL-3.0',
                            'GPL-3.0' => 'GPL-3.0',
                            'BSD-3-Clause' => 'BSD-3-Clause',
                            'MPL-2.0' => 'MPL-2.0',
                            'BSL-1.1' => 'BSL-1.1',
                            'Other' => 'Other',
                        ])
                        ->searchable(),
                    Forms\Components\Select::make('self_host_difficulty')
                        ->options([
                            1 => '1 - Very Easy',
                            2 => '2 - Easy',
                            3 => '3 - Moderate',
                            4 => '4 - Hard',
                            5 => '5 - Expert',
                        ])
                        ->default(3),
                    Forms\Components\TextInput::make('primary_language'),
                    Forms\Components\TextInput::make('overall_health_score')->numeric()->disabled(),
                    Forms\Components\Textarea::make('docker_compose_blueprint')
                        ->rows(10)
                        ->columnSpanFull(),
                    Forms\Components\TagsInput::make('pros')->columnSpanFull(),
                    Forms\Components\TagsInput::make('cons')->columnSpanFull(),
                    Forms\Components\Textarea::make('editor_note')->rows(3)->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('Link health')->schema([
                    Forms\Components\IconEntry::make('repo_reachable')->boolean()->label('Repo reachable')->visible(false),
                    Forms\Components\Placeholder::make('link_status')
                        ->label('Last check')
                        ->content(function (?OpenSourceAlternative $record) {
                            if (! $record || ! $record->links_checked_at) {
                                return 'Not checked yet';
                            }
                            $repo = $record->repo_reachable === null ? '—' : ($record->repo_reachable ? 'OK' : 'BROKEN');
                            $site = $record->website_reachable === null ? '—' : ($record->website_reachable ? 'OK' : 'BROKEN');

                            return 'Repo: '.$repo.' · Website: '.$site.' · '.$record->links_checked_at->diffForHumans();
                        }),
                ])->collapsed(),

                Forms\Components\Section::make('SEO')->schema([
                    Forms\Components\TextInput::make('meta_title')->maxLength(70),
                    Forms\Components\Textarea::make('meta_description')->rows(3)->maxLength(160),
                ])->columns(1)->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('proprietaryTool.name')->label('Proprietary')->toggleable(),
                Tables\Columns\TextColumn::make('license_type')->toggleable(),
                Tables\Columns\TextColumn::make('overall_health_score')->sortable()->label('Health'),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Published'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured')->toggleable(),
                Tables\Columns\IconColumn::make('repo_reachable')
                    ->boolean()
                    ->label('Repo')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('website_reachable')
                    ->boolean()
                    ->label('Site')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('repoMetric.synced_at')->dateTime()->label('Last synced')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\TernaryFilter::make('repo_reachable')->label('Repo reachable'),
                Tables\Filters\SelectFilter::make('license_type')
                    ->options([
                        'MIT' => 'MIT',
                        'Apache-2.0' => 'Apache-2.0',
                        'AGPL-3.0' => 'AGPL-3.0',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('syncMetrics')
                    ->label('Sync GitHub')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (OpenSourceAlternative $record) {
                        SyncGitHubMetricsJob::dispatch($record);
                        Notification::make()->title('Sync job dispatched')->success()->send();
                    }),
                Tables\Actions\Action::make('checkLinks')
                    ->label('Check links')
                    ->icon('heroicon-o-link')
                    ->action(function (OpenSourceAlternative $record) {
                        app(LinkHealthService::class)->checkAlternative($record);
                        $fresh = $record->fresh();
                        $msg = 'Repo: '.($fresh->repo_reachable ? 'OK' : 'broken');
                        if ($fresh->website_url) {
                            $msg .= ' · Site: '.($fresh->website_reachable ? 'OK' : 'broken');
                        }
                        Notification::make()->title('Link check done')->body($msg)->success()->send();
                    }),
                Tables\Actions\Action::make('publish')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (OpenSourceAlternative $r) => ! $r->is_published)
                    ->action(fn (OpenSourceAlternative $r) => $r->update(['is_published' => true])),
                Tables\Actions\Action::make('unpublish')
                    ->icon('heroicon-o-eye-slash')
                    ->color('gray')
                    ->visible(fn (OpenSourceAlternative $r) => $r->is_published)
                    ->action(fn (OpenSourceAlternative $r) => $r->update(['is_published' => false])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publishSelected')
                        ->label('Publish')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_published' => true])),
                    Tables\Actions\BulkAction::make('unpublishSelected')
                        ->label('Unpublish')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_published' => false])),
                    Tables\Actions\BulkAction::make('checkLinks')
                        ->label('Check links')
                        ->icon('heroicon-o-link')
                        ->action(function ($records) {
                            $service = app(LinkHealthService::class);
                            foreach ($records as $record) {
                                $service->checkAlternative($record);
                            }
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
