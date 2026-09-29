<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;

class AlternativeMarkdownExporter
{
    public function export(OpenSourceAlternative $alt): string
    {
        $alt->loadMissing(['proprietaryTool', 'repoMetric', 'tags']);

        $lines = [
            '# '.$alt->name,
            '',
            '> Open-source alternative to **'.($alt->proprietaryTool?->name ?? 'proprietary software').'**',
            '',
            '- **Slug:** `'.$alt->slug.'`',
            '- **License:** '.($alt->license_type ?? '—'),
            '- **Language:** '.($alt->primary_language ?? '—'),
            '- **Health score:** '.number_format((float) $alt->overall_health_score, 1),
            '- **Self-host difficulty:** '.$alt->self_host_difficulty.'/5',
            '- **Repo:** '.($alt->repo_url ?? '—'),
            '- **Website:** '.($alt->website_url ?? '—'),
        ];

        if ($alt->repoMetric) {
            $m = $alt->repoMetric;
            $lines[] = '- **GitHub stars:** '.number_format((int) $m->github_stars);
            $lines[] = '- **Forks:** '.number_format((int) $m->github_forks);
            $lines[] = '- **Open issues:** '.number_format((int) $m->open_issues);
        }

        $cats = $alt->tags->where('type', 'category')->pluck('name')->all();
        if ($cats) {
            $lines[] = '- **Categories:** '.implode(', ', $cats);
        }

        $lines[] = '';
        $lines[] = '## Description';
        $lines[] = '';
        $lines[] = trim((string) $alt->description) ?: '_No description._';

        if (! empty($alt->pros)) {
            $lines[] = '';
            $lines[] = '## Pros';
            foreach ((array) $alt->pros as $p) {
                $lines[] = '- '.$p;
            }
        }

        if (! empty($alt->cons)) {
            $lines[] = '';
            $lines[] = '## Cons';
            foreach ((array) $alt->cons as $c) {
                $lines[] = '- '.$c;
            }
        }

        if ($alt->docker_compose_blueprint) {
            $lines[] = '';
            $lines[] = '## Docker Compose';
            $lines[] = '';
            $lines[] = '```yaml';
            $lines[] = rtrim((string) $alt->docker_compose_blueprint);
            $lines[] = '```';
        }

        if ($alt->changelog) {
            $lines[] = '';
            $lines[] = '## Changelog';
            $lines[] = '';
            $lines[] = trim((string) $alt->changelog);
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '_Exported from Alternova on '.now()->toDateString().'_';

        return implode("\n", $lines)."\n";
    }
}
