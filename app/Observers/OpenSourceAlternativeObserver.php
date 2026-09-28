<?php

namespace App\Observers;

use App\Models\OpenSourceAlternative;
use App\Models\SlugRedirect;

class OpenSourceAlternativeObserver
{
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

        // Point old slug → new
        SlugRedirect::query()->updateOrCreate(
            ['old_slug' => $old],
            ['new_slug' => $new, 'model_type' => 'OpenSourceAlternative']
        );

        // Update any redirects that already pointed to the old slug
        SlugRedirect::query()
            ->where('new_slug', $old)
            ->update(['new_slug' => $new]);

        // Avoid redirect loops if new_slug was previously an old_slug
        SlugRedirect::query()
            ->where('old_slug', $new)
            ->delete();
    }
}
