<?php

namespace App\Console\Commands;

use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use Illuminate\Console\Command;

class SyncMetricsCommand extends Command
{
    protected $signature = 'alternova:sync-metrics {--limit=0 : Max alternatives to sync (0 = all)}';

    protected $description = 'Sync GitHub metrics and health scores for published alternatives';

    public function handle(): int
    {
        $query = OpenSourceAlternative::query()
            ->whereNotNull('repo_url')
            ->where('repo_url', 'like', '%github.com%')
            ->orderBy('updated_at');

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $alts = $query->get();
        $ok = 0;
        $fail = 0;

        $this->info('Syncing '.$alts->count().' alternative(s)…');

        foreach ($alts as $alt) {
            try {
                SyncGitHubMetricsJob::dispatchSync($alt);
                $ok++;
                $this->line('  ✓ '.$alt->name.' → health '.$alt->fresh()->overall_health_score);
            } catch (\Throwable $e) {
                $fail++;
                $this->error('  ✗ '.$alt->name.': '.$e->getMessage());
            }
        }

        $this->info("Done. OK: {$ok}, failed: {$fail}");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
