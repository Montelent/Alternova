<?php

namespace App\Filament\Resources\OpenSourceAlternativeResource\Pages;

use App\Filament\Resources\OpenSourceAlternativeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOpenSourceAlternative extends EditRecord
{
    protected static string $resource = OpenSourceAlternativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['category_tags'] = $this->record->tags
            ->where('type', 'category')
            ->pluck('name')
            ->map(fn ($n) => (string) $n)
            ->values()
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['category_tags']);

        return $data;
    }

    protected function afterSave(): void
    {
        $tags = $this->form->getState()['category_tags'] ?? [];
        $this->record->syncTagsWithType(is_array($tags) ? $tags : [], 'category');
    }
}
