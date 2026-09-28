<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IssueReportResource\Pages;
use App\Models\IssueReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class IssueReportResource extends Resource
{
    protected static ?string $model = IssueReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Issue reports';

    protected static ?int $navigationSort = 15;

    public static function tableReady(): bool
    {
        try {
            return Schema::hasTable('issue_reports');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::tableReady();
    }

    public static function canAccess(): bool
    {
        return static::tableReady();
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            if (! static::tableReady()) {
                return null;
            }
            $count = IssueReport::query()->where('status', 'open')->count();

            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options([
                    'open' => 'Open',
                    'resolved' => 'Resolved',
                    'dismissed' => 'Dismissed',
                ])
                ->required(),
            Forms\Components\Select::make('type')
                ->options([
                    'broken_link' => 'Broken link',
                    'wrong_info' => 'Wrong information',
                    'spam' => 'Spam',
                    'other' => 'Other',
                ])
                ->disabled(),
            Forms\Components\TextInput::make('email')->disabled(),
            Forms\Components\TextInput::make('page_url')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('message')->disabled()->rows(5)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('alternative.name')->label('Alternative')->placeholder('—'),
                Tables\Columns\TextColumn::make('message')->limit(40)->wrap(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'open' => 'warning',
                        'resolved' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['open' => 'Open', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed']),
            ])
            ->actions([
                Tables\Actions\Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check')
                    ->visible(fn (IssueReport $r) => $r->status === 'open')
                    ->action(fn (IssueReport $r) => $r->update(['status' => 'resolved'])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIssueReports::route('/'),
            'edit' => Pages\EditIssueReport::route('/{record}/edit'),
        ];
    }
}
