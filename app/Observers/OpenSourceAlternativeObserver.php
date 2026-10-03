<?php

namespace App\Observers;

use App\Models\OpenSourceAlternative;
use App\Models\SlugRedirect;
use App\Services\CatalogEnrichmentService;
use Illuminate\Support\Facades\Log;

class OpenSourceAlternativeObserver
{
    public function created(OpenSourceAlternative $alternative): void
    {
        $this->scheduleEnrich($alternative->id, [
            'repo_changed' => true,
            'website_changed' => true,
        ]);
    }

    public function updated(OpenSourceAlternative $alternative): void
    {
        $repoChanged = $alternative->wasChanged('repo_url');
        $websiteChanged = $alternative->wasChanged('website_url');

        $should = $repoChanged
            || $websiteChanged
            || $alternative->wasChanged(['description', 'is_published'])
            || $alternative->overall_health_score === null
            || (float) $alternative->overall_health_score <= 0
            || $alternative->links_checked_at === null;

        if ($should) {
            $this->scheduleEnrich($alternative->id, [
                'repo_changed' => $repoChanged,
                'website_changed' => $websiteChanged,
            ]);
        }
    }

    public function updating(OpenSourceAlternative $alternative): void
    {
        if (! $alternative->isDirty('slug')) {
            return;
        }

        $old = $alternative->getOriginal('slug');
        $new = $alternative->slug;

        if (! $old || ! $new || $old === $new) {
            return;
        }

        SlugRedirect::query()->updateOrCreate(
            ['old_slug' => $old],
            ['new_slug' => $new, 'model_type' => 'OpenSourceAlternative']
        );

       SlugRedirect::query()
            ->where('new_slug', $old)
            ->update(['new_slug' => $new]);

        SlugRedirect::query()
            ->where('old_slug', $new)
            ->delete();
    }

    /**
     * Run GitHub/link enrichment after the HTTP response so admin Save is fast.
     *
     * @param  array{repo_changed?: bool, website_changed?: bool}  $opts
     */
    protected function scheduleEnrich(int $alternativeId, array $opts = []): void
    {
        dispatch(function () use ($alternativeId, $opts) {
            try {
                $alt = OpenSourceAlternative::query()->find($alternativeId);
                if (! $alt) {
                    return;
                }
                app(CatalogEnrichmentService::class)->enrichAlternative($alt, $opts);
            } catch (\Throwable $e) {
                Log::warning('Catalog enrichment (alternative) failed: '.$e->getMessage());
            }
        })->afterResponse();
    }
}
