<?php

namespace App\Filament\Resources\ProprietaryToolResource\Pages;

use App\Filament\Resources\ProprietaryToolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProprietaryTool extends EditRecord
{
    protected static string $resource = ProprietaryToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
