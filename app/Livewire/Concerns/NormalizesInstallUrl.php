<?php

namespace App\Livewire\Concerns;

trait NormalizesInstallUrl
{
    public function normalizeAppUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return $url;
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['host'])) {
            return rtrim($url, '/');
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        if ($scheme !== 'http' && $scheme !== 'https') {
            $scheme = 'https';
        }

        $host = strtolower((string) $parts['host']);
        if ($scheme === 'http' && ! $this->isLocalHostName($host)) {
            $scheme = 'https';
        }

        $path = isset($parts['path']) ? rtrim((string) $parts['path'], '/') : '';
        if ($path === '/' || $path === '') {
            $path = '';
        }

        return $scheme.'://'.$host.$path;
    }

    /**
     * @return list<string>
     */
    public function urlHostChoices(): array
    {
        $host = parse_url($this->app_url, PHP_URL_HOST) ?: request()->getHost();
        $host = strtolower((string) $host);
        $bare = preg_replace('/^www\./i', '', $host) ?: $host;
        $scheme = parse_url($this->app_url, PHP_URL_SCHEME) ?: 'https';
        $scheme = strtolower((string) $scheme);
        if ($scheme !== 'http' && $scheme !== 'https') {
            $scheme = 'https';
        }
        if ($scheme === 'http' && ! $this->isLocalHostName($bare)) {
            $scheme = 'https';
        }

        return array_values(array_unique([
            $scheme.'://'.$bare,
            $scheme.'://www.'.$bare,
        ]));
    }

    public function selectPreferredHost(string $url): void
    {
        $this->app_url = $this->normalizeAppUrl($url);
    }

    protected function isLocalHostName(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');
    }
}
