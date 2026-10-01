<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Facades\Schema;

class DefaultPages
{
    public static function seed(): int
    {
        if (! Schema::hasTable('pages')) {
            return 0;
        }

        $created = 0;
        foreach (static::definitions() as $def) {
            if (Page::query()->where('slug', $def['slug'])->exists()) {
                continue;
            }
            Page::create($def);
            $created++;
        }

        return $created;
    }

    public static function definitions(): array
    {
        return [
            [
                'title' => 'About Alternova',
                'slug' => 'about',
                'template' => 'default',
                'excerpt' => 'Learn about Alternova — open-source alternatives and brandable domain ideas.',
                'body_html' => '<p>Alternova helps you discover high-quality <strong>open-source alternatives</strong> to proprietary software and generate <strong>brandable domain name ideas</strong> with availability checks.</p><h2>What we do</h2><p>We curate self-hostable projects with licenses, difficulty ratings, health signals, and practical deploy guidance so you can move away from vendor lock-in with confidence.</p><h2>Our tools</h2><ul><li><a href="/alternatives">Open Source Finder</a> — compare alternatives to popular proprietary products.</li><li><a href="/domains">Domain Combinator</a> — invent brandable names and check availability.</li></ul><h2>Editorial standards</h2><p>Listings are reviewed before publishing. Metrics may be synced from public GitHub data.</p><h2>Contact</h2><p>Questions or corrections? <a href="/contact">Get in touch</a>.</p>',
                'is_published' => true,
                'show_in_footer' => true,
                'show_in_nav' => false,
                'sort_order' => 10,
                'meta_title' => 'About Alternova',
                'meta_description' => 'Learn about Alternova — a free directory of open-source software alternatives and a brandable domain name idea generator.',
                'published_at' => now(),
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'template' => 'legal',
                'excerpt' => 'How Alternova handles data, cookies, and third-party services.',
                'body_html' => '<p>This Privacy Policy explains how Alternova collects, uses, and shares information when you use our website.</p><h2>Information we collect</h2><p>We may collect technical data such as IP address, browser type, and pages visited. If you create an account, we store your email and profile details you provide.</p><h2>Cookies</h2><p>We use essential cookies for sessions and optional cookies for analytics or advertising when enabled.</p><h2>Third parties</h2><p>We may use advertising networks (e.g. Google AdSense), email providers, and analytics tools. Their policies apply to data they process.</p><h2>Contact</h2><p>For privacy requests, use our <a href="/contact">contact form</a>.</p>',
                'is_published' => true,
                'show_in_footer' => true,
                'show_in_nav' => false,
                'sort_order' => 20,
                'meta_title' => 'Privacy Policy — Alternova',
                'meta_description' => 'Privacy policy for Alternova: how we handle data, cookies, and third-party services including advertising.',
                'published_at' => now(),
            ],
            [
                'title' => 'Terms of Use',
                'slug' => 'terms',
                'template' => 'legal',
                'excerpt' => 'Terms of use for the Alternova website and tools.',
                'body_html' => '<p>By using Alternova you agree to these Terms of Use.</p><h2>Service</h2><p>Alternova provides informational listings of open-source software and domain name idea tools. Content is provided as-is without warranty.</p><h2>User content</h2><p>Comments, submissions, and votes must be lawful and respectful. We may moderate or remove content.</p><h2>Trademarks</h2><p>All product names and logos are property of their respective owners. Listing an alternative does not imply endorsement.</p><h2>Limitation of liability</h2><p>We are not liable for damages arising from use of third-party software or domain registrations.</p>',
                'is_published' => true,
                'show_in_footer' => true,
                'show_in_nav' => false,
                'sort_order' => 30,
                'meta_title' => 'Terms of Use — Alternova',
                'meta_description' => 'Terms of use for the Alternova website and tools.',
                'published_at' => now(),
            ],
            [
                'title' => 'Affiliate & Advertising Disclosure',
                'slug' => 'disclosure',
                'template' => 'legal',
                'excerpt' => 'How Alternova uses affiliate links and advertising.',
                'body_html' => '<p>Alternova may earn commissions from domain registrars and other partners when you use our links. This does not change the price you pay.</p><h2>Advertising</h2><p>We may display ads (including Google AdSense). Ads are labeled where required.</p><h2>Open-source listings</h2><p>Editorial rankings and health scores are independent of advertising. Sponsored placements are clearly marked when used.</p>',
                'is_published' => true,
                'show_in_footer' => true,
                'show_in_nav' => false,
                'sort_order' => 40,
                'meta_title' => 'Affiliate & Advertising Disclosure — Alternova',
                'meta_description' => 'How Alternova uses affiliate links and advertising, and how that relates to open-source listings.',
                'published_at' => now(),
            ],
        ];
    }
}
