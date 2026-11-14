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

    protected function setUp(): void
    {
        parent::setUp();
        Application::setInstance(new Application(dirname(__DIR__, 2)));
    }

    private function makeAlternative(bool $published): OpenSourceAlternative
    {
        // Construct without Eloquent boot so this unit test does not register
        // Scout observers or require a configured search driver.
        $alternative = (new \\ReflectionClass(OpenSourceAlternative::class))
            ->newInstanceWithoutConstructor();
        $alternative->setAttribute('is_published', $published);

        return $alternative;
    }

    public function test_unpublished_alternative_cannot_be_added_to_watchlist(): void
    {
        $alternative = $this->makeAlternative(false);

        self::assertFalse((new WatchlistService())->toggle($alternative));
        self::assertFalse((new WatchlistService())->isWatching($alternative));
    }
}
