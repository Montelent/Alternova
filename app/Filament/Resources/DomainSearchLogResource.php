<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DomainSearchLogResource\Pages;
use App\Models\DomainSearchLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DomainSearchLogResource extends Resource
{
    protected static ?string $model = DomainSearchLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Domain analytics';

    protected static ?int $navigationSort = 25;

    public static function tableReady(): bool
    {
        try {
            return Schema::hasTable('domain_search_logs');
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
        return static::tableReady() && (auth()->user()?->canManageSystem() ?? false);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('When'),
                Tables\Columns\TextColumn::make('seed_keywords')
                    ->label('Keywords')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : (string) $state)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('selected_tlds')
                    ->label('TLDs')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : (string) $state)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('domain_generated_count')->label('Generated')->sortable(),
                Tables\Columns\TextColumn::make('ip_address')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (): StreamedResponse {
                        $rows = DomainSearchLog::query()->orderByDesc('created_at')->limit(5000)->get();

                        return response()->streamDownload(function () use ($rows) {
                            $h = fopen('php://output', 'w');
                            fputcsv($h, ['when', 'keywords', 'tlds', 'generated', 'ip']);
                            foreach ($rows as $r) {
                                fputcsv($h, [
                                    optional($r->created_at)->toDateTimeString(),
                                    is_array($r->seed_keywords) ? implode('|', $r->seed_keywords) : $r->seed_keywords,
                                    is_array($r->selected_tlds) ? implode('|', $r->selected_tlds) : $r->selected_tlds,
                                    $r->domain_generated_count,
                                    $r->ip_address,
                                ]);
                            }
                            fclose($h);
                        }, 'domain-searches-'.date('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
                    }),
            ])
            ->actions([
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
            'index' => Pages\ListDomainSearchLogs::route('/'),
        ];
    }
}
