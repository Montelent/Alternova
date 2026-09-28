<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Support\Str;

class AlternativeCsvImporter
{
    /**
     * Expected CSV headers (case-insensitive):
     * proprietary_name, alternative_name, repo_url, website_url, description, license_type, difficulty, language, published, featured
     *
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function importFromString(string $csv): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csv)) ?: [];
        if (count($lines) < 2) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['CSV needs a header row and at least one data row.']];
        }

        $header = str_getcsv(array_shift($lines));
        $header = array_map(fn ($h) => Str::snake(strtolower(trim((string) $h))), $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($lines as $i => $line) {
            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv($line);
            if (count($row) === 1 && $row[0] === null) {
                continue;
            }

            $data = [];
            foreach ($header as $idx => $key) {
                $data[$key] = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
            }

            $propName = $data['proprietary_name'] ?? $data['proprietary'] ?? '';
            $altName = $data['alternative_name'] ?? $data['name'] ?? '';
            $repo = $data['repo_url'] ?? $data['repo'] ?? '';

            if ($propName === '' || $altName === '' || $repo === '') {
                $skipped++;
                $errors[] = 'Row '.($i + 2).': missing proprietary_name, alternative_name, or repo_url';

                continue;
            }

            $tool = ProprietaryTool::query()->firstOrCreate(
                ['slug' => Str::slug($propName)],
                [
                    'name' => $propName,
                    'is_published' => true,
                    'description' => $propName.' (imported for open-source comparison).',
                ]
            );

            $slug = Str::slug($altName);
            $base = $slug;
            $n = 1;
            while (OpenSourceAlternative::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$n++;
            }

            $published = $this->toBool($data['published'] ?? $data['is_published'] ?? '1');
            $featured = $this->toBool($data['featured'] ?? $data['is_featured'] ?? '0');

            OpenSourceAlternative::create([
                'proprietary_tool_id' => $tool->id,
                'name' => $altName,
                'slug' => $slug,
                'repo_url' => $repo,
                'website_url' => $data['website_url'] ?? $data['website'] ?? null,
                'description' => $data['description'] ?? null,
                'license_type' => $data['license_type'] ?? $data['license'] ?? null,
                'self_host_difficulty' => max(1, min(5, (int) ($data['difficulty'] ?? $data['self_host_difficulty'] ?? 3))),
                'primary_language' => $data['language'] ?? $data['primary_language'] ?? null,
                'overall_health_score' => 0,
                'is_published' => $published,
                'is_featured' => $featured,
            ]);

            $imported++;
        }

        return compact('imported', 'skipped', 'errors');
    }

    protected function toBool(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'y', 'on'], true);
    }
}
