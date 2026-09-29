<?php

namespace App\Filament\Resources\ProprietaryToolResource\Pages;

use App\Filament\Resources\ProprietaryToolResource;
use App\Models\SlugRedirect;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProprietaryTool extends EditRecord
{
    protected static string $resource = ProprietaryToolResource::class;

    protected ?string $originalSlug = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->originalSlug = $this->record->slug;

        return $data;
    }

    protected function afterSave(): void
    {
        $newSlug = $this->record->slug;
        if ($this->originalSlug && $this->originalSlug !== $newSlug) {
            try {
                SlugRedirect::record($this->originalSlug, $newSlug, 'tool');
            } catch (\Throwable) {
            }
            $this->originalSlug = $newSlug;
        }
    }
}
