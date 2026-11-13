<?php

namespace Tests\Unit;

use App\Support\CsvSafe;
use PHPUnit\Framework\TestCase;

class CsvSafeTest extends TestCase
{
    public function test_formula_prefixes_are_neutralized(): void
    {
        foreach (['=1+1', '+SUM(A1:A2)', '-2+3', '@SUM(A1:A2)', " \t=1+1"] as $value) {
            self::assertSame("'".$value, CsvSafe::cell($value));
        }
    }

    public function test_normal_values_are_preserved(): void
    {
        self::assertSame('Alternova', CsvSafe::cell('Alternova'));
        self::assertSame('example.com', CsvSafe::cell('example.com'));
        self::assertSame('42', CsvSafe::cell(42));
        self::assertSame('', CsvSafe::cell(null));
    }

    public function test_row_sanitizes_each_cell(): void
    {
        self::assertSame(["'=1+1", 'safe'], CsvSafe::row(['=1+1', 'safe']));
    }
}
