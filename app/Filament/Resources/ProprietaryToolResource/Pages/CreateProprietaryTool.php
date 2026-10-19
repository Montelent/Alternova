<?php

namespace App\Filament\Resources\ProprietaryToolResource\Pages;

use App\Filament\Concerns\HasAutoSaveDraft;
use App\Filament\Resources\ProprietaryToolResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProprietaryTool extends CreateRecord
{
    use HasAutoSaveDraft;

    protected static string $resource = ProprietaryToolResource::class;

    protected function afterCreate(): void
    {
        $this->clearAutoSaveDraft();
        $this->dispatch('autosave-clear');
    }
}
