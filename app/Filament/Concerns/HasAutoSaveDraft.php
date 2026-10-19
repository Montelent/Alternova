<?php

namespace App\Filament\Concerns;

use App\Models\AdminDraft;
use App\Support\DemoMode;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

/**
 * Offline-first auto-save for Filament create/edit forms.
 *
 * - localStorage: every ~2.5s when dirty (zero server load, survives offline)
 * - Server draft: at most every 60s when online (shared-hosting friendly)
 * - Restores local draft on load if present
 */
trait HasAutoSaveDraft
{
    public function getAutoSaveResourceKey(): string
    {
        return class_basename(static::getResource());
    }

    public function getAutoSaveConfig(): array
    {
        $userId = (int) (auth()->id() ?? 0);
        $recordId = $this->record?->getKey();

        return [
            'key' => sprintf(
                'alternova_draft:u%d:%s:%s',
                $userId,
                $this->getAutoSaveResourceKey(),
                $recordId !== null ? (string) $recordId : 'new'
            ),
            'resource' => $this->getAutoSaveResourceKey(),
            'recordId' => $recordId,
            'userId' => $userId,
            'localMs' => 2500,
            'serverMs' => 60000,
            'enabled' => true,
            'demo' => DemoMode::enabled(),
        ];
    }

    public function persistAutoSaveDraft(array $data = []): void
    {
        if (DemoMode::enabled()) {
            return;
        }

        if (! Schema::hasTable('admin_drafts')) {
            return;
        }

        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        $payload = $this->sanitizeDraftPayload($data !== [] ? $data : ($this->form->getState() ?? []));

        $encoded = json_encode($payload);
        if (is_string($encoded) && strlen($encoded) > 200_000) {
            $payload = $this->truncateDraftPayload($payload);
        }

        $recordKey = $this->record?->getKey() ?? 0;

        AdminDraft::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'resource' => $this->getAutoSaveResourceKey(),
                'record_id' => $recordKey,
            ],
            [
                'payload' => $payload,
            ]
        );
    }

    public function restoreAutoSaveDraft(array $data): void
    {
        if ($data === []) {
            return;
        }

        $payload = $this->sanitizeDraftPayload($data);
        $current = $this->form->getState() ?? [];
        $this->form->fill(array_merge($current, $payload));

        Notification::make()
            ->title('Draft restored')
            ->body('Your previous unsaved work was loaded.')
            ->success()
            ->send();
    }

    public function clearAutoSaveDraft(): void
    {
        if (! Schema::hasTable('admin_drafts')) {
            return;
        }

        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        $recordKey = $this->record?->getKey() ?? 0;

        AdminDraft::query()
            ->where('user_id', $userId)
            ->where('resource', $this->getAutoSaveResourceKey())
            ->where('record_id', $recordKey)
            ->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sanitizeDraftPayload(array $data): array
    {
        $drop = [
            'logo', 'logo_path', 'cover', 'cover_path', 'image', 'screenshot',
            'og_image_upload', 'favicon', 'attachments', 'files',
        ];
        foreach ($drop as $key) {
            if (array_key_exists($key, $data) && is_array($data[$key])) {
                unset($data[$key]);
            }
        }

        unset($data['password'], $data['password_confirmation']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function truncateDraftPayload(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_string($v) && strlen($v) > 20_000) {
                $data[$k] = mb_substr($v, 0, 20_000).'…';
            }
        }

        return $data;
    }

    public function renderedHasAutoSaveDraft(\Illuminate\View\View $view): void
    {
        $cfg = json_encode($this->getAutoSaveConfig(), JSON_UNESCAPED_SLASHES);
        $this->js("window.__alternovaAutosave&&window.__alternovaAutosave.init({$cfg})");
    }
}
