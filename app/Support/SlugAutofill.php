<?php

namespace App\Support;

use Illuminate\Support\Str;

final class SlugAutofill
{
    /**
     * Return a generated slug only while creating a record and only while the
     * slug field is blank or still follows the previous name value.
     */
    public static function fromName(
        ?string $name,
        ?string $currentSlug,
        ?string $oldName,
        string $operation,
    ): ?string {
        if ($operation !== 'create') {
            return null;
        }

        $newSlug = Str::slug(trim((string) $name));
        if ($newSlug === '') {
            return null;
        }

        $currentSlug = trim((string) $currentSlug);
        $oldSlug = Str::slug(trim((string) $oldName));

        if ($currentSlug === '' || ($oldSlug !== '' && $currentSlug === $oldSlug)) {
            return $newSlug;
        }

        return null;
    }
}
