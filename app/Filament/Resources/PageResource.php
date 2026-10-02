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
                    ->live(debounce: 300)
                    ->afterStateUpdated(fn ($state, Set $set, ?Page $record) => $record ? null : $set('slug', Str::slug((string) $state))),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(180)
                    ->unique(ignoreRecord: true)
                    ->live(debounce: 300)
                    ->helperText('URL path: /about, /privacy, or /p/your-slug for custom pages.'),
                Forms\Components\Select::make('template')
                    ->options([
                        'default' => 'Default article',
                        'legal' => 'Legal / policy',
                    ])
                    ->default('default')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Textarea::make('excerpt')
                    ->rows(2)
                    ->maxLength(300)
                    ->live(debounce: 400)
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
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->copyable(),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Live'),
                Tables\Columns\IconColumn::make('show_in_footer')->boolean()->label('Footer')->toggleable(),
                Tables\Columns\IconColumn::make('show_in_nav')->boolean()->label('Nav')->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('show_in_footer'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Page $record) => $record->publicUrl(), shouldOpenInNewTab: true)
                    ->visible(fn (Page $record) => $record->is_published),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_published' => true, 'published_at' => now()])),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_published' => false])),
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
