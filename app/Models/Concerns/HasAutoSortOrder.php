<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Auto-assign sort_order = max+1 when empty/0 on create.
 * If a manual sort_order collides with another row, swap (interchange).
 */
trait HasAutoSortOrder
{
    public static function bootHasAutoSortOrder(): void
    {
        static::creating(function (Model $model): void {
            if (! array_key_exists('sort_order', $model->getAttributes())
                && ! $model->isDirty('sort_order')) {
                $model->setAttribute('sort_order', static::nextSortOrder());

                return;
            }

            $value = $model->getAttribute('sort_order');
            if ($value === null || $value === '' || (int) $value === 0) {
                $model->setAttribute('sort_order', static::nextSortOrder());
            }
        });

        static::saved(function (Model $model): void {
            if (! $model->wasRecentlyCreated && ! $model->wasChanged('sort_order')) {
                return;
            }

            static::interchangeSortOrderIfNeeded($model);
        });
    }

    public static function nextSortOrder(): int
    {
        try {
            $max = static::query()->max('sort_order');
        } catch (\Throwable) {
            return 1;
        }

        return ((int) $max) + 1;
    }

    /**
     * If another record already uses this sort_order, swap with it
     * (other gets our previous order, or next free slot on create).
     */
    protected static function interchangeSortOrderIfNeeded(Model $model): void
    {
        $order = (int) $model->getAttribute('sort_order');

        try {
            /** @var Model|null $other */
            $other = static::query()
                ->where('sort_order', $order)
                ->whereKeyNot($model->getKey())
                ->first();
        } catch (\Throwable) {
            return;
        }

        if (! $other) {
            return;
        }

        $previous = $model->getOriginal('sort_order');

        if ($previous !== null && $previous !== '' && (int) $previous !== $order) {
            $swapTo = (int) $previous;
        } else {
            // Create (or no previous): bump the other item to the next free order
            $swapTo = static::nextSortOrder();
            // nextSortOrder may equal $order if only these two exist — force higher
            if ($swapTo <= $order) {
                $swapTo = $order + 1;
            }
        }

        // Avoid a second conflict when possible
        try {
            $stillTaken = static::query()
                ->where('sort_order', $swapTo)
                ->whereKeyNot($other->getKey())
                ->whereKeyNot($model->getKey())
                ->exists();
            if ($stillTaken) {
                $swapTo = max(
                    (int) static::query()->max('sort_order'),
                    $order,
                    (int) $previous
                ) + 1;
            }
        } catch (\Throwable) {
        }

        $other->forceFill(['sort_order' => $swapTo])->saveQuietly();
    }
}
