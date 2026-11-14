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

        $token = Installer::unlock();

        // Clear cached config so next boot reads .env fresh
        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            @unlink(base_path('bootstrap/cache/config.php'));
        }

        $this->info('Installer access token created.');
        $baseUrl = rtrim((string) config('app.url', ''), '/');
        $installUrl = ($baseUrl !== '' ? $baseUrl : 'https://your-configured-domain')
            .'/install?reinstall_token='.rawurlencode($token);
        $this->line('Open this private URL: '.$installUrl);
        $this->comment('The installer is not publicly accessible without this token. Existing tables are kept unless you drop them manually.');

        return self::SUCCESS;
    }
}
