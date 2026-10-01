<?php

namespace App\Filament\Resources\OssCategoryResource\Pages;

use App\Filament\Resources\OssCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageOssCategories extends ManageRecords
{
    protected static string $resource = OssCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
