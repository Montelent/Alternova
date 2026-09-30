<?php

namespace App\Filament\Resources\CollectionSubmissionResource\Pages;

use App\Filament\Resources\CollectionSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollectionSubmission extends EditRecord
{
    protected static string $resource = CollectionSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
