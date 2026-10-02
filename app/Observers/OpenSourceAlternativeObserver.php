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
        $this->enrich($alternative);
    }

    public function updated(OpenSourceAlternative $alternative): void
    {
        // Enrich when key fields change or still missing enrichment data
        if ($alternative->wasChanged(['repo_url', 'website_url', 'description', 'is_published'])
            || $alternative->overall_health_score === null
            || (float) $alternative->overall_health_score <= 0
            || $alternative->links_checked_at === null
        ) {
            $this->enrich($alternative);
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

    protected function enrich(OpenSourceAlternative $alternative): void
    {
        try {
            app(CatalogEnrichmentService::class)->enrichAlternative($alternative);
        } catch (\Throwable $e) {
            Log::warning('Catalog enrichment (alternative) failed: '.$e->getMessage());
        }
    }
}
