<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateClickResource\Pages;
use App\Models\AffiliateClick;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AffiliateClickResource extends Resource
{
    protected static ?string $model = AffiliateClick::class;

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-rays';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Affiliate clicks';

    protected static ?int $navigationSort = 8;

    public static function tableReady(): bool
    {
        try {
            return Schema::hasTable('affiliate_clicks');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::tableReady();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            if (! static::tableReady()) {
                return null;
            }
            $count = AffiliateClick::query()->where('created_at', '>=', now()->subDay())->count();

            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('When'),
                Tables\Columns\TextColumn::make('provider')->badge()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('domain')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('ip_address')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('referer')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'namecheap' => 'Namecheap',
                        'porkbun' => 'Porkbun',
                        'godaddy' => 'GoDaddy',
                    ]),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('stats')
                    ->label('7-day summary')
                    ->icon('heroicon-o-chart-bar')
                    ->action(function () {
                        $rows = AffiliateClick::query()
                            ->where('created_at', '>=', now()->subDays(7))
                            ->select('provider', DB::raw('count(*) as c'))
                            ->groupBy('provider')
                            ->pluck('c', 'provider');

                        $msg = $rows->isEmpty()
                            ? 'No clicks in the last 7 days.'
                            : $rows->map(fn ($c, $p) => "{$p}: {$c}")->implode(' · ');

                        \Filament\Notifications\Notification::make()
                            ->title('Affiliate clicks (7 days)')
                            ->body($msg)
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliateClicks::route('/'),
        ];
    }
}
