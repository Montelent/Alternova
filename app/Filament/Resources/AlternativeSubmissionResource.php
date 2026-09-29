<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlternativeSubmissionResource\Pages;
use App\Jobs\SyncGitHubMetricsJob;
use App\Mail\SubmissionReviewedMail;
use App\Models\AdminActivityLog;
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AlternativeSubmissionResource extends Resource
{
    protected static ?string $model = AlternativeSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?string $navigationLabel = 'Submissions';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'alternative_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['alternative_name', 'proprietary_name', 'repo_url', 'submitter_email'];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            if (! Schema::hasTable('alternative_submissions')) {
                return null;
            }

            $count = AlternativeSubmission::query()->where('status', 'pending')->count();

            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
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
                    ->modalHeading('Approve and publish?')
                    ->modalDescription('Creates the proprietary tool if needed, publishes the alternative, and syncs GitHub metrics.')
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
                        static::notifySubmitter($record);
                        AdminActivityLog::record('rejected', null, [], $record->alternative_name);
                        Notification::make()->title('Submission rejected')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approveSelected')
                        ->label('Approve & create')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $ok = 0;
                            foreach ($records as $record) {
                                if ($record->status !== 'pending') {
                                    continue;
                                }
                                try {
                                    static::approveSubmission($record, quiet: true);
                                    $ok++;
                                } catch (\Throwable) {
                                }
                            }
                            Notification::make()
                                ->title("Approved {$ok} submission(s)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('rejectSelected')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->status !== 'pending') {
                                    continue;
                                }
                                $record->update([
                                    'status' => 'rejected',
                                    'reviewed_by' => auth()->id(),
                                    'reviewed_at' => now(),
                                ]);
                                static::notifySubmitter($record);
                            }
                            Notification::make()->title('Selected submissions rejected')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function approveSubmission(AlternativeSubmission $record, bool $quiet = false): void
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

        try {
            SyncGitHubMetricsJob::dispatchSync($alt);
        } catch (\Throwable) {
            try {
                SyncGitHubMetricsJob::dispatch($alt);
            } catch (\Throwable) {
            }
        }

        $record->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'created_alternative_id' => $alt->id,
        ]);

        static::notifySubmitter($record);
        AdminActivityLog::record('published', $alt, ['from_submission' => $record->id]);

        if (! $quiet) {
            Notification::make()
                ->title('Approved')
                ->body($alt->name.' was created, published, and synced from GitHub.')
                ->success()
                ->send();
        }
    }

    protected static function notifySubmitter(AlternativeSubmission $record): void
    {
        if (! $record->submitter_email || ! filter_var($record->submitter_email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::to($record->submitter_email)->send(new SubmissionReviewedMail($record));
        } catch (\Throwable) {
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlternativeSubmissions::route('/'),
            'edit' => Pages\EditAlternativeSubmission::route('/{record}/edit'),
        ];
    }
}
