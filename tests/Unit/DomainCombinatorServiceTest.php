<?php

namespace Tests\Unit;

use App\Services\DomainCombinatorService;
use PHPUnit\Framework\TestCase;

class DomainCombinatorServiceTest extends TestCase
{
    public function test_generator_caps_input_arrays_and_only_emits_supported_tlds(): void
    {
        $service = new DomainCombinatorService();
        $keywords = array_map(fn (int $i) => 'keyword'.$i, range(1, 40));
        $prefixes = array_map(fn (int $i) => 'prefix'.$i, range(1, 40));
        $suffixes = array_map(fn (int $i) => 'suffix'.$i, range(1, 40));

        $domains = $service->generateCombinations(
            $keywords,
            $prefixes,
            $suffixes,
            ['com', 'net', 'internal', 'local', 'evil']
        );

        self::assertNotEmpty($domains);
        self::assertLessThanOrEqual(3000, count($domains));

        foreach ($domains as $domain) {
            self::assertMatchesRegularExpression('/\.(com|net)$/', $domain);
        }
    }
}
