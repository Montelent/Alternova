<?php

namespace App\Filament\Concerns;

use App\Models\AdminDraft;
use Filament\Actions\Action;

trait HasContinueFormDraftAction
{
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

        $payload = is_array($draft->payload) ? $draft->payload : [];
        $name = trim((string) ($payload['name'] ?? $payload['title'] ?? ''));
        if ($name === '' && ! $this->payloadLooksUseful($payload)) {
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
            ->url($resource::getUrl('create').'?resume=1')
            ->tooltip("{$label} · saved {$when}");
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function payloadLooksUseful(array $payload): bool
    {
        foreach ($payload as $v) {
            if (is_string($v) && mb_strlen(trim(strip_tags($v))) >= 2) {
                return true;
            }
        }

        return false;
    }
}
