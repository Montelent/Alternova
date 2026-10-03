<?php

namespace App\Observers;

use App\Models\ProprietaryTool;
use App\Services\CatalogEnrichmentService;
use Illuminate\Support\Facades\Log;

class ProprietaryToolObserver
{
    public function created(ProprietaryTool $tool): void
    {
        $this->scheduleEnrich($tool->id);
    }

    public function updated(ProprietaryTool $tool): void
    {
        if ($tool->wasChanged(['website_url', 'description', 'name', 'is_published'])) {
            $this->scheduleEnrich($tool->id);
        }
    }

    protected function scheduleEnrich(int $toolId): void
    {
        dispatch(function () use ($toolId) {
            try {
                $tool = ProprietaryTool::query()->find($toolId);
                if (! $tool) {
                    return;
                }
                app(CatalogEnrichmentService::class)->enrichProprietaryTool($tool);
            } catch (\Throwable $e) {
                Log::warning('Catalog enrichment (proprietary) failed: '.$e->getMessage());
            }
        })->afterResponse();
    }
}
