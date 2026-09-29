<?php

namespace App\Filament\Resources\SlugRedirectResource\Pages;

use App\Filament\Resources\SlugRedirectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSlugRedirect extends EditRecord
{
    protected static string $resource = SlugRedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
