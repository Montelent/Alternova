<?php

namespace App\Filament\Resources\OpenSourceAlternativeResource\Pages;

use App\Filament\Resources\OpenSourceAlternativeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOpenSourceAlternatives extends ListRecords
{
    protected static string $resource = OpenSourceAlternativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
