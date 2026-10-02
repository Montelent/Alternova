<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\TagsInput;

/**
 * Tags input that accepts paste/type of many items at once (comma or newline).
 */
class TagsField
{
    public static function make(string $name, ?string $label = null): TagsInput
    {
        $field = TagsInput::make($name)
            // Typing , Tab or Enter commits the current chip
            ->splitKeys([',', 'Tab', 'Enter'])
            ->reorderable()
            ->live(onBlur: true)
            ->helperText('Paste or type several at once, separated by commas or new lines. Example: Fast, Self-hostable, Open API — then press Enter or click outside the field.')
            ->afterStateHydrated(function (TagsInput $component, $state) {
                $component->state(static::normalize($state));
            })
            ->afterStateUpdated(function (TagsInput $component, $state) {
                $normalized = static::normalize($state);

                // Avoid unnecessary Livewire updates / loops
                if ($normalized === static::asComparableList($state)) {
                    return;
                }

                $component->state($normalized);
            })
            ->dehydrateStateUsing(fn ($state) => static::normalize($state));

        if ($label !== null) {
            $field->label($label);
        }

        return $field;
    }

    /**
     * Expand any value that still contains commas or newlines into separate tags.
     *
     * @return list<string>
     */
    public static function normalize(mixed $state): array
    {
        if ($state === null || $state === '') {
            return [];
        }

        // DB / form edge cases: JSON string
        if (is_string($state)) {
            $decoded = json_decode($state, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $state = $decoded;
            } else {
                $state = preg_split('/[\n\r,]+/', $state) ?: [];
            }
        }

        if (! is_array($state)) {
            return [];
        }

        $out = [];

        foreach ($state as $item) {
            if (is_array($item)) {
                foreach (static::normalize($item) as $nested) {
                    $key = mb_strtolower($nested);
                    if (! isset($out[$key])) {
                        $out[$key] = $nested;
                    }
                }

                continue;
            }

            if (! is_scalar($item)) {
                continue;
            }

            $parts = preg_split('/[\n\r,]+/', (string) $item) ?: [];

            foreach ($parts as $part) {
                $part = trim($part);
                $part = trim($part, " \t\"'");

                if ($part === '') {
                    continue;
                }

                $key = mb_strtolower($part);
                if (! isset($out[$key])) {
                    $out[$key] = $part;
                }
            }
        }

        return array_values($out);
    }

    /**
     * @return list<string>
     */
    protected static function asComparableList(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        $list = [];
        foreach ($state as $item) {
            if (! is_scalar($item)) {
                continue;
            }
            $item = trim((string) $item);
            if ($item === '') {
                continue;
            }
            $list[] = $item;
        }

        return array_values($list);
    }
}
