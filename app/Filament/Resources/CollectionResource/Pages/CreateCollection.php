<?php

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Concerns\HasAutoSaveDraft;
use App\Filament\Resources\CollectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCollection extends CreateRecord
{
    use HasAutoSaveDraft;

    protected static string $resource = CollectionResource::class;

    protected function afterCreate(): void
    {
        $this->clearAutoSaveDraft();
        $this->dispatch('autosave-clear');
    }
}
