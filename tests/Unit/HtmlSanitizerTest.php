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
    public function test_unsupported_semantic_wrappers_are_removed_without_breaking_sanitization(): void
    {
        $clean = HtmlSanitizer::clean(
            '<figure><figcaption>Screenshot</figcaption><p>Safe description</p></figure>'
            .'<script>alert(1)</script>'
        );

        self::assertStringContainsString('Safe description', $clean);
        self::assertStringNotContainsString('<figure', strtolower($clean));
        self::assertStringNotContainsString('<figcaption', strtolower($clean));
        self::assertStringNotContainsString('<script', strtolower($clean));
    }

}
