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
            // HEAD avoids downloading arbitrary remote response bodies. Do not
            // fall back to GET: an untrusted host could return an unbounded file.
            $response = Http::timeout(4)
                ->connectTimeout(3)
                ->withHeaders(['User-Agent' => 'Alternova-LinkChecker/1.0'])
                ->withOptions([
                    'allow_redirects' => PublicHttpUrl::safeRedirectOptions(),
                    'stream' => true,
                ])
                ->head($url);

            $status = $response->status();
            $response->toPsrResponse()->getBody()->close();

            return $response->successful() || in_array($status, [401, 403, 405], true);
        } catch (\Throwable $e) {
            Log::debug('Link check failed.', [
                'host' => parse_url($url, PHP_URL_HOST),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
