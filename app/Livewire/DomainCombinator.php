<?php

namespace App\Livewire;

use App\Models\DomainSearchLog;
use App\Services\DomainCheckService;
use App\Services\DomainCombinatorService;
use Livewire\Component;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DomainCombinator extends Component
{
    #[Url]
    public array $keywords = [];

    public string $keywordInput = '';

    public array $prefixes = ['get', 'my', 'the', 'try', 'use'];

    public array $suffixes = ['ly', 'ify', 'hub', 'app', 'hq', 'io'];

    public array $selectedTlds = ['com', 'io', 'dev', 'app', 'co'];

    public int $maxLength = 15;

    public int $maxSyllables = 4;

    public int $minBrandability = 50;

    public array $results = [];

    public bool $isGenerating = false;

    public bool $isChecking = false;

    protected DomainCombinatorService $combinator;
    protected DomainCheckService $checker;

    public function boot(DomainCombinatorService $combinator, DomainCheckService $checker): void
    {
        $this->combinator = $combinator;
        $this->checker = $checker;
    }

    public function addKeyword(): void
    {
        $kw = trim(strtolower($this->keywordInput));
        if ($kw && !in_array($kw, $this->keywords) && count($this->keywords) < 8) {
            $this->keywords[] = $kw;
        }
        $this->keywordInput = '';
    }

    public function removeKeyword(string $keyword): void
    {
        $this->keywords = array_values(array_filter($this->keywords, fn ($k) => $k !== $keyword));
    }

    public function generate(): void
    {
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

        // Sort by brandability desc and limit
        usort($filtered, fn ($a, $b) => $b['score'] <=> $a['score']);
        $this->results = array_slice($filtered, 0, 60);

        // Log the search
        DomainSearchLog::create([
            'seed_keywords' => $this->keywords,
            'selected_tlds' => $this->selectedTlds,
            'domain_generated_count' => count($this->results),
            'ip_address' => request()->ip(),
            'user_id' => auth()->id(),
        ]);

        $this->isGenerating = false;

        // Kick off async checks via Livewire events or polling
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

    public function render()
    {
        return view('livewire.domain-combinator', [
            'availableTlds' => ['com', 'net', 'org', 'io', 'dev', 'app', 'co', 'ai', 'xyz', 'me'],
        ])->layout('layouts.app', ['title' => 'Domain Name Idea Combinator']);
    }
}
