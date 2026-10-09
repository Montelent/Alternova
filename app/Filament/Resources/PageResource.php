<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TinyEditor;
use App\Filament\Forms\SeoForm;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function shouldRegisterNavigation(): bool
    {
        try {
            return Schema::hasTable('pages');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Page')->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(200)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Set $set, Forms\Get $get, string $operation): void {
                        if ($operation !== 'create') {
                            return;
                        }
                        $title = trim((string) $state);
                        if ($title === '') {
                            return;
                        }
                        if (trim((string) $get('slug')) === '') {
                            $set('slug', Str::slug($title));
                        }
                    }),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(180)
                    ->unique(ignoreRecord: true)
                    ->helperText('Public URL is /your-slug (same as /about). Example: slug "team" → /team.'),
                Forms\Components\Select::make('template')
                    ->options([
                        'default' => 'Default article',
                        'legal' => 'Legal / policy',
                    ])
                    ->default('default')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->nullable()
                    ->placeholder('Auto')
                    ->helperText('Leave empty for next number. Setting a used number swaps with that item.'),
                Forms\Components\Textarea::make('excerpt')
                    ->rows(2)
                    ->maxLength(300)
                    ->live(onBlur: true)
                    ->columnSpanFull()
                    ->helperText('Short summary for SEO and listings. Also used as meta description when SEO meta is empty.'),
                TinyEditor::make('body_html')
                    ->label('Body')
                    ->height(480)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')->label('Published')->default(false),
                Forms\Components\Toggle::make('show_in_footer')->label('Show in site footer')->default(false),
                Forms\Components\Toggle::make('show_in_nav')->label('Show in header nav')->default(false),
                Forms\Components\DateTimePicker::make('published_at')->native(false),
            ])->columns(2),

            ...SeoForm::schema('page', 'slug', 'title', 'body_html'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->copyable(),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Live'),
                Tables\Columns\IconColumn::make('show_in_footer')->boolean()->label('Footer')->toggleable(),
                Tables\Columns\IconColumn::make('show_in_nav')->boolean()->label('Nav')->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->label('Updated')->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('show_in_footer'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Page $record) {
                        $copy = $record->replicate(['slug', 'is_published', 'published_at']);
                        $copy->title = $record->title.' (copy)';
                        $copy->slug = $record->slug.'-copy-'.Str::lower(Str::random(4));
                        $copy->is_published = false;
                        $copy->published_at = null;
                        $copy->save();
                        Notification::make()->title('Page duplicated as draft')->success()->send();
                    }),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
