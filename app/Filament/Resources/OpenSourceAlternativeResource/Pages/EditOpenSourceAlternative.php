<?php

namespace App\Filament\Resources\OpenSourceAlternativeResource\Pages;

use App\Filament\Resources\OpenSourceAlternativeResource;
use App\Models\AdminActivityLog;
use App\Models\SlugRedirect;
use App\Services\AlternativeMarkdownExporter;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EditOpenSourceAlternative extends EditRecord
{
    protected static string $resource = OpenSourceAlternativeResource::class;

    protected ?string $originalSlug = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportMarkdown')
                ->label('Export Markdown')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    $md = app(AlternativeMarkdownExporter::class)->export($this->record);
                    AdminActivityLog::record('exported', $this->record, ['format' => 'markdown']);

                    return response()->streamDownload(function () use ($md) {
                        echo $md;
                    }, $this->record->slug.'-alternova.md', [
                        'Content-Type' => 'text/markdown; charset=UTF-8',
                    ]);
                }),
            Actions\DeleteAction::make()
                ->after(fn () => AdminActivityLog::record('deleted', $this->record)),
            Actions\ForceDeleteAction::make()
                ->after(fn () => AdminActivityLog::record('force_deleted', null, [], $this->record->name ?? null)),
            Actions\RestoreAction::make()
                ->after(fn () => AdminActivityLog::record('restored', $this->record)),
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

        try {
            if (Schema::hasTable('alternative_proprietary_tool')) {
                $this->record->load('proprietaryTools');
                $firstId = $this->record->proprietaryTools->first()?->id;
                if ($firstId && (int) $this->record->proprietary_tool_id !== (int) $firstId) {
                    $this->record->forceFill(['proprietary_tool_id' => $firstId])->saveQuietly();
                }
                // Set pivot positions
                $pos = 0;
                foreach ($this->record->proprietaryTools as $tool) {
                    $this->record->proprietaryTools()->updateExistingPivot($tool->id, ['position' => $pos++]);
                }
            }
        } catch (\Throwable) {
        }

        $newSlug = $this->record->slug;
        if ($this->originalSlug && $this->originalSlug !== $newSlug) {
            try {
                SlugRedirect::record($this->originalSlug, $newSlug, 'alternative');
            } catch (\Throwable) {
            }
            $this->originalSlug = $newSlug;
        }

        AdminActivityLog::record('updated', $this->record, [
            'slug' => $this->record->slug,
            'published' => (bool) $this->record->is_published,
        ]);
    }
}
