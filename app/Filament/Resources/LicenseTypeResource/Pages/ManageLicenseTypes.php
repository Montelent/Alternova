<?php

namespace App\Filament\Resources\LicenseTypeResource\Pages;

use App\Filament\Resources\LicenseTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLicenseTypes extends ManageRecords
{
    protected static string $resource = LicenseTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
