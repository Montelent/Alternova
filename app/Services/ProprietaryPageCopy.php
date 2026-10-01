<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Builds natural, unique on-page copy for /alternativesto/{slug} pages.
 * Alternative names mentioned in the text are linked to their profiles.
 */
class ProprietaryPageCopy
{
    /**
     * @param  Collection<int, OpenSourceAlternative>  $alternatives
     * @param  list<string>  $categoryList
     * @return array{heading: string, kicker: string, intro_html: string, body_html: string, meta_description: string}
     */
    public function build(ProprietaryTool $tool, Collection $alternatives, array $categoryList = []): array
    {
        $count = $alternatives->count();
        $name = $tool->name;

        $heading = $count > 0
            ? $count.' Open Source Alternative'.($count === 1 ? '' : 's').' to '.$name
            : 'Open Source Alternatives to '.$name;

        $categories = array_values(array_filter($categoryList));
        $categoryPhrase = $this->categoryPhrase($categories);

        $kicker = $count > 0
            ? 'Hand-picked '.$categoryPhrase.' you can self-host instead of '.$name
            : 'Looking for free, self-hostable options instead of '.$name;

        $top = $alternatives->first();
        $topName = $top?->name;
        $topStars = $top?->repoMetric?->github_stars;
        $licenses = $alternatives->pluck('license_type')->filter()->unique()->take(3)->values()->all();
        $langs = $alternatives->pluck('primary_language')->filter()->unique()->take(3)->values()->all();

        $introPlain = $this->introParagraph($name, $count, $topName, $topStars, $categories, $licenses, $alternatives);
        $bodyPlain = $this->bodyParagraph($name, $count, $alternatives, $langs);

        $introHtml = $this->linkNames($introPlain, $alternatives);
        $bodyHtml = $this->linkNames($bodyPlain, $alternatives);

        $meta = Str::limit($heading.'. '.$introPlain, 155);

        return [
            'heading' => $heading,
            'kicker' => $kicker,
            'intro_html' => $introHtml,
            'body_html' => $bodyHtml,
            'meta_description' => $meta,
        ];
    }

    /**
     * Replace known alternative names with profile links (longest names first).
     *
     * @param  Collection<int, OpenSourceAlternative>  $alternatives
     */
    public function linkNames(string $text, Collection $alternatives): string
    {
        $escaped = e($text);

        $named = $alternatives
            ->filter(fn ($a) => filled($a->name) && filled($a->slug))
            ->sortByDesc(fn ($a) => mb_strlen($a->name))
            ->values();

        foreach ($named as $alt) {
            $needle = e($alt->name);
            if ($needle === '') {
                continue;
            }

            $url = e(route('alternatives.show', $alt->slug));
            $link = '<a href="'.$url.'" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline underline-offset-2">'.$needle.'</a>';

            // Case-sensitive whole-word-ish replace; avoid breaking existing tags
            $escaped = preg_replace(
                '/(?<![\w\/"\-=])'.preg_quote($needle, '/').'(?![\w])/u',
                $link,
                $escaped,
                1 // first mention only keeps the prose readable
            ) ?? $escaped;
        }

        return $escaped;
    }

    /** @param  list<string>  $categories */
    protected function categoryPhrase(array $categories): string
    {
        if ($categories === []) {
            return 'open source tools';
        }

        $lower = array_map(fn ($c) => Str::lower($c), $categories);

        if (count($lower) === 1) {
            return $lower[0].' tools';
        }

        if (count($lower) === 2) {
            return $lower[0].' and '.$lower[1].' tools';
        }

        return $lower[0].', '.$lower[1].', and related tools';
    }

    /**
     * @param  list<string>  $categories
     * @param  list<string>  $licenses
     * @param  Collection<int, OpenSourceAlternative>  $alternatives
     */
    protected function introParagraph(
        string $name,
        int $count,
        ?string $topName,
        ?int $topStars,
        array $categories,
        array $licenses,
        Collection $alternatives,
    ): string {
        if ($count === 0) {
            return 'We have not listed a published open source alternative to '.$name.' yet. '
                .'You can suggest one from the community form so other teams can find it here.';
        }

        $parts = [];

        if ($count === 1 && $topName) {
            $parts[] = $topName.' is the open source option we currently track as an alternative to '.$name.'.';
        } else {
            $parts[] = 'Teams leave '.$name.' for many reasons: cost, data residency, vendor lock-in, or the need to run software on their own servers.';
            $parts[] = 'Below are '.$count.' open source projects that people actually use in place of '.$name.'.';
        }

        if ($topName && $count > 1) {
            if ($topStars && $topStars >= 1000) {
                $parts[] = $topName.' currently leads this list with about '.number_format($topStars).' GitHub stars, '
                    .'which is a useful signal of community activity (not a guarantee of fit for every team).';
            } else {
                $parts[] = $topName.' ranks highest on our health score among the options listed here.';
            }
        }

        // Mention 2–3 other names so they become links in the prose
        $others = $alternatives->skip(1)->take(3)->pluck('name')->filter()->values();
        if ($others->isNotEmpty()) {
            if ($others->count() === 1) {
                $parts[] = 'Another option worth a look is '.$others[0].'.';
            } elseif ($others->count() === 2) {
                $parts[] = 'Other strong contenders include '.$others[0].' and '.$others[1].'.';
            } else {
                $parts[] = 'Other strong contenders include '.$others[0].', '.$others[1].', and '.$others[2].'.';
            }
        }

        if ($licenses !== []) {
            $parts[] = 'Licenses in this set include '.implode(', ', $licenses).'.';
        }

        if ($categories !== []) {
            $parts[] = 'Most of these projects sit in the '.$this->categoryPhrase($categories).' space.';
        }

        return implode(' ', $parts);
    }

    /**
     * @param  Collection<int, OpenSourceAlternative>  $alternatives
     * @param  list<string>  $langs
     */
    protected function bodyParagraph(
        string $name,
        int $count,
        Collection $alternatives,
        array $langs,
    ): string {
        if ($count === 0) {
            return 'Check back after more alternatives are published, or browse the full finder for tools in a related category.';
        }

        $parts = [];

        $parts[] = 'When you compare alternatives to '.$name.', look past marketing pages. '
            .'Check how often the repo ships commits, how clear the install docs are, '
            .'and whether the license matches how you plan to deploy (including SaaS or internal use).';

        if ($langs !== []) {
            $parts[] = 'Primary languages among these projects include '.implode(', ', $langs).'. '
                .'That matters if your team already standardizes on a stack for operations and security review.';
        }

        $hard = $alternatives->filter(fn ($a) => (int) $a->self_host_difficulty >= 4)->count();
        $easy = $alternatives->filter(fn ($a) => (int) $a->self_host_difficulty <= 2)->count();

        if ($easy > 0 && $hard > 0) {
            $parts[] = $easy.' of these score as relatively easy to self-host, while '.$hard.' need more ops experience.';
        } elseif ($easy > 0) {
            $parts[] = 'Several options here are rated as approachable for smaller teams to self-host.';
        } elseif ($hard > 0) {
            $parts[] = 'Expect a steeper setup curve on several of these; plan time for deployment and upgrades.';
        }

        $parts[] = 'Use the cards below to open full profiles, Docker notes, and community pros and cons for each project.';

        return implode(' ', $parts);
    }
}
