<?php

namespace Tests\Unit;

use App\Models\OpenSourceAlternative;
use App\Services\FavoriteService;
use App\Services\WatchlistService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PublishedContentBoundaryTest extends TestCase
{
    public function test_unpublished_alternative_cannot_be_added_to_favorites(): void
    {
        $alternative = $this->makeAlternative(false);

        $this->expectException(HttpException::class);

        (new FavoriteService())->toggle($alternative);
    }

    public function test_unpublished_alternative_is_never_reported_as_favorited(): void
    {
        $alternative = $this->makeAlternative(false);

        self::assertFalse((new FavoriteService())->has($alternative));
    }

    private function makeAlternative(bool $published): OpenSourceAlternative
    {
        // Avoid booting Scout's model observer in this isolated service test;
        // the test has no Laravel application container or search driver.
        $alternative = new class extends OpenSourceAlternative
        {
            protected static function boot(): void {}
        };
        $alternative->setAttribute('is_published', $published);

        return $alternative;
    }

    public function test_unpublished_alternative_cannot_be_added_to_watchlist(): void
    {
        $alternative = new OpenSourceAlternative(['is_published' => false]);

        self::assertFalse((new WatchlistService())->toggle($alternative));
        self::assertFalse((new WatchlistService())->isWatching($alternative));
    }
}
