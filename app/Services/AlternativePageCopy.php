<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Long-form, human editorial copy for alternative detail pages (AdSense / SEO).
 * Targets 300+ words using real project attributes, not filler spam.
 */
class AlternativePageCopy
{
    /**
     * @return array{
     *     guide_html: string,
     *     faq: list<array{q: string, a: string}>,
     *     word_count: int,
     *     meta_description: string
     * }
     */
    public function build(OpenSourceAlternative $alt): array
    {
        $name = $alt->name;
        $tools = $this->linkedTools($alt);
        $toolNames = $tools->pluck('name')->filter()->values();
        $stars = $alt->repoMetric?->github_stars;
        $forks = $alt->repoMetric?->github_forks;
        $issues = $alt->repoMetric?->open_issues;
        $license = $alt->license_type;
        $lang = $alt->primary_language;
        $health = $alt->overall_health_score;
        $difficulty = $alt->self_host_difficulty;
        $hasDocker = filled(trim((string) $alt->docker_compose_blueprint));
        $repo = $alt->repo_url;
        $site = $alt->website_url;

        $p1 = $this->opening($name, $toolNames, $license, $lang);
        $p2 = $this->whoItIsFor($name, $toolNames, $difficulty, $hasDocker);
        $p3 = $this->communitySignals($name, $stars, $forks, $issues, $health, $repo);
        $p4 = $this->howToEvaluate($name, $license, $lang, $hasDocker);
        $p5 = $this->practicalNextSteps($name, $site, $repo, $toolNames);

        $plain = implode("\n\n", array_filter([$p1, $p2, $p3, $p4, $p5]));
        $html = $this->toHtml($plain, $tools);

        $faq = $this->faq($name, $toolNames, $license, $lang, $difficulty, $hasDocker, $health);

        $wordCount = str_word_count(strip_tags($plain));

        // Pad carefully if still short (rare)
        if ($wordCount < 300) {
            $extra = $this->depthParagraph($name, $toolNames, $lang, $license);
            $plain .= "\n\n".$extra;
            $html = $this->toHtml($plain, $tools);
            $wordCount = str_word_count(strip_tags($plain));
        }

        $meta = Str::limit(
            $name.' is an open-source project'.($toolNames->isNotEmpty() ? ' often considered alongside '.$toolNames->take(2)->implode(' and ') : '').'. '
            .Str::limit($p1, 100),
            155
        );

        return [
            'guide_html' => $html,
            'faq' => $faq,
            'word_count' => $wordCount,
            'meta_description' => $meta,
        ];
    }

    /** @return Collection<int, \App\Models\ProprietaryTool> */
    protected function linkedTools(OpenSourceAlternative $alt): Collection
    {
        try {
            if ($alt->relationLoaded('proprietaryTools') && $alt->proprietaryTools->isNotEmpty()) {
                return $alt->proprietaryTools;
            }
        } catch (\Throwable) {
        }

        if ($alt->relationLoaded('proprietaryTool') && $alt->proprietaryTool) {
            return collect([$alt->proprietaryTool]);
        }

        try {
            if (method_exists($alt, 'proprietaryTools')) {
                $many = $alt->proprietaryTools()->get();
                if ($many->isNotEmpty()) {
                    return $many;
                }
            }
        } catch (\Throwable) {
        }

        return $alt->proprietaryTool ? collect([$alt->proprietaryTool]) : collect();
    }

    /** @param  Collection<int, string>  $toolNames */
    protected function opening(string $name, $toolNames, ?string $license, ?string $lang): string
    {
        $parts = [];

        if ($toolNames->isNotEmpty()) {
            $list = $this->joinNames($toolNames->take(3)->all());
            $parts[] = $name.' sits in the growing class of open-source products teams evaluate when they want more control than a fully managed commercial stack such as '.$list.'.';
        } else {
            $parts[] = $name.' is an open-source project listed in our directory for teams that want transparent code, portable deployments, and the option to run infrastructure on their own terms.';
        }

        $parts[] = 'Unlike brochure pages that only celebrate features, this guide focuses on the practical questions operators ask before a pilot: licensing posture, language and runtime fit, self-host effort, and whether the community around the repository still looks active.';

        if ($license) {
            $parts[] = 'The project is associated with a '.$license.' license in our catalog, which is a starting point for legal review rather than a substitute for reading the full license text and any additional terms published by the maintainers.';
        }

        if ($lang) {
            $parts[] = 'Its primary language is listed as '.$lang.', a detail that influences hiring, security tooling, and how easily your existing SRE or platform team can own the service after launch.';
        }

        return implode(' ', $parts);
    }

