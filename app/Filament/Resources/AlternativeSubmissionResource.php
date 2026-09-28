<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlternativeSubmissionResource\Pages;
use App\Models\AlternativeSubmission;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Services\DescriptionGeneratorService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AlternativeSubmissionResource extends Resource
{
    protected static ?string $model = AlternativeSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?string $navigationLabel = 'Submissions';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = AlternativeSubmission::query()->where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Submission')->schema([
                Forms\Components\TextInput::make('submitter_name')->disabled(),
                Forms\Components\TextInput::make('submitter_email')->disabled(),
                Forms\Components\TextInput::make('proprietary_name')->required(),
                Forms\Components\TextInput::make('alternative_name')->required(),
                Forms\Components\TextInput::make('repo_url')->url()->required(),
                Forms\Components\TextInput::make('website_url')->url(),
                Forms\Components\TextInput::make('license_type'),
                Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')->rows(3)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('alternative_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('proprietary_name')->label('Replaces')->searchable(),
                Tables\Columns\TextColumn::make('repo_url')->limit(30)->url(fn ($r) => $r->repo_url, true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve & create')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AlternativeSubmission $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (AlternativeSubmission $record) {
                        static::approveSubmission($record);
                    }),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (AlternativeSubmission $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (AlternativeSubmission $record) {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->title('Submission rejected')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function approveSubmission(AlternativeSubmission $record): void
    {
        $prop = ProprietaryTool::query()->firstOrCreate(
            ['slug' => Str::slug($record->proprietary_name)],
            [
                'name' => $record->proprietary_name,
                'is_published' => true,
                'description' => $record->proprietary_name.' is a proprietary product listed for open-source comparison on Alternova.',
            ]
        );

        $description = $record->description;
        $language = null;
        $license = $record->license_type;
        $website = $record->website_url;

        if (! $description || ! $license) {
            $gen = app(DescriptionGeneratorService::class)->fromGitHubRepo(
                $record->repo_url,
                $prop->name
            );
            if ($gen['success']) {
                $description = $description ?: $gen['description'];
                $language = $gen['primary_language'];
                $license = $license ?: $gen['license_type'];
                $website = $website ?: $gen['website_url'];
            }
        }

        $slug = Str::slug($record->alternative_name);
        $base = $slug;
        $i = 1;
        while (OpenSourceAlternative::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        $alt = OpenSourceAlternative::create([
            'proprietary_tool_id' => $prop->id,
            'name' => $record->alternative_name,
            'slug' => $slug,
            'repo_url' => $record->repo_url,
            'website_url' => $website,
            'description' => $description,
            'license_type' => $license,
            'primary_language' => $language,
            'self_host_difficulty' => 3,
            'overall_health_score' => 0,
            'is_published' => true,
            'is_featured' => false,
        ]);

        $record->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'created_alternative_id' => $alt->id,
        ]);

        Notification::make()
            ->title('Approved')
            ->body($alt->name.' was created and published.')
            ->success()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlternativeSubmissions::route('/'),
            'edit' => Pages\EditAlternativeSubmission::route('/{record}/edit'),
        ];
    }
}
