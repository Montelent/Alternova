<?php

namespace App\Filament\Resources\WebhookDeliveryResource\Pages;

use App\Filament\Resources\WebhookDeliveryResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewWebhookDelivery extends ViewRecord
{
    protected static string $resource = WebhookDeliveryResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Delivery')->schema([
                TextEntry::make('webhook.name')->label('Webhook'),
                TextEntry::make('event'),
                IconEntry::make('success')->boolean()->label('Success'),
                TextEntry::make('status_code')->label('HTTP status'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('error')->columnSpanFull()->placeholder('—'),
                TextEntry::make('response_body')->columnSpanFull()->placeholder('—'),
                TextEntry::make('payload')->columnSpanFull()->placeholder('— (older deliveries may be empty)'),
            ])->columns(2),
        ]);
    }
}
