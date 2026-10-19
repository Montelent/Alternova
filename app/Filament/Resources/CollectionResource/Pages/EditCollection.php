<?php

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Concerns\HasAutoSaveDraft;
use App\Filament\Resources\CollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollection extends EditRecord
{
    use HasAutoSaveDraft;

    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->clearAutoSaveDraft();
        $this->dispatch('autosave-clear');
    }
}
