<?php

namespace App\Support;

/**
 * Parse github.com owner/repo from common URL shapes.
 */
class GitHubUrl
{
    /**
     * @return array{0: string, 1: string}|null  [owner, repo]
     */
    public static function parse(string $url): ?array
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        // Accept https://github.com/owner/repo, git@github.com:owner/repo.git, etc.
        // Repo names may contain dots (Rocket.Chat), underscores, hyphens.
        if (! preg_match(
            '~(?:github\.com[:/]|git@github\.com:)(?P<owner>[A-Za-z0-9_.-]+)/(?P<repo>[A-Za-z0-9_.-]+?)(?:\.git)?(?:[/#?]|$)~i',
            $url,
            $m
        )) {
            return null;
        }

        $owner = $m['owner'];
        $repo = $m['repo'];

        if ($owner === '' || $repo === '') {
            return null;
        }

        return [$owner, $repo];
    }

    public static function apiRepoUrl(string $owner, string $repo): string
    {
        return sprintf(
            'https://api.github.com/repos/%s/%s',
            rawurlencode($owner),
            rawurlencode($repo)
        );
    }
}
