<?php

namespace App\Filament\Resources\OpenSourceAlternativeResource\Pages;

use App\Filament\Resources\OpenSourceAlternativeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpenSourceAlternative extends CreateRecord
{
    protected static string $resource = OpenSourceAlternativeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['category_tags']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $tags = $this->form->getState()['category_tags'] ?? [];
        if (is_array($tags) && $tags) {
            $this->record->syncTagsWithType($tags, 'category');
        }
    }
}
