<?php

namespace App\Console\Commands;

use App\Support\Installer;
use Illuminate\Console\Command;

class ReinstallCommand extends Command
{
    protected $signature = 'alternova:reinstall {--force : Skip confirmation}';

    protected $description = 'Unlock the installer so you can run /install again (does not drop the database)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will unlock the installer. Continue?')) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        Installer::unlock();

        // Clear cached config so next boot reads .env fresh
        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            @unlink(base_path('bootstrap/cache/config.php'));
        }

        $this->info('Installer unlocked.');
        $this->line('Visit: ' . rtrim(config('app.url', ''), '/') . '/install');
        $this->comment('Tip: enter your MySQL credentials again on step 2. Existing tables are kept unless you drop them manually.');

        return self::SUCCESS;
    }
}
