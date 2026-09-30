<?php

namespace App\Filament\Resources\AlternativeProsConResource\Pages;

use App\Filament\Resources\AlternativeProsConResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlternativeProsCon extends EditRecord
{
    protected static string $resource = AlternativeProsConResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
