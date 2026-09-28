<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')->email()->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'active' => 'Active',
                    'unsubscribed' => 'Unsubscribed',
                ])
                ->required(),
            Forms\Components\TextInput::make('source')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state) => $state === 'active' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('source')->toggleable(),
                Tables\Columns\TextColumn::make('subscribed_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'unsubscribed' => 'Unsubscribed']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (): StreamedResponse {
                        $rows = NewsletterSubscriber::query()->orderBy('email')->get();

                        return response()->streamDownload(function () use ($rows) {
                            $h = fopen('php://output', 'w');
                            fputcsv($h, ['email', 'status', 'source', 'subscribed_at']);
                            foreach ($rows as $r) {
                                fputcsv($h, [
                                    $r->email,
                                    $r->status,
                                    $r->source,
                                    optional($r->subscribed_at)->toDateTimeString(),
                                ]);
                            }
                            fclose($h);
                        }, 'newsletter-'.date('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
                    }),
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
            'index' => Pages\ListNewsletterSubscribers::route('/'),
            'edit' => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}
