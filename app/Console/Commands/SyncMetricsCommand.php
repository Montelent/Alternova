<?php

namespace App\Console\Commands;

use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use Illuminate\Console\Command;

class SyncMetricsCommand extends Command
{
    protected $signature = 'app:sync-metrics
                            {--id= : Sync a specific alternative by ID}
                            {--limit= : Limit number of alternatives to sync}';

    protected $description = 'Dispatch jobs to sync GitHub metrics for all published open-source alternatives';

    public function handle(): int
    {
        $query = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->whereNotNull('repo_url');

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $alternatives = $query->get();

        if ($alternatives->isEmpty()) {
            $this->warn('No alternatives found to sync.');
            return self::SUCCESS;
        }

        $this->info("Dispatching sync jobs for {$alternatives->count()} alternative(s)...");

        $bar = $this->output->createProgressBar($alternatives->count());
        $bar->start();

        foreach ($alternatives as $alternative) {
            SyncGitHubMetricsJob::dispatch($alternative);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All sync jobs have been dispatched to the queue.');

        return self::SUCCESS;
    }
}
