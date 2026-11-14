<?php

namespace App\Support;

/**
 * Neutralize spreadsheet formula prefixes before writing untrusted values to CSV.
 */
class CsvSafe
{
    public static function cell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $text = is_scalar($value) ? (string) $value : '';

        if (preg_match('/\A[\s\x00-\x20]*[=+\-@]/', $text)) {
            return "'".$text;
        }

        return $text;
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, string>
     */
    public static function row(array $row): array
    {
        return array_map([self::class, 'cell'], $row);
    }
}
