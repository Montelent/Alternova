<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use App\Support\PublicHttpUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkHealthService
{
    public function checkAlternative(OpenSourceAlternative $alternative): OpenSourceAlternative
    {
        $repoOk = $alternative->repo_url
            ? $this->isReachable($alternative->repo_url)
            : null;

        $siteOk = $alternative->website_url
            ? $this->isReachable($alternative->website_url)
            : null;

        $alternative->update([
            'repo_reachable' => $repoOk,
            'website_reachable' => $siteOk,
            'links_checked_at' => now(),
        ]);

        return $alternative->fresh();
    }

    public function isReachable(string $url): bool
    {
        if (! PublicHttpUrl::isSafe($url)) {
            return false;
        }

        try {
            $response = Http::timeout(4)
                ->connectTimeout(3)
                ->withHeaders(['User-Agent' => 'Alternova-LinkChecker/1.0'])
                ->withOptions(['allow_redirects' => PublicHttpUrl::safeRedirectOptions()])
                ->head($url);

            if ($response->successful() || in_array($response->status(), [401, 403, 405], true)) {
                return true;
            }

            // Some hosts block HEAD — try GET once with short timeout
            $get = Http::timeout(4)
                ->connectTimeout(3)
                ->withHeaders(['User-Agent' => 'Alternova-LinkChecker/1.0'])
                ->withOptions(['allow_redirects' => PublicHttpUrl::safeRedirectOptions()])
                ->get($url);

            return $get->successful() || in_array($get->status(), [401, 403], true);
        } catch (\Throwable $e) {
            Log::debug('Link check failed.', [
                'host' => parse_url($url, PHP_URL_HOST),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
