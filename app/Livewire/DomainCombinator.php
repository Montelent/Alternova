<?php

namespace App\Livewire;

use App\Models\DomainSearchLog;
use App\Models\SavedDomain;
use App\Services\DomainCheckService;
use App\Services\DomainCombinatorService;
use App\Services\SeoManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Support\CsvSafe;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DomainCombinator extends Component
{
    public array $keywords = [];

    public string $keywordInput = '';

    public string $prefixInput = '';

    public string $suffixInput = '';

    public array $prefixes = ['get', 'my', 'the', 'try', 'use'];

    public array $suffixes = ['ly', 'ify', 'hub', 'app', 'hq'];

    public array $selectedTlds = ['com', 'io', 'dev', 'app', 'co'];

    public int $maxLength = 15;

    public int $maxSyllables = 4;

    public int $minBrandability = 40;

    public array $results = [];

    public bool $isGenerating = false;

    public bool $isChecking = false;

    public array $saved = [];

    public string $errorMessage = '';

    public function mount(): void
    {
        $this->loadSaved();
    }

    protected function combinator(): DomainCombinatorService
    {
        return app(DomainCombinatorService::class);
    }

    protected function checker(): DomainCheckService
    {
        return app(DomainCheckService::class);
    }

    protected function sessionKey(): string
    {
        return substr(hash('sha256', session()->getId() ?: (string) request()->ip()), 0, 64);
    }

    public function loadSaved(): void
    {
        try {
            $q = SavedDomain::query();
            if (Auth::id()) {
                $q->where(function ($inner) {
                    $inner->where('user_id', Auth::id())
                        ->orWhere('session_id', $this->sessionKey());
                });
            } else {
                $q->where('session_id', $this->sessionKey());
            }

            $this->saved = $q->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->map(fn (SavedDomain $d) => [
                    'domain' => $d->domain,
                    'score' => $d->brandability,
                    'status' => $d->status,
                ])
                ->unique('domain')
                ->values()
                ->all();
        } catch (\Throwable) {
            $this->saved = [];
        }
    }

    public function toggleSave(string $domain, int $score = 0, string $status = ''): void
    {
        $domain = strtolower(trim($domain));
        $allowedTlds = ['com', 'net', 'org', 'io', 'dev', 'app', 'co', 'ai', 'xyz', 'me'];
        $tld = strtolower(substr(strrchr($domain, '.') ?: '', 1));
        if (strlen($domain) > 253
            || ! in_array($tld, $allowedTlds, true)
            || ! preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\\.)+[a-z](?:[a-z0-9-]{0,61}[a-z0-9])$/i', $domain)
        ) {
            $this->errorMessage = 'Invalid domain name.';

            return;
        }

        $score = max(0, min(100, $score));
        $status = in_array($status, ['available', 'taken', 'unknown', 'pending'], true) ? $status : 'unknown';

        try {
            $sid = $this->sessionKey();
            $uid = Auth::id();

            $q = SavedDomain::query()->where('domain', $domain);
            if ($uid) {
                $q->where(function ($inner) use ($uid, $sid) {
                    $inner->where('user_id', $uid)->orWhere('session_id', $sid);
                });
            } else {
                $q->where('session_id', $sid);
            }

            $existing = $q->first();

            if ($existing) {
                $existing->delete();
            } else {
                SavedDomain::create([
                    'session_id' => $sid,
                    'user_id' => $uid,
                    'domain' => $domain,
                    'brandability' => $score ?: null,
                    'status' => $status ?: null,
                ]);
            }

            $this->loadSaved();
        } catch (\Throwable $e) {
            Log::warning('Saved domain update failed: '.$e->getMessage());
            $this->errorMessage = 'Could not save this domain right now.';
        }
    }

    public function clearSaved(): void
    {
        try {
            $q = SavedDomain::query();
            if (Auth::id()) {
                $q->where(function ($inner) {
                    $inner->where('user_id', Auth::id())
                        ->orWhere('session_id', $this->sessionKey());
                });
            } else {
                $q->where('session_id', $this->sessionKey());
            }
            $q->delete();
            $this->saved = [];
        } catch (\Throwable) {
            $this->saved = [];
        }
    }

    public function isSaved(string $domain): bool
    {
        return collect($this->saved)->contains(fn ($s) => ($s['domain'] ?? '') === $domain);
    }

    public function addKeyword(): void
    {
        $kw = strtolower(trim(preg_replace('/[^a-z0-9\s-]/i', '', $this->keywordInput) ?? ''));
        $kw = str_replace([' ', '-'], '', $kw);
        if ($kw !== '' && ! in_array($kw, $this->keywords, true) && count($this->keywords) < 8) {
            $this->keywords[] = $kw;
        }
        $this->keywordInput = '';
    }

    public function removeKeyword(string $keyword): void
    {
        $this->keywords = array_values(array_filter($this->keywords, fn ($k) => $k !== $keyword));
    }

    public function addPrefix(): void
    {
        $p = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $this->prefixInput) ?? ''));
        if ($p !== '' && ! in_array($p, $this->prefixes, true) && count($this->prefixes) < 12) {
            $this->prefixes[] = $p;
        }
        $this->prefixInput = '';
    }

    public function removePrefix(string $prefix): void
    {
        $this->prefixes = array_values(array_filter($this->prefixes, fn ($x) => $x !== $prefix));
    }

    public function addSuffix(): void
    {
        $s = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $this->suffixInput) ?? ''));
        if ($s !== '' && ! in_array($s, $this->suffixes, true) && count($this->suffixes) < 12) {
            $this->suffixes[] = $s;
        }
        $this->suffixInput = '';
    }

    public function removeSuffix(string $suffix): void
    {
        $this->suffixes = array_values(array_filter($this->suffixes, fn ($x) => $x !== $suffix));
    }

    /**
     * Generate names only (no network). Availability runs in checkNextBatch via poll.
     */
    public function generate(): void
    {
        $this->errorMessage = '';
        $this->results = [];
        $this->isChecking = false;

        try {
            $key = 'domain-gen:'.(request()->ip() ?: 'cli');
            if (RateLimiter::tooManyAttempts($key, 40)) {
                $this->errorMessage = 'Rate limit: max 40 generations per hour from this network. Try later.';

                return;
            }
            RateLimiter::hit($key, 3600);

            // Livewire public properties are client-controlled. Re-apply the
            // UI limits server-side before doing combinatorial work.
            $cleanList = static function (array $values, int $maxItems, int $maxLength): array {
                $clean = [];
                foreach (array_slice($values, 0, $maxItems) as $value) {
                    if (! is_string($value)) {
                        continue;
                    }
                    $value = substr(strtolower(preg_replace('/[^a-z0-9]/i', '', $value) ?? ''), 0, $maxLength);
                    if ($value !== '') {
                        $clean[] = $value;
                    }
                }

                return array_values(array_unique($clean));
            };

            $this->keywords = $cleanList($this->keywords, 8, 32);
            $this->prefixes = $cleanList($this->prefixes, 12, 16);
            $this->suffixes = $cleanList($this->suffixes, 12, 16);
            $allowedTlds = ['com', 'net', 'org', 'io', 'dev', 'app', 'co', 'ai', 'xyz', 'me'];
            $requestedTlds = array_filter($this->selectedTlds, 'is_string');
            $this->selectedTlds = array_values(array_unique(array_intersect(
                $allowedTlds,
                array_map(fn ($tld) => strtolower(ltrim($tld, '.')), $requestedTlds)
            )));
            $this->maxLength = max(4, min(30, (int) $this->maxLength));
            $this->minBrandability = max(0, min(100, (int) $this->minBrandability));

            // Auto-add typed keyword if user forgot to click Add
            if ($this->keywordInput !== '') {
                $this->addKeyword();
            }

            if (count($this->keywords) < 1) {
                $this->errorMessage = 'Add at least one seed keyword, then click Generate.';

                return;
            }

            if (count($this->selectedTlds) < 1) {
                $this->errorMessage = 'Select at least one TLD (e.g. .com).';

                return;
            }

            $raw = $this->combinator()->generateCombinations(
                $this->keywords,
                $this->prefixes,
                $this->suffixes,
                $this->selectedTlds
            );

            $filtered = [];
            foreach ($raw as $domain) {
                $name = explode('.', $domain)[0] ?? '';
                if ($name === '' || strlen($name) > $this->maxLength) {
                    continue;
                }

                $score = $this->combinator()->calculateBrandabilityScore($domain);
                if ($score < $this->minBrandability) {
                    continue;
                }

                $filtered[] = [
                    'domain' => $domain,
                    'score' => $score,
                    'status' => 'pending',
                    'affiliate' => [],
                ];
            }

            usort($filtered, fn ($a, $b) => $b['score'] <=> $a['score']);
            $this->results = array_slice($filtered, 0, 36);

            if ($this->results === []) {
                $this->errorMessage = 'No names matched your filters. Try lowering min brandability or increasing max length.';

                return;
            }

            try {
                DomainSearchLog::create([
                    'seed_keywords' => $this->keywords,
                    'selected_tlds' => $this->selectedTlds,
                    'domain_generated_count' => count($this->results),
                    'ip_address' => request()->ip(),
                    'user_id' => auth()->id(),
                ]);
            } catch (\Throwable) {
            }

            // Availability is checked in follow-up requests (poll), not here — avoids PHP timeouts.
            $this->isChecking = true;
        } catch (\Throwable $e) {
            Log::warning('Domain generate failed: '.$e->getMessage());
            $this->errorMessage = 'Generation failed. Please check your inputs and try again.';
            $this->results = [];
            $this->isChecking = false;
        }
    }

    public function checkNextBatch(): void
    {
        if ($this->results === []) {
            $this->isChecking = false;

            return;
        }

        $checkKey = 'domain-check:'.(request()->ip() ?: 'unknown');
        if (RateLimiter::tooManyAttempts($checkKey, 30)) {
            $this->errorMessage = 'Availability checks are temporarily rate-limited. Please wait a minute.';
            $this->isChecking = false;

            return;
        }
        RateLimiter::hit($checkKey, 60);

        $pending = collect($this->results)
            ->where('status', 'pending')
            ->take(5)
            ->pluck('domain')
            ->all();

        if ($pending === []) {
            $this->isChecking = false;

            return;
        }

        $this->isChecking = true;

        foreach ($this->results as &$item) {
            if (in_array($item['domain'], $pending, true) && ($item['status'] ?? '') === 'pending') {
                $item['status'] = 'checking';
            }
        }
        unset($item);

        try {
            $checked = $this->checker()->checkMany($pending);
        } catch (\Throwable $e) {
            Log::debug('Domain batch check error: '.$e->getMessage());
            // Mark as unknown so UI does not spin forever
            foreach ($this->results as &$item) {
                if (in_array($item['domain'], $pending, true)) {
                    $item['status'] = 'unknown';
                }
            }
            unset($item);
            $this->isChecking = collect($this->results)->contains(
                fn ($r) => in_array($r['status'] ?? '', ['pending', 'checking'], true)
            );

            return;
        }

        foreach ($this->results as &$item) {
            $d = $item['domain'];
            if (! isset($checked[$d])) {
                continue;
            }
            $item['status'] = $checked[$d]['status'] ?? 'unknown';
            if (($item['status'] ?? '') === 'available') {
                $item['affiliate'] = $this->checker()->affiliateLinks($d);
            }
        }
        unset($item);

        $this->isChecking = collect($this->results)->contains(
            fn ($r) => in_array($r['status'] ?? '', ['pending', 'checking'], true)
        );
    }

    public function exportCsv(): StreamedResponse
    {
        $available = collect($this->results)->where('status', 'available');

        return response()->streamDownload(function () use ($available) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['domain', 'brandability_score']);
            foreach ($available as $item) {
                fputcsv($handle, CsvSafe::row([$item['domain'], $item['score']]));
            }
            fclose($handle);
        }, 'available-domains.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportTxt(): StreamedResponse
    {
        $available = collect($this->results)->where('status', 'available')->pluck('domain');

        return response()->streamDownload(function () use ($available) {
            echo $available->implode(PHP_EOL);
        }, 'available-domains.txt', [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function exportSavedTxt(): StreamedResponse
    {
        $domains = collect($this->saved)->pluck('domain');

        return response()->streamDownload(function () use ($domains) {
            echo $domains->implode(PHP_EOL);
        }, 'saved-domains.txt', [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function render()
    {
        $seo = app(SeoManager::class);

        return view('livewire.domain-combinator', [
            'availableTlds' => ['com', 'net', 'org', 'io', 'dev', 'app', 'co', 'ai', 'xyz', 'me'],
        ])->layout('layouts.app', [
            'title' => $seo->pageTitle('domains', 'Domain Name Idea Combinator'),
            'description' => 'Generate brandable domain ideas from seed keywords, score them, and check availability in real time.',
            'canonical' => route('domains'),
        ]);
    }
}
