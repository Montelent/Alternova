<?php

namespace App\Console\Commands;

use App\Models\OpenSourceAlternative;
use App\Services\LinkHealthService;
use Illuminate\Console\Command;

class CheckLinksCommand extends Command
{
    protected $signature = 'alternova:check-links {--limit=0}';

    protected $description = 'HTTP-check repo and website URLs for alternatives';

    public function handle(LinkHealthService $service): int
    {
        $query = OpenSourceAlternative::query()->orderBy('links_checked_at');
        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $alts = $query->get();
        $broken = 0;

        foreach ($alts as $alt) {
            $service->checkAlternative($alt);
            $fresh = $alt->fresh();
            if ($fresh->hasBrokenLinks()) {
                $broken++;
                $this->warn('  Broken: '.$alt->name);
            } else {
                $this->line('  OK: '.$alt->name);
            }
        }

        $this->info("Checked {$alts->count()}, broken: {$broken}");

        return self::SUCCESS;
    }
}
