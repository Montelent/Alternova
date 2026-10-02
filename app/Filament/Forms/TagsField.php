<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Set;

/**
 * Tags input that accepts paste/type of many items at once, split by commas.
 */
class TagsField
{
    public static function make(string $name, ?string $label = null): TagsInput
    {
        $field = TagsInput::make($name)
            ->splitKeys([',', 'Tab', 'Enter'])
            ->reorderable()
            ->helperText('Add several at once separated by commas, e.g. Fast, Open API, Self-hostable')
            ->afterStateUpdated(function ($state, Set $set) use ($name) {
                $set($name, static::normalize($state));
            })
            ->dehydrateStateUsing(fn ($state) => static::normalize($state));

        if ($label !== null) {
            $field->label($label);
        }

        return $field;
    }

    /**
     * Expand any tag that still contains commas (e.g. pasted "a, b, c" as one chip).
     *
     * @param  mixed  $state
     * @return list<string>
     */
    public static function normalize(mixed $state): array
    {
        if ($state === null || $state === '') {
            return [];
        }

        if (is_string($state)) {
            $state = preg_split('/\s*,\s*/', $state) ?: [];
        }

        if (! is_array($state)) {
            return [];
        }

        $out = [];
        foreach ($state as $item) {
            if (is_array($item)) {
                continue;
            }
            $parts = preg_split('/\s*,\s*/', (string) $item) ?: [];
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }
                $out[$part] = $part;
            }
        }

        return array_values($out);
    }
}
