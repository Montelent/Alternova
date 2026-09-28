<?php

namespace App\Filament\Resources\AlternativeSubmissionResource\Pages;

use App\Filament\Resources\AlternativeSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlternativeSubmission extends EditRecord
{
    protected static string $resource = AlternativeSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
