<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionSubmissionResource\Pages;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\CollectionSubmission;
use App\Models\OpenSourceAlternative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CollectionSubmissionResource extends Resource
{
    protected static ?string $model = CollectionSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Collection ideas';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        try {
            return Schema::hasTable('collection_submissions');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(160),
            Forms\Components\TextInput::make('proposed_slug')->maxLength(180),
            Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
            Forms\Components\Textarea::make('intro')->rows(4)->columnSpanFull(),
            Forms\Components\TagsInput::make('alternative_slugs')
                ->label('Alternative slugs')
                ->columnSpanFull(),
            Forms\Components\Textarea::make('notes')->label('Submitter notes')->rows(2)->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->required(),
            Forms\Components\Textarea::make('admin_notes')->rows(2)->columnSpanFull(),
            Forms\Components\TextInput::make('submitter_name'),
            Forms\Components\TextInput::make('submitter_email')->email(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('submitter_email')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('alternative_slugs')
                    ->label('Tools')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state).' listed' : '—'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\Filter::make('pending_only')
                    ->label('Pending only')
                    ->query(fn (Builder $query) => $query->where('status', 'pending'))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\Action::make('approvePublish')
                    ->label('Approve & create')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CollectionSubmission $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (CollectionSubmission $record) {
                        static::approveAndCreate($record);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CollectionSubmission $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (CollectionSubmission $record) {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);
                        Notification::make()->title('Rejected')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function approveAndCreate(CollectionSubmission $record): void
    {
        if (! Schema::hasTable('collections')) {
            Notification::make()->title('Collections table missing')->danger()->send();

            return;
        }

        $slug = $record->proposed_slug ?: Str::slug($record->title);
        $base = $slug;
        $i = 1;
        while (Collection::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        $collection = Collection::create([
            'name' => $record->title,
            'slug' => $slug,
            'description' => $record->description,
            'intro_html' => $record->intro,
            'is_published' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ]);

        $slugs = is_array($record->alternative_slugs) ? $record->alternative_slugs : [];
        $position = 0;
        foreach ($slugs as $s) {
            $alt = OpenSourceAlternative::query()->where('slug', $s)->first();
            if (! $alt) {
                continue;
            }
            CollectionItem::create([
                'collection_id' => $collection->id,
                'open_source_alternative_id' => $alt->id,
                'position' => $position++,
            ]);
        }

        $record->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'created_collection_id' => $collection->id,
            'proposed_slug' => $slug,
        ]);

        Notification::make()
            ->title('Collection published')
            ->body($collection->name.' with '.$position.' tools')
            ->success()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectionSubmissions::route('/'),
            'edit' => Pages\EditCollectionSubmission::route('/{record}/edit'),
        ];
    }
}
