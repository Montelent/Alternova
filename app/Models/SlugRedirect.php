<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlugRedirect extends Model
{
    protected $fillable = [
        'old_slug',
        'new_slug',
        'model_type',
    ];

    public static function record(string $oldSlug, string $newSlug, string $modelType = 'alternative'): void
    {
        $oldSlug = trim($oldSlug);
        $newSlug = trim($newSlug);

        if ($oldSlug === '' || $newSlug === '' || $oldSlug === $newSlug) {
            return;
        }

        // Point previous redirects that landed on the old slug to the newest slug
        static::query()
            ->where('model_type', $modelType)
            ->where('new_slug', $oldSlug)
            ->update(['new_slug' => $newSlug]);

        static::query()->updateOrCreate(
            [
                'old_slug' => $oldSlug,
                'model_type' => $modelType,
            ],
            [
                'new_slug' => $newSlug,
            ]
        );
    }
}
