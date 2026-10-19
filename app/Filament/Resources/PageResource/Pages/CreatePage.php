<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Concerns\HasAutoSaveDraft;
use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    use HasAutoSaveDraft;

    protected static string $resource = PageResource::class;

    protected function afterCreate(): void
    {
        $this->clearAutoSaveDraft();
        $this->dispatch('autosave-clear');
    }
}
