<?php

namespace App\Filament\Concerns;

use App\Models\AdminDraft;
use Filament\Actions\Action;

trait HasContinueFormDraftAction
{
    /**
     * Resource basename matching HasAutoSaveDraft::getAutoSaveResourceKey(),
     * e.g. OpenSourceAlternativeResource.
     */
    protected function getFormDraftResourceKey(): string
    {
        return class_basename(static::getResource());
    }

    protected function getContinueFormDraftAction(): ?Action
    {
        $draft = AdminDraft::unfinishedCreateFor($this->getFormDraftResourceKey());
        if (! $draft) {
            return null;
        }

        $resource = static::getResource();
        if (! $resource::hasPage('create')) {
            return null;
        }

        $label = $draft->summaryLabel();
        $when = $draft->updated_at?->diffForHumans() ?? 'recently';

        return Action::make('continueFormDraft')
            ->label('Continue form backup')
            ->icon('heroicon-o-document-text')
            ->color('warning')
            ->url($resource::getUrl('create'))
            ->tooltip("{$label} · saved {$when}. This is a form backup, not a Drafts-tab record.");
    }
}
