<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpenSourceAlternativeResource\Pages;
use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class OpenSourceAlternativeResource extends Resource
{
    protected static ?string $model = OpenSourceAlternative::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationGroup = 'Open Source Finder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Core')->schema([
                    Forms\Components\Select::make('proprietary_tool_id')
                        ->relationship('proprietaryTool', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('repo_url')->url()->required(),
                    Forms\Components\TextInput::make('website_url')->url(),
                    Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
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
                        ]),
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
                        ->rows(12)
                        ->columnSpanFull()
                        ->helperText('Paste a complete docker-compose.yml blueprint'),
                    Forms\Components\Toggle::make('is_published'),
                    Forms\Components\Toggle::make('is_featured'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('proprietaryTool.name')->label('Proprietary'),
                Tables\Columns\TextColumn::make('license_type'),
                Tables\Columns\TextColumn::make('overall_health_score')->sortable(),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('repoMetric.synced_at')->dateTime()->label('Last Synced'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
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
                        Notification::make()
                            ->title('Sync job dispatched')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListOpenSourceAlternatives::route('/'),
            'create' => Pages\CreateOpenSourceAlternative::route('/create'),
            'edit' => Pages\EditOpenSourceAlternative::route('/{record}/edit'),
        ];
    }
}
