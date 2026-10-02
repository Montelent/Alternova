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
        $this->enrich($alternative, [
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
            $this->enrich($alternative, [
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

    /** @param  array{repo_changed?: bool, website_changed?: bool}  $opts */
    protected function enrich(OpenSourceAlternative $alternative, array $opts = []): void
    {
        try {
            app(CatalogEnrichmentService::class)->enrichAlternative($alternative, $opts);
        } catch (\Throwable $e) {
            Log::warning('Catalog enrichment (alternative) failed: '.$e->getMessage());
        }
    }
}
