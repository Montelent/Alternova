<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DescriptionGeneratorService
{
    /**
     * @return array{description: string, primary_language: ?string, license_type: ?string, website_url: ?string, success: bool, message: string}
     */
    public function fromGitHubRepo(string $repoUrl, ?string $proprietaryName = null): array
    {
        $parsed = $this->parseGitHubUrl($repoUrl);

        if (! $parsed) {
            return [
                'description' => '',
                'primary_language' => null,
                'license_type' => null,
                'website_url' => null,
                'success' => false,
                'message' => 'Could not parse GitHub repository URL. Use https://github.com/owner/repo',
            ];
        }

        [$owner, $repo] = $parsed;

        try {
            $request = Http::timeout(12)
                ->acceptJson()
                ->withHeaders(['User-Agent' => 'Alternova-DescriptionBot/1.0']);

            $token = config('services.github.token') ?: env('GITHUB_TOKEN');
            if ($token) {
                $request = $request->withToken($token);
            }

            $response = $request->get("https://api.github.com/repos/{$owner}/{$repo}");

            if (! $response->successful()) {
                return [
                    'description' => '',
                    'primary_language' => null,
                    'license_type' => null,
                    'website_url' => null,
                    'success' => false,
                    'message' => 'GitHub API error: HTTP '.$response->status(),
                ];
            }

            $data = $response->json();
            $repoDescription = trim((string) ($data['description'] ?? ''));
            $name = $data['name'] ?? $repo;
            $language = $data['language'] ?? null;
            $license = $data['license']['spdx_id'] ?? ($data['license']['name'] ?? null);
            if ($license === 'NOASSERTION') {
                $license = null;
            }
            $homepage = $data['homepage'] ?? null;

            $description = $this->composeOpenSourceDescription(
                $name,
                $repoDescription,
                $proprietaryName,
                $license,
                $language
            );

            return [
                'description' => $description,
                'primary_language' => $language,
                'license_type' => $license,
                'website_url' => $homepage ?: null,
                'success' => true,
                'message' => 'Description generated from GitHub.',
            ];
        } catch (\Throwable $e) {
            Log::warning('GitHub description fetch failed: '.$e->getMessage());

            return [
                'description' => '',
                'primary_language' => null,
                'license_type' => null,
                'website_url' => null,
                'success' => false,
                'message' => 'Failed to reach GitHub: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{description: string, success: bool, message: string}
     */
    public function fromWebsite(string $websiteUrl, string $name = ''): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Alternova-DescriptionBot/1.0'])
                ->get($websiteUrl);

            if (! $response->successful()) {
                return [
                    'description' => $this->composeProprietaryFallback($name),
                    'success' => false,
                    'message' => 'Could not fetch website (HTTP '.$response->status().'). Used a template instead.',
                ];
            }

            $html = $response->body();
            $meta = $this->extractMetaDescription($html);

            if ($meta) {
                $description = $meta;
                if ($name && ! str_contains(strtolower($description), strtolower($name))) {
                    $description = $name.' — '.$description;
                }

                return [
                    'description' => Str::limit($description, 500, ''),
                    'success' => true,
                    'message' => 'Description filled from website meta tag.',
                ];
            }

            return [
                'description' => $this->composeProprietaryFallback($name),
                'success' => true,
                'message' => 'No meta description found. Used a template — please edit.',
            ];
        } catch (\Throwable $e) {
            return [
                'description' => $this->composeProprietaryFallback($name),
                'success' => false,
                'message' => 'Fetch failed: '.$e->getMessage(),
            ];
        }
    }

    protected function composeOpenSourceDescription(
        string $name,
        string $repoDescription,
        ?string $proprietaryName,
        ?string $license,
        ?string $language
    ): string {
        $parts = [];

        if ($proprietaryName) {
            $parts[] = "{$name} is a free, self-hostable open-source alternative to {$proprietaryName}.";
        } else {
            $parts[] = "{$name} is a free, self-hostable open-source project.";
        }

        if ($repoDescription !== '') {
            $parts[] = rtrim($repoDescription, '.').'.';
        }

        $extras = [];
        if ($license) {
            $extras[] = "licensed under {$license}";
        }
        if ($language) {
            $extras[] = "primarily written in {$language}";
        }
        if ($extras) {
            $parts[] = 'It is '.implode(' and ', $extras).'.';
        }

        return Str::limit(implode(' ', $parts), 600, '');
    }

    protected function composeProprietaryFallback(string $name): string
    {
        if ($name === '') {
            return 'A widely used proprietary software product. Add a short description of what it does and who it is for.';
        }

        return "{$name} is a proprietary software product. Add details about its main features and target audience for better comparisons with open-source alternatives.";
    }

    protected function extractMetaDescription(string $html): ?string
    {
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        if (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']description["\']/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    protected function parseGitHubUrl(string $url): ?array
    {
        $url = trim($url);

        // Use ~ delimiter so # in character class is safe
        if (preg_match('~github\.com[:/]([^/\s]+)/([^/\s\.?#]+)~i', $url, $m)) {
            return [$m[1], rtrim($m[2], '/')];
        }

        return null;
    }
}
