<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Auto-assign sort_order = max+1 when empty/0 on create.
 * On update, empty/null sort_order keeps the existing value (never writes NULL).
 * If a manual sort_order collides with another row, swap (interchange).
 */
trait HasAutoSortOrder
{
    public static function bootHasAutoSortOrder(): void
    {
        static::saving(function (Model $model): void {
            $value = $model->getAttribute('sort_order');

            if ($value !== null && $value !== '' && (int) $value !== 0) {
                return;
            }

            // Update with empty field → keep previous order (DB column is NOT NULL)
            if ($model->exists) {
                $original = $model->getOriginal('sort_order');
                if ($original !== null && $original !== '' && (int) $original !== 0) {
                    $model->setAttribute('sort_order', (int) $original);

                    return;
                }
            }

            $model->setAttribute('sort_order', static::nextSortOrder());
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

        return max(1, ((int) $max) + 1);
    }

    /**
     * If another record already uses this sort_order, swap with it
     * (other gets our previous order, or next free slot on create).
     */
    protected static function interchangeSortOrderIfNeeded(Model $model): void
    {
        $order = (int) $model->getAttribute('sort_order');
        if ($order <= 0) {
            return;
        }

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
            $swapTo = static::nextSortOrder();
            if ($swapTo <= $order) {
                $swapTo = $order + 1;
            }
        }

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
