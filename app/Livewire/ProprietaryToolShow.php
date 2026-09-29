<?php

namespace App\Livewire;

use App\Models\ProprietaryTool;
use App\Models\SlugRedirect;
use App\Services\SeoManager;
use Illuminate\Http\Exceptions\HttpResponseException;
use Livewire\Component;

class ProprietaryToolShow extends Component
{
    public ProprietaryTool $tool;

    public function mount(string $tool): void
    {
        $requestedSlug = $tool;

        $record = ProprietaryTool::query()
            ->where('slug', $requestedSlug)
            ->where('is_published', true)
            ->first();

        if (! $record) {
            try {
                $redirect = SlugRedirect::query()
                    ->where('old_slug', $requestedSlug)
                    ->where('model_type', 'tool')
                    ->first();

                if ($redirect) {
                    $target = ProprietaryTool::query()
                        ->where('slug', $redirect->new_slug)
                        ->where('is_published', true)
                        ->first();

                    if ($target) {
                        throw new HttpResponseException(
                            redirect()->to(route('tools.show', $target), 301)
                        );
                    }
                }
            } catch (HttpResponseException $e) {
                throw $e;
            } catch (\Throwable) {
            }

            abort(404);
        }

        $this->tool = $record->load([
            'publishedAlternatives' => fn ($q) => $q
                ->with(['repoMetric', 'tags'])
                ->orderByDesc('overall_health_score'),
        ]);
    }

    public function render()
    {
        $tool = $this->tool;
        $seo = app(SeoManager::class);
        $title = $seo->toolTitle($tool);
        $description = $seo->toolDescription($tool);
        $canonical = $tool->canonical_url ?: route('tools.show', $tool);

        return view('livewire.proprietary-tool-show', [
            'alternatives' => $tool->publishedAlternatives,
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $tool->robots_meta ?: null,
            'ogType' => 'article',
            'ogTitle' => $tool->og_title ?: $title,
            'ogDescription' => $tool->og_description ?: $description,
            'ogImage' => $tool->og_image_url
                ?: ($tool->logo_path ? url($tool->logo_path) : null),
        ]);
    }
}
