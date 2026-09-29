<?php

namespace App\Filament\Resources\SlugRedirectResource\Pages;

use App\Filament\Resources\SlugRedirectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSlugRedirects extends ListRecords
{
    protected static string $resource = SlugRedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
