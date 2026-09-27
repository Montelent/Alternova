<?php

namespace App\Services;

class DomainCombinatorService
{
    /**
     * Generate domain name combinations algorithmically.
     *
     * @param  array<string>  $keywords
     * @param  array<string>  $prefixes
     * @param  array<string>  $suffixes
     * @param  array<string>  $tlds
     * @return array<string>
     */
    public function generateCombinations(
        array $keywords,
        array $prefixes = [],
        array $suffixes = [],
        array $tlds = ['com']
    ): array {
        $keywords = array_map(fn ($k) => $this->sanitize($k), array_filter($keywords));
        $prefixes = array_map(fn ($p) => $this->sanitize($p), array_filter($prefixes));
        $suffixes = array_map(fn ($s) => $this->sanitize($s), array_filter($suffixes));
        $tlds = array_map(fn ($t) => ltrim(strtolower($t), '.'), array_filter($tlds));

        if (empty($keywords) || empty($tlds)) {
            return [];
        }

        $bases = [];

        // Single keywords
        foreach ($keywords as $kw) {
            $bases[] = $kw;
        }

        // Keyword + Keyword combinations (limited)
        $count = count($keywords);
        for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j < $count; $j++) {
                if ($i !== $j) {
                    $bases[] = $keywords[$i] . $keywords[$j];
                }
            }
        }

        // Prefix + Keyword
        foreach ($prefixes as $prefix) {
            foreach ($keywords as $kw) {
                $bases[] = $prefix . $kw;
            }
        }

        // Keyword + Suffix
        foreach ($keywords as $kw) {
            foreach ($suffixes as $suffix) {
                $bases[] = $kw . $suffix;
            }
        }

        // Prefix + Keyword + Suffix
        foreach ($prefixes as $prefix) {
            foreach ($keywords as $kw) {
                foreach ($suffixes as $suffix) {
                    $bases[] = $prefix . $kw . $suffix;
                }
            }
        }

        $bases = array_unique($bases);

        $domains = [];
        foreach ($bases as $base) {
            foreach ($tlds as $tld) {
                $domains[] = strtolower($base . '.' . $tld);
            }
        }

        return array_values(array_unique($domains));
    }

    /**
     * Calculate brandability score (0-100).
     * Factors: length, syllable balance, Metaphone pronounceability, vowel ratio.
     */
    public function calculateBrandabilityScore(string $domain): float
    {
        $name = explode('.', $domain)[0] ?? $domain;
        $name = strtolower(preg_replace('/[^a-z0-9]/', '', $name) ?? '');

        if (strlen($name) < 2) {
            return 0;
        }

        $score = 100.0;

        // Length penalty (ideal 6-12 chars)
        $len = strlen($name);
        if ($len < 4) {
            $score -= 25;
        } elseif ($len < 6) {
            $score -= 10;
        } elseif ($len > 15) {
            $score -= ($len - 15) * 4;
        } elseif ($len > 12) {
            $score -= ($len - 12) * 2;
        }

        // Syllable estimate (rough)
        $syllables = $this->estimateSyllables($name);
        if ($syllables > 4) {
            $score -= ($syllables - 4) * 8;
        } elseif ($syllables === 1 && $len > 6) {
            $score -= 5; // slightly penalize long mono-syllabic
        }

        // Vowel ratio (good brands have balanced vowels)
        $vowels = preg_match_all('/[aeiouy]/', $name);
        $vowelRatio = $vowels / max($len, 1);
        if ($vowelRatio < 0.2 || $vowelRatio > 0.6) {
            $score -= 12;
        }

        // Consecutive consonants penalty
        if (preg_match('/[bcdfghjklmnpqrstvwxz]{4,}/', $name)) {
            $score -= 15;
        }

        // Numbers penalty
        if (preg_match('/\d/', $name)) {
            $score -= 10;
        }

        // Metaphone uniqueness bonus (simple heuristic)
        $meta = metaphone($name);
        if (strlen($meta) >= 3 && strlen($meta) <= 6) {
            $score += 5;
        }

        return max(0, min(100, round($score, 1)));
    }

    protected function sanitize(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $value) ?? '');
    }

    protected function estimateSyllables(string $word): int
    {
        $word = strtolower($word);
        $word = preg_replace('/[^a-z]/', '', $word) ?? '';

        if (strlen($word) <= 3) {
            return 1;
        }

        $syllables = preg_match_all('/[aeiouy]+/', $word);
        // Adjust for silent e
        if (str_ends_with($word, 'e') && $syllables > 1) {
            $syllables--;
        }

        return max(1, $syllables);
    }
}
