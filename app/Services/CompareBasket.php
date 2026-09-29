<?php

namespace App\Services;

class CompareBasket
{
    protected string $key = 'alternova_compare';

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
            if (count($clean) >= 2) {
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

        if (count($items) >= 2) {
            $items[1] = $slug;
        } else {
            $items[] = $slug;
        }

        session()->put($this->key, $items);

        return $this->state(count($items) === 2 ? 'Ready to compare' : 'Added — pick one more');
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

        return self::urlFor($items[0], $items[1]);
    }

    public static function urlFor(string $a, string $b): string
    {
        // Stable order for shareable canonicals (alphabetical by slug)
        $pair = [$a, $b];
        sort($pair);

        return route('alternatives.compare', [
            'a' => $pair[0],
            'b' => $pair[1],
        ]);
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
                ? self::urlFor($slugs[0], $slugs[1])
                : null,
        ];
    }
}
