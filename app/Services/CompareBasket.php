<?php

namespace App\Services;

class CompareBasket
{
    protected string $key = 'alternova_compare';

    public const MAX = 3;

    /**
     * @return list<string>
     */
    public function all(): array
    {
        return array_values(array_filter(session()->get($this->key, [])));
    }

    public function set(array $slugs): array
    {
        $clean = [];
        foreach ($slugs as $slug) {
            $slug = trim((string) $slug);
            if ($slug !== '' && ! in_array($slug, $clean, true)) {
                $clean[] = $slug;
            }
            if (count($clean) >= self::MAX) {
                break;
            }
        }
        session()->put($this->key, $clean);

        return $this->state();
    }

    public function add(string $slug): array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return $this->state();
        }

        $items = $this->all();
        if (in_array($slug, $items, true)) {
            return $this->state('Already in compare list');
        }

        if (count($items) >= self::MAX) {
            $items[self::MAX - 1] = $slug;
        } else {
            $items[] = $slug;
        }

        session()->put($this->key, $items);

        $count = count($items);
        $message = match (true) {
            $count >= 2 => 'Ready to compare',
            default => 'Added — pick one more',
        };

        return $this->state($message);
    }

    public function remove(string $slug): array
    {
        $items = array_values(array_filter($this->all(), fn ($s) => $s !== $slug));
        session()->put($this->key, $items);

        return $this->state('Removed from compare');
    }

    public function clear(): void
    {
        session()->forget($this->key);
    }

    public function has(string $slug): bool
    {
        return in_array($slug, $this->all(), true);
    }

    public function compareUrl(): ?string
    {
        $items = $this->all();
        if (count($items) < 2) {
            return null;
        }

        return self::urlFor(...array_slice($items, 0, self::MAX));
    }

    public static function urlFor(string ...$slugs): string
    {
        $slugs = array_values(array_filter($slugs));
        sort($slugs);

        $params = [];
        if (isset($slugs[0])) {
            $params['a'] = $slugs[0];
        }
        if (isset($slugs[1])) {
            $params['b'] = $slugs[1];
        }
        if (isset($slugs[2])) {
            $params['c'] = $slugs[2];
        }

        return route('alternatives.compare', $params);
    }

    /**
     * @return array{slugs: list<string>, count: int, message: string, url: ?string}
     */
    public function state(string $message = ''): array
    {
        $slugs = $this->all();

        return [
            'slugs' => $slugs,
            'count' => count($slugs),
            'message' => $message,
            'url' => count($slugs) >= 2
                ? self::urlFor(...$slugs)
                : null,
        ];
    }
}
