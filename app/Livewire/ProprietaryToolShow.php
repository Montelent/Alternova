<?php

namespace App\Livewire;

use App\Models\ProprietaryTool;
use App\Models\SlugRedirect;
use App\Services\OgImageService;
use App\Services\SeoManager;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
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
        $count = $alternatives->count();

        // "5 Open Source Alternatives to Salesforce"
        $heading = $count.' Open Source Alternative'.($count === 1 ? '' : 's').' to '.$tool->name;

        $seo = app(SeoManager::class);
        $title = $tool->meta_title ?: $heading;
        $description = $tool->meta_description
            ?: $seo->toolDescription($tool);

        // Enrich description with count when using default
        if (! $tool->meta_description) {
            $description = Str::limit(
                'Discover '.$count.' free, self-hostable open-source alternative'.($count === 1 ? '' : 's').' to '.$tool->name.'. '
                .strip_tags((string) ($tool->description ?? '')),
                155
            );
        }

        $canonical = $tool->canonical_url ?: route('alternativesto.show', $tool->slug);
        $ogImage = app(OgImageService::class)->toolUrl($tool);

        $notable = $alternatives->take(4)->pluck('name')->all();

        $categories = [];
        foreach ($alternatives as $alt) {
            if ($alt->relationLoaded('tags')) {
                foreach ($alt->tags->where('type', 'category') as $tag) {
                    $categories[(string) $tag->name] = true;
                }
            }
        }
        $categoryList = array_keys($categories);

        return view('livewire.proprietary-tool-show', [
            'alternatives' => $alternatives,
            'count' => $count,
            'heading' => $heading,
            'notable' => $notable,
            'categoryList' => $categoryList,
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
