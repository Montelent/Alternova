<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_untrusted_rich_html_is_reduced_to_safe_markup(): void
    {
        $html = '<p>Hello <strong>world</strong></p>'
            .'<script>alert(1)</script>'
            .'<a href="javascript:alert(1)" onclick="alert(2)">unsafe link</a>'
            .'<img src="https://tracker.example/pixel.png" onerror="alert(3)">';

        $clean = HtmlSanitizer::clean($html);

        self::assertStringContainsString('<strong>world</strong>', $clean);
        self::assertStringNotContainsString('<script', strtolower($clean));
        self::assertStringNotContainsString('javascript:', strtolower($clean));
        self::assertStringNotContainsString('onclick', strtolower($clean));
        self::assertStringNotContainsString('onerror', strtolower($clean));
        self::assertStringNotContainsString('tracker.example', strtolower($clean));
    }
}
