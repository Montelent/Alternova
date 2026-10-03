<?php

namespace App\Filament\Resources\OpenSourceAlternativeResource\Pages;

use App\Filament\Resources\OpenSourceAlternativeResource;
use App\Models\AdminActivityLog;
use App\Models\OpenSourceAlternative;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOpenSourceAlternative extends CreateRecord
{
    protected static string $resource = OpenSourceAlternativeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug((string) $data['name']);
        }

        if (! empty($data['repo_url'])) {
            $normalized = rtrim(strtolower(trim($data['repo_url'])), '/');
            $existing = OpenSourceAlternative::query()
                ->whereRaw('LOWER(TRIM(TRAILING "/" FROM repo_url)) = ?', [$normalized])
                ->first();

            if ($existing) {
                Notification::make()
                    ->title('Possible duplicate')
                    ->body('An alternative already uses this repo: '.$existing->name.' ('.$existing->slug.'). You can still save.')
                    ->warning()
                    ->persistent()
                    ->send();
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $tags = $this->form->getState()['category_tags'] ?? [];
        if (is_array($tags) && $tags !== []) {
            $this->record->syncTagsWithType($tags, 'category');
        }

        AdminActivityLog::record('created', $this->record);
    }
}
