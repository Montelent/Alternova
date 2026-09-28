<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
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
        try {
            $response = Http::timeout(8)
                ->connectTimeout(5)
                ->withHeaders(['User-Agent' => 'Alternova-LinkChecker/1.0'])
                ->withOptions(['allow_redirects' => true])
                ->head($url);

            if ($response->successful() || in_array($response->status(), [401, 403, 405], true)) {
                return true;
            }

            // Some hosts block HEAD — try GET
            $get = Http::timeout(8)
                ->connectTimeout(5)
                ->withHeaders(['User-Agent' => 'Alternova-LinkChecker/1.0'])
                ->get($url);

            return $get->successful() || in_array($get->status(), [401, 403], true);
        } catch (\Throwable $e) {
            Log::debug('Link check failed for '.$url.': '.$e->getMessage());

            return false;
        }
    }
}
