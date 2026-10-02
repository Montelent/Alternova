<?php

namespace App\Observers;

use App\Models\ProprietaryTool;
use App\Services\CatalogEnrichmentService;
use Illuminate\Support\Facades\Log;

class ProprietaryToolObserver
{
    public function created(ProprietaryTool $tool): void
    {
        $this->enrich($tool);
    }

    public function updated(ProprietaryTool $tool): void
    {
        if ($tool->wasChanged(['website_url', 'description', 'name', 'is_published'])) {
            $this->enrich($tool);
        }
    }

    protected function enrich(ProprietaryTool $tool): void
    {
        try {
            app(CatalogEnrichmentService::class)->enrichProprietaryTool($tool);
        } catch (\Throwable $e) {
            Log::warning('Catalog enrichment (proprietary) failed: '.$e->getMessage());
        }
    }
}