    /** @param  Collection<int, string>  $toolNames */
    protected function whoItIsFor(string $name, $toolNames, $difficulty, bool $hasDocker): string
    {
        $parts = [];

        $parts[] = 'Most organizations do not adopt '.$name.' because open source is fashionable. They adopt it when data residency, customization, predictable cost at scale, or independence from a single vendor roadmap becomes a board-level concern.';

        if ($toolNames->isNotEmpty()) {
            $parts[] = 'If your current workflow is built around '.$this->joinNames($toolNames->take(2)->all()).', treat '.$name.' as a candidate for a controlled migration path: mirror a non-critical workspace first, measure latency and admin overhead, then decide whether a hybrid or full cutover is justified.';
        }

        if ($difficulty !== null && $difficulty !== '') {
            $d = (int) $difficulty;
            if ($d <= 2) {
                $parts[] = 'Our editors rate self-host difficulty at '.$d.' out of 5, which usually means a competent generalist can bring up a test instance without a multi-week platform project.';
            } elseif ($d === 3) {
                $parts[] = 'Self-host difficulty is rated '.$d.' out of 5. Expect a real install guide, environment variables to get right, and some ongoing maintenance rather than a one-click appliance experience.';
            } else {
                $parts[] = 'Self-host difficulty is rated '.$d.' out of 5. Plan for dedicated operations capacity, backups, upgrades, and monitoring before you promise production timelines to leadership.';
            }
        }

        if ($hasDocker) {
            $parts[] = 'A Docker Compose oriented blueprint is available in our listing, which can shorten the time from evaluation to a disposable sandbox on a single VM or workstation.';
        } else {
            $parts[] = 'Even without a packaged Compose file in our listing, most mature open-source products publish official containers or Helm charts; verify those upstream before you standardize on a home-grown install script.';
        }

        return implode(' ', $parts);
    }

    protected function communitySignals(string $name, $stars, $forks, $issues, $health, ?string $repo): string
    {
        $parts = [];

        $parts[] = 'Community health is imperfectly measured, yet a few signals still help. GitHub stars and forks describe attention, not quality. Open issues describe workload, not neglect. Release cadence and maintainer response patterns often matter more than any single vanity metric.';

        if ($stars) {
            $parts[] = 'In our last sync, '.$name.' showed roughly '.number_format((int) $stars).' stars'
                .($forks ? ' and '.number_format((int) $forks).' forks' : '').'.';
        }

        if ($issues !== null && $issues !== '') {
            $parts[] = 'Open issues were recorded at about '.number_format((int) $issues).'. A higher count is not automatically bad if triage is active and critical defects close quickly.';
        }

        if ($health !== null && $health !== '') {
            $parts[] = 'Our composite health score currently sits near '.number_format((float) $health, 1)
                .'. That score blends repository activity and related checks so you can sort candidates faster; it is not a certification.';
        }

        if ($repo) {
            $parts[] = 'Always read the upstream repository and recent commits directly. Metrics on this page can lag, and maintainers sometimes move development to a different default branch or organization.';
        }

        return implode(' ', $parts);
    }

    protected function howToEvaluate(string $name, ?string $license, ?string $lang, bool $hasDocker): string
    {
        $parts = [];

        $parts[] = 'A disciplined evaluation of '.$name.' usually follows four steps.';

        $parts[] = 'First, confirm the license and any CLA or enterprise dual-license model matches how you ship software, including internal tools and customer-facing SaaS.';

        $parts[] = 'Second, validate authentication, backup, and upgrade stories. The best feature set fails if restore drills are unclear or major versions require multi-day downtime.';

        $parts[] = 'Third, test integration surface area: APIs, webhooks, SSO, and export formats. Migrations die when data cannot leave cleanly.';

        $parts[] = 'Fourth, estimate total cost of ownership. Cloud subscriptions are visible on an invoice; self-hosting moves cost into people, observability, and incident response.';

        if ($lang) {
            $parts[] = 'Because '.$name.' is primarily associated with '.$lang.', align the pilot with engineers who already operate that stack so review comments stay concrete.';
        }

        if ($hasDocker) {
            $parts[] = 'Use the Compose-oriented path for the first pilot if your policy allows containers, then graduate to hardened production topology only after functional sign-off.';
        }

        return implode(' ', $parts);
    }

    /** @param  Collection<int, string>  $toolNames */
    protected function practicalNextSteps(string $name, ?string $site, ?string $repo, $toolNames): string
    {
        $parts = [];

        $parts[] = 'If '.$name.' looks promising, bookmark this page, star the upstream project only after you have skimmed the README and security policy, and schedule a time-boxed proof of concept with success criteria written down in advance.';

        if ($site) {
            $parts[] = 'The project website and documentation should be your source of truth for install steps and supported versions.';
        }

        if ($repo) {
            $parts[] = 'The public repository remains the best place to inspect issues, pull requests, and release notes before you commit budget.';
        }

        if ($toolNames->isNotEmpty()) {
            $parts[] = 'You can also open the proprietary product pages for '.$this->joinNames($toolNames->all())
                .' on this site to compare other open-source options side by side, then shortlist two candidates instead of ten.';
        }

        $parts[] = 'Finally, involve security and legal early. Open source reduces license fees; it does not remove due diligence. A calm pilot with clear owners beats a rushed production cutover every time.';

        return implode(' ', $parts);
    }

