<?php

namespace App\Livewire;

use App\Models\ProprietaryTool;
use App\Models\SlugRedirect;
use App\Services\OgImageService;
use App\Services\SeoManager;
use Illuminate\Http\Exceptions\HttpResponseException;
use Livewire\Component;

class ProprietaryToolShow extends Component
{
    public ProprietaryTool $tool;

    public function mount(string $slug): void
    {
        $requestedSlug = trim($slug);

        $record = ProprietaryTool::query()
            ->where('slug', $requestedSlug)
            ->where('is_published', true)
            ->first();

        if (! $record) {
            try {
                $redirect = null;
                if (class_exists(SlugRedirect::class)) {
                    $redirect = SlugRedirect::query()
                        ->where('old_slug', $requestedSlug)
                        ->where('model_type', 'tool')
                        ->first();
                }

                if ($redirect) {
                    $target = ProprietaryTool::query()
                        ->where('slug', $redirect->new_slug)
                        ->where('is_published', true)
                        ->first();

                    if ($target) {
                        throw new HttpResponseException(
                            redirect()->to(route('alternativesto.show', $target->slug), 301)
                        );
                    }
                }
            } catch (HttpResponseException $e) {
                throw $e;
            } catch (\Throwable) {
            }

            abort(404);
        }

        $this->tool = $record;
    }

    public function render()
    {
        $tool = $this->tool;
        $alternatives = $tool->linkedAlternatives();

        $seo = app(SeoManager::class);
        $heading = 'Open Source Alternatives to '.$tool->name;
        $title = $tool->meta_title ?: $heading;
        $description = $seo->toolDescription($tool);
        $canonical = $tool->canonical_url ?: route('alternativesto.show', $tool->slug);
        $ogImage = app(OgImageService::class)->toolUrl($tool);

        return view('livewire.proprietary-tool-show', [
            'alternatives' => $alternatives,
            'heading' => $heading,
        ])->layout('layouts.app', [
            'title' => $title.' | Alternova',
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $tool->robots_meta ?: null,
            'ogType' => 'article',
            'ogTitle' => $tool->og_title ?: $title,
            'ogDescription' => $tool->og_description ?: $description,
            'ogImage' => $ogImage,
        ]);
    }
}
