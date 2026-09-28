<?php

namespace App\Livewire;

use App\Models\DomainSearchLog;
use App\Models\SavedDomain;
use App\Services\DomainCheckService;
use App\Services\DomainCombinatorService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DomainCombinator extends Component
{
    #[Url]
    public array $keywords = [];

    public string $keywordInput = '';

    public string $prefixInput = '';

    public string $suffixInput = '';

    public array $prefixes = ['get', 'my', 'the', 'try', 'use'];

    public array $suffixes = ['ly', 'ify', 'hub', 'app', 'hq', 'io'];

    public array $selectedTlds = ['com', 'io', 'dev', 'app', 'co'];

    public int $maxLength = 15;

    public int $maxSyllables = 4;

    public int $minBrandability = 50;

    public array $results = [];

    public bool $isGenerating = false;

    public array $saved = [];

    public string $errorMessage = '';

    protected DomainCombinatorService $combinator;

    protected DomainCheckService $checker;

    public function boot(DomainCombinatorService $combinator, DomainCheckService $checker): void
    {
        $this->combinator = $combinator;
        $this->checker = $checker;
    }

    public function mount(): void
    {
        $this->loadSaved();
    }

    protected function sessionKey(): string
    {
        return substr(hash('sha256', session()->getId() ?: request()->ip()), 0, 64);
    }

    public function loadSaved(): void
    {
        try {
            $this->saved = SavedDomain::query()
                ->where('session_id', $this->sessionKey())
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->map(fn (SavedDomain $d) => [
                    'domain' => $d->domain,
                    'score' => $d->brandability,
                    'status' => $d->status,
                ])
                ->all();
        } catch (\Throwable) {
            $this->saved = [];
        }
    }

    public function toggleSave(string $domain, int $score = 0, string $status = ''): void
    {
        $sid = $this->sessionKey();

        $existing = SavedDomain::query()
            ->where('session_id', $sid)
            ->where('domain', $domain)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            SavedDomain::create([
                'session_id' => $sid,
                'domain' => $domain,
                'brandability' => $score ?: null,
                'status' => $status ?: null,
            ]);
        }

        $this->loadSaved();
    }

    public function clearSaved(): void
    {
        SavedDomain::query()->where('session_id', $this->sessionKey())->delete();
        $this->saved = [];
    }

    public function isSaved(string $domain): bool
    {
        return collect($this->saved)->contains(fn ($s) => $s['domain'] === $domain);
    }

    public function addKeyword(): void
    {
        $kw = trim(strtolower($this->keywordInput));
        if ($kw && ! in_array($kw, $this->keywords) && count($this->keywords) < 8) {
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
        $p = trim(strtolower(preg_replace('/[^a-z0-9]/i', '', $this->prefixInput) ?? ''));
        if ($p && ! in_array($p, $this->prefixes, true) && count($this->prefixes) < 12) {
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
        $s = trim(strtolower(preg_replace('/[^a-z0-9]/i', '', $this->suffixInput) ?? ''));
        if ($s && ! in_array($s, $this->suffixes, true) && count($this->suffixes) < 12) {
            $this->suffixes[] = $s;
        }
        $this->suffixInput = '';
    }

    public function removeSuffix(string $suffix): void
    {
        $this->suffixes = array_values(array_filter($this->suffixes, fn ($x) => $x !== $suffix));
    }

    public function generate(): void
    {
        $this->errorMessage = '';

        $key = 'domain-gen:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->errorMessage = 'Rate limit: max 20 generations per hour from this network. Try later.';

            return;
        }
        RateLimiter::hit($key, 3600);

        $this->validate([
            'keywords' => 'required|array|min:1|max:8',
            'selectedTlds' => 'required|array|min:1',
        ]);

        $this->isGenerating = true;
        $this->results = [];

        $raw = $this->combinator->generateCombinations(
            $this->keywords,
            $this->prefixes,
            $this->suffixes,
            $this->selectedTlds
        );

        $filtered = [];
        foreach ($raw as $domain) {
            $name = explode('.', $domain)[0];
            if (strlen($name) > $this->maxLength) {
                continue;
            }

            $score = $this->combinator->calculateBrandabilityScore($domain);
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
        $this->results = array_slice($filtered, 0, 60);

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

        $this->isGenerating = false;
        $this->dispatch('domains-generated');
    }

    public function checkDomain(string $domain): void
    {
        $result = $this->checker->check($domain);

        foreach ($this->results as &$item) {
            if ($item['domain'] === $domain) {
                $item['status'] = $result['status'];
                if ($result['status'] === 'available') {
                    $item['affiliate'] = $this->checker->affiliateLinks($domain);
                }
                break;
            }
        }
    }

    public function checkNextBatch(): void
    {
        $pending = collect($this->results)
            ->where('status', 'pending')
            ->take(5)
            ->pluck('domain');

        foreach ($pending as $domain) {
            $this->checkDomain($domain);
        }
    }

    public function exportCsv(): StreamedResponse
    {
        $available = collect($this->results)->where('status', 'available');

        return response()->streamDownload(function () use ($available) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['domain', 'brandability_score']);
            foreach ($available as $item) {
                fputcsv($handle, [$item['domain'], $item['score']]);
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
        return view('livewire.domain-combinator', [
            'availableTlds' => ['com', 'net', 'org', 'io', 'dev', 'app', 'co', 'ai', 'xyz', 'me'],
        ])->layout('layouts.app', [
            'title' => 'Domain Name Idea Combinator | Alternova',
            'description' => 'Generate brandable domain ideas from seed keywords, score them, and check availability in real time.',
        ]);
    }
}
