<?php

namespace App\Filament\Resources\OpenSourceAlternativeResource\Pages;

use App\Filament\Resources\OpenSourceAlternativeResource;
use App\Models\SlugRedirect;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOpenSourceAlternative extends EditRecord
{
    protected static string $resource = OpenSourceAlternativeResource::class;

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

        $newSlug = $this->record->slug;
        if ($this->originalSlug && $this->originalSlug !== $newSlug) {
            try {
                SlugRedirect::record($this->originalSlug, $newSlug, 'alternative');
            } catch (\Throwable) {
                // table may not exist yet
            }
            $this->originalSlug = $newSlug;
        }
    }
}
