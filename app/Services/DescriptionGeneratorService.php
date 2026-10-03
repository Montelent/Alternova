<?php

namespace App\Services;

use App\Support\GitHubUrl;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DescriptionGeneratorService
{
    /** Cache successful GitHub lookups (seconds). */
    public const CACHE_TTL = 21600; // 6 hours

    /**
     * @return array{description: string, primary_language: ?string, license_type: ?string, website_url: ?string, success: bool, message: string}
     */
    public function fromGitHubRepo(string $repoUrl, ?string $proprietaryName = null): array
    {
        $parsed = GitHubUrl::parse($repoUrl);

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
        $cacheKey = 'gh_repo_meta:'.strtolower($owner.'/'.$repo);

        try {
            $cached = Cache::get($cacheKey);

            if (is_array($cached) && isset($cached['name'])) {
                $description = $this->composeOpenSourceDescription(
                    $cached['name'],
                    $cached['repo_description'] ?? '',
                    $proprietaryName,
                    $cached['license'] ?? null,
                    $cached['language'] ?? null
                );

                return [
                    'description' => $description,
                    'primary_language' => $cached['language'] ?? null,
                    'license_type' => $cached['license'] ?? null,
                    'website_url' => $cached['homepage'] ?? null,
                    'success' => true,
                    'message' => 'Description generated from GitHub (cached '.$owner.'/'.$repo.').',
                ];
            }

            $request = Http::timeout(8)
                ->connectTimeout(4)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'Alternova-DescriptionBot/1.0',
                    'Accept' => 'application/vnd.github+json',
                ]);

            $token = config('services.github.token') ?: env('GITHUB_TOKEN');
            if ($token) {
                $request = $request->withToken($token);
            }

            $response = $request->get(GitHubUrl::apiRepoUrl($owner, $repo));

            if (! $response->successful()) {
                $hint = $response->status() === 404
                    ? " Repo not found as {$owner}/{$repo}. Check the URL (dots in names like Rocket.Chat are supported)."
                    : ($response->status() === 403
                        ? ' Rate limited or blocked — add a free GitHub token (Integrations / GITHUB_TOKEN) for much faster responses.'
                        : '');

                return [
                    'description' => '',
                    'primary_language' => null,
                    'license_type' => null,
                    'website_url' => null,
                    'success' => false,
                    'message' => 'GitHub API error: HTTP '.$response->status().'.'.$hint,
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
            if (is_string($homepage)) {
                $homepage = trim($homepage) ?: null;
            } else {
                $homepage = null;
            }

            Cache::put($cacheKey, [
                'name' => $name,
                'repo_description' => $repoDescription,
                'language' => $language,
                'license' => $license,
                'homepage' => $homepage,
            ], self::CACHE_TTL);

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
                'website_url' => $homepage,
                'success' => true,
                'message' => 'Description generated from GitHub ('.$owner.'/'.$repo.').',
            ];
        } catch (\Throwable $e) {
            Log::warning('GitHub description fetch failed: '.$e->getMessage());

            return [
                'description' => '',
                'primary_language' => null,
                'license_type' => null,
                'website_url' => null,
                'success' => false,
                'message' => 'Failed to reach GitHub (timeout or network). Try again, or set GITHUB_TOKEN for reliability. '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{description: string, success: bool, message: string}
     */
    public function fromWebsite(string $websiteUrl, string $name = ''): array
    {
        try {
            $response = Http::timeout(6)
                ->connectTimeout(3)
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
}
