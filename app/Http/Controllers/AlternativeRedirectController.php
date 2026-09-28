<?php

namespace App\Http\Controllers;

use App\Models\OpenSourceAlternative;
use App\Models\SlugRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlternativeRedirectController extends Controller
{
    /**
     * Fallback when Livewire route model binding 404s — check slug_redirects.
     */
    public function __invoke(Request $request, string $slug): RedirectResponse
    {
        $redirect = SlugRedirect::query()->where('old_slug', $slug)->first();

        if ($redirect) {
            $target = OpenSourceAlternative::query()
                ->where('slug', $redirect->new_slug)
                ->where('is_published', true)
                ->first();

            if ($target) {
                return redirect()->route('alternatives.show', $target, 301);
            }
        }

        abort(404);
    }
}
