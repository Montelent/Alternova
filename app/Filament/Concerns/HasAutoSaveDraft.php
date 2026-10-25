<?php

namespace App\Filament\Concerns;

use App\Models\AdminDraft;
use App\Support\DemoMode;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

/**
 * Offline-first form backup for Filament create/edit.
 *
 * - localStorage: only when the form has meaningful text (name/title/etc.)
 * - Server draft: rare sync; create drafts are resume-able via ?resume=1
 * - Never calls form->getState() during restore (that validates and breaks the form)
 */
trait HasAutoSaveDraft
{
    public bool $autoSaveDidRestore = false;

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
            'resume' => request()->boolean('resume'),
            'isCreate' => $recordId === null,
        ];
    }

    /**
     * Raw form data without validation (Filament public $data on Create/Edit pages).
     *
     * @return array<string, mixed>
     */
    protected function getFormDataRaw(): array
    {
        if (property_exists($this, 'data') && is_array($this->data)) {
            return $this->data;
        }

        try {
            return $this->form->getRawState();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function draftHasMeaningfulContent(array $data): bool
    {
        $keys = [
            'name', 'title', 'slug', 'description', 'repo_url', 'website_url',
            'intro_html', 'body_html', 'body', 'content', 'meta_title',
            'meta_description', 'focus_keyword', 'target_audience',
        ];

        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $v = $data[$key];
            if (is_string($v) && trim(strip_tags($v)) !== '') {
                return true;
            }
        }

        foreach ($data as $v) {
            if (is_string($v) && mb_strlen(trim(strip_tags($v))) >= 2) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAutoSaveSnapshot(): array
    {
        return $this->sanitizeDraftPayload($this->getFormDataRaw());
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

        $payload = $this->sanitizeDraftPayload(
            $data !== [] ? $data : $this->getFormDataRaw()
        );

        if (! $this->draftHasMeaningfulContent($payload)) {
            return;
        }

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

    /**
     * @param  array<string, mixed>  $data
     */
    public function restoreAutoSaveDraft(array $data, bool $notify = true): void
    {
        if ($this->autoSaveDidRestore) {
            return;
        }

        $payload = $this->sanitizeDraftPayload($data);

        if (! $this->draftHasMeaningfulContent($payload)) {
            return;
        }

        // Never use form->getState() here — it validates and surfaces required errors.
        $current = $this->getFormDataRaw();
        $merged = array_merge($current, $payload);

        foreach ($payload as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            if (is_array($v) && $v === []) {
                continue;
            }
            $merged[$k] = $v;
        }

        $this->form->fill($merged);
        $this->autoSaveDidRestore = true;

        if ($notify) {
            Notification::make()
                ->title('Form backup restored')
                ->body('Your previous unsaved work was loaded. Complete required fields, then Save.')
                ->success()
                ->send();
        }
    }

    public function loadServerDraftIfNeeded(bool $force = false): void
    {
        if ($this->autoSaveDidRestore) {
            return;
        }

        if ($this->record?->getKey() && ! $force) {
            return;
        }

        if (! Schema::hasTable('admin_drafts')) {
            return;
        }

        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        $draft = AdminDraft::query()
            ->where('user_id', $userId)
            ->where('resource', $this->getAutoSaveResourceKey())
            ->where('record_id', $this->record?->getKey() ?? 0)
            ->latest('updated_at')
            ->first();

        if (! $draft || ! is_array($draft->payload)) {
            return;
        }

        if (! $this->draftHasMeaningfulContent($draft->payload)) {
            return;
        }

        $this->restoreAutoSaveDraft($draft->payload, notify: true);
    }

    public function clearAutoSaveDraft(): void
    {
        if (Schema::hasTable('admin_drafts')) {
            $userId = auth()->id();
            if ($userId) {
                $recordKey = $this->record?->getKey() ?? 0;

                AdminDraft::query()
                    ->where('user_id', $userId)
                    ->where('resource', $this->getAutoSaveResourceKey())
                    ->where('record_id', $recordKey)
                    ->delete();

                if ($recordKey !== 0) {
                    AdminDraft::query()
                        ->where('user_id', $userId)
                        ->where('resource', $this->getAutoSaveResourceKey())
                        ->where('record_id', 0)
                        ->delete();
                }
            }
        }

        $this->dispatch('autosave-clear');
        $cfg = $this->getAutoSaveConfig();
        $this->js(
            'window.__alternovaAutosave&&window.__alternovaAutosave.clearKey('
            .json_encode($cfg['key'])
            .')'
        );
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
        $cfg = json_encode($this->getAutoSaveConfig(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);
        $this->js('window.__alternovaAutosave&&window.__alternovaAutosave.init('.$cfg.')');
    }
}