    /** @param  Collection<int, string>  $toolNames */
    protected function depthParagraph(string $name, $toolNames, ?string $lang, ?string $license): string
    {
        return 'Teams researching '.$name.' often compare not only features but governance: who can publish releases, how vulnerabilities are disclosed, and whether commercial support exists for regulated industries. '
            .'Document those answers in your architecture decision record so future hires understand why '.$name.' was chosen'
            .($toolNames->isNotEmpty() ? ' relative to '.$this->joinNames($toolNames->take(2)->all()) : '')
            .'. '
            .'Keep screenshots of admin settings, note the version you tested, and record any plugins required for parity with your current workflow. '
            .'That paper trail shortens audits and prevents silent configuration drift after the excitement of the first successful install fades.'
            .($lang ? ' Language and runtime choices around '.$lang.' should appear in the same record.' : '')
            .($license ? ' License notes for '.$license.' belong there as well.' : '');
    }

    /**
     * @param  Collection<int, string>  $toolNames
     * @return list<array{q: string, a: string}>
     */
    protected function faq(string $name, $toolNames, ?string $license, ?string $lang, $difficulty, bool $hasDocker, $health): array
    {
        $items = [];

        $items[] = [
            'q' => 'Is '.$name.' a full replacement for commercial SaaS?',
            'a' => $toolNames->isNotEmpty()
                ? $name.' can replace parts of stacks such as '.$this->joinNames($toolNames->take(2)->all()).' for many teams, but parity depends on your exact workflow, integrations, and compliance needs. Run a pilot before you commit.'
                : $name.' can cover a large share of needs for teams that accept self-hosting tradeoffs. Validate must-have integrations during a time-boxed trial.',
        ];

        $items[] = [
            'q' => 'How hard is it to self-host '.$name.'?',
            'a' => $difficulty
                ? 'We list self-host difficulty at '.(int) $difficulty.'/5 based on typical install complexity. Your result still depends on SSO, storage, and the skill of the people running it.'
                : 'Difficulty varies by environment. Start with official docs and a non-production host before exposing the service to the public internet.',
        ];

        if ($license) {
            $items[] = [
                'q' => 'What license does '.$name.' use?',
                'a' => 'Our catalog lists '.$license.'. Confirm the upstream LICENSE file and any enterprise terms before production use, especially if you modify or resell the software.',
            ];
        }

        if ($lang) {
            $items[] = [
                'q' => 'What language is '.$name.' built with?',
                'a' => 'Primary language is listed as '.$lang.'. That affects hiring, vulnerability scanning, and how you package the service internally.',
            ];
        }

        $items[] = [
            'q' => 'Should I trust the health score alone?',
            'a' => $health !== null && $health !== ''
                ? 'No. The score (about '.number_format((float) $health, 1).' here) is a sorting aid built from repository and related signals. Always read recent commits, security advisories, and docs.'
                : 'No single score replaces reading the repository, docs, and release history.',
        ];

        if ($hasDocker) {
            $items[] = [
                'q' => 'Can I try '.$name.' with Docker?',
                'a' => 'A Compose-oriented blueprint is referenced on this page to help you spin up a lab instance. Harden networking, secrets, and backups before any production traffic.',
            ];
        }

        return $items;
    }

    /** @param  list<string>  $names */
    protected function joinNames(array $names): string
    {
        $names = array_values(array_filter($names));
        $c = count($names);
        if ($c === 0) {
            return 'comparable commercial tools';
        }
        if ($c === 1) {
            return $names[0];
        }
        if ($c === 2) {
            return $names[0].' and '.$names[1];
        }

        return $names[0].', '.$names[1].', and '.$names[2];
    }

    /**
     * @param  Collection<int, \App\Models\ProprietaryTool>  $tools
     */
    protected function toHtml(string $plain, Collection $tools): string
    {
        $paragraphs = preg_split('/\n{2,}/', trim($plain)) ?: [];
        $html = [];

        foreach ($paragraphs as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            $html[] = '<p>'.$this->linkTools(e($p), $tools).'</p>';
        }

        return implode("\n", $html);
    }

    /**
     * @param  Collection<int, \App\Models\ProprietaryTool>  $tools
     */
    protected function linkTools(string $escapedText, Collection $tools): string
    {
        $sorted = $tools->sortByDesc(fn ($t) => strlen((string) $t->name))->values();

        foreach ($sorted as $tool) {
            $n = (string) $tool->name;
            if ($n === '' || ! $tool->slug) {
                continue;
            }
            $pattern = '/\b'.preg_quote(e($n), '/').'\b/';
            $url = e(route('alternativesto.show', $tool->slug));
            $escapedText = preg_replace(
                $pattern,
                '<a href="'.$url.'" class="text-brand-600 dark:text-brand-400 font-medium hover:underline">'.e($n).'</a>',
                $escapedText,
                1
            ) ?? $escapedText;
        }

        return $escapedText;
    }
}
