<?php

namespace App\Livewire;

use App\Livewire\Concerns\NormalizesInstallUrl;
use App\Models\User;
use App\Support\Installer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.install')]
class InstallWizard extends Component
{
    use NormalizesInstallUrl;

    #[Locked]
    public int $step = 1;

    public array $requirements = [];

    public bool $requirementsMet = false;

    public string $app_name = 'Alternova';

    public string $app_url = '';

    public string $db_connection = 'mysql';

    public string $db_host = '127.0.0.1';

    public string $db_port = '3306';

    public string $db_database = '';

    public string $db_username = '';

    public string $db_password = '';

    public ?string $dbTestMessage = null;

    public bool $dbTestSuccess = false;

    public ?string $migrateOutput = null;

    public bool $migrateSuccess = false;

    public string $admin_name = '';

    public string $admin_email = '';

    public string $admin_password = '';

    public string $admin_password_confirmation = '';

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->ensureInstallerOpen();

        $this->app_url = $this->normalizeAppUrl(Installer::detectAppUrl());
        Installer::ensureStorageDirectories();
        Installer::ensureEnvFile();
        Installer::ensureInstallSafeDrivers();
        Installer::ensureAppKey();
        $this->refreshRequirements();
    }

    public function refreshRequirements(): void
    {
        $this->ensureInstallerOpen();
        Installer::ensureStorageDirectories();
        $this->requirements = Installer::requirements();
        $this->requirementsMet = Installer::allRequirementsMet();
    }

    public function updatedDbConnection(string $value): void
    {
        $this->ensureInstallerOpen();
        $this->db_port = $value === 'mysql' ? '3306' : '5432';
    }

    public function testDatabase(): void
    {
        $this->ensureInstallerOpen();
        $this->validate([
            'db_connection' => ['required', 'in:pgsql,mysql'],
            'db_host' => ['required', 'string', 'max:253', 'regex:/\\A(?:localhost|[A-Za-z0-9.-]+|\\[[0-9A-Fa-f:]+\\]|[0-9A-Fa-f:]+)\\z/'],
            'db_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:128', 'regex:/\\A[A-Za-z0-9_.-]+\\z/'],
            'db_username' => ['required', 'string', 'max:128'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        $result = Installer::testDatabaseConnection(
            $this->db_connection,
            $this->db_host,
            $this->db_port,
            $this->db_database,
            $this->db_username,
            $this->db_password
        );

        $this->dbTestSuccess = $result['success'];
        $this->dbTestMessage = $result['message'];
    }

    public function nextStep(): void
    {
        $this->ensureInstallerOpen();
        $this->errorMessage = null;

        if ($this->step === 1) {
            $this->refreshRequirements();

            if (! $this->requirementsMet) {
                $failed = Installer::failedRequirementLabels();
                $this->errorMessage = 'Fix these before continuing: '.implode('; ', $failed);

                return;
            }

            if (! Installer::ensureEnvFile()) {
                $this->errorMessage = 'Could not create .env automatically.';

                return;
            }

            Installer::ensureAppKey();
            $this->step = 2;

            return;
        }

        if ($this->step === 2) {
            $this->saveEnvironmentAndContinue();

            return;
        }

        if ($this->step === 3) {
            $this->runMigrationsAndContinue();

            return;
        }

        if ($this->step === 4) {
            $this->createAdminAndFinish();
        }
    }

    public function previousStep(): void
    {
        $this->ensureInstallerOpen();
        if ($this->step > 1 && $this->step < 5) {
            $this->step--;
            $this->errorMessage = null;
            $this->dbTestMessage = null;
        }
    }

    protected function saveEnvironmentAndContinue(): void
    {
        $this->ensureInstallerOpen();

        $this->app_url = $this->normalizeAppUrl($this->app_url);

        $this->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url', 'regex:/\\Ahttps?:\\/\\//i', 'max:2048'],
            'db_connection' => ['required', 'in:pgsql,mysql'],
            'db_host' => ['required', 'string', 'max:253', 'regex:/\\A(?:localhost|[A-Za-z0-9.-]+|\\[[0-9A-Fa-f:]+\\]|[0-9A-Fa-f:]+)\\z/'],
            'db_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:128', 'regex:/\\A[A-Za-z0-9_.-]+\\z/'],
            'db_username' => ['required', 'string', 'max:128'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        $test = Installer::testDatabaseConnection(
            $this->db_connection,
            $this->db_host,
            $this->db_port,
            $this->db_database,
            $this->db_username,
            $this->db_password
        );

        if (! $test['success']) {
            $this->dbTestSuccess = false;
            $this->dbTestMessage = $test['message'];
            $this->errorMessage = $test['message'];

            return;
        }

        try {
            Installer::ensureEnvFile();
            Installer::ensureAppKey();

            Installer::writeEnv([
                'APP_NAME' => $this->app_name,
                'APP_URL' => $this->app_url,
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'LOG_LEVEL' => 'error',
                'DB_CONNECTION' => $this->db_connection,
                'DB_HOST' => $this->db_host,
                'DB_PORT' => $this->db_port,
                'DB_DATABASE' => $this->db_database,
                'DB_USERNAME' => $this->db_username,
                'DB_PASSWORD' => $this->db_password,
                'CACHE_DRIVER' => 'file',
                'CACHE_STORE' => 'file',
                'SESSION_DRIVER' => 'file',
                'SESSION_LIFETIME' => '480',
                'QUEUE_CONNECTION' => 'sync',
                'SCOUT_DRIVER' => 'collection',
                'MAIL_MAILER' => 'log',
                'MAIL_FROM_ADDRESS' => 'noreply@'.(parse_url($this->app_url, PHP_URL_HOST) ?: 'example.com'),
                'MAIL_FROM_NAME' => '"${APP_NAME}"',
            ]);

            try {
                Artisan::call('config:clear');
            } catch (\Throwable) {
            }

            config([
                'cache.default' => 'file',
                'session.driver' => 'file',
            ]);

            config([
                'app.name' => $this->app_name,
                'app.url' => $this->app_url,
                'database.default' => $this->db_connection,
                "database.connections.{$this->db_connection}.host" => $this->db_host,
                "database.connections.{$this->db_connection}.port" => $this->db_port,
                "database.connections.{$this->db_connection}.database" => $this->db_database,
                "database.connections.{$this->db_connection}.username" => $this->db_username,
                "database.connections.{$this->db_connection}.password" => $this->db_password,
            ]);

            $this->dbTestSuccess = true;
            $this->dbTestMessage = 'Connection successful. Environment saved.';
            $this->step = 3;
        } catch (\Throwable $e) {
            Log::error('Livewire installer failed to write the environment file.', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Failed to save the environment configuration. Check the server logs.';
        }
    }

    protected function runMigrationsAndContinue(): void
    {
        $this->ensureInstallerOpen();

        $this->app_url = $this->normalizeAppUrl($this->app_url);

        try {
            config([
                'cache.default' => 'file',
                'session.driver' => 'file',
                'database.default' => $this->db_connection,
                "database.connections.{$this->db_connection}.host" => $this->db_host,
                "database.connections.{$this->db_connection}.port" => $this->db_port,
                "database.connections.{$this->db_connection}.database" => $this->db_database,
                "database.connections.{$this->db_connection}.username" => $this->db_username,
                "database.connections.{$this->db_connection}.password" => $this->db_password,
            ]);

            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            if ($exitCode !== 0) {
                Log::error('Livewire installer migrations failed.', ['output' => $output]);
                $this->migrateSuccess = false;
                $this->migrateOutput = 'Migration failed. Check the server logs.';
                $this->errorMessage = 'Migration failed. Check the server logs.';

                return;
            }

            $this->migrateOutput = $output;
            $this->migrateSuccess = true;

            Installer::writeEnv([
                'APP_NAME' => $this->app_name,
                'APP_URL' => $this->app_url,
                'DB_CONNECTION' => $this->db_connection,
                'DB_HOST' => $this->db_host,
                'DB_PORT' => $this->db_port,
                'DB_DATABASE' => $this->db_database,
                'DB_USERNAME' => $this->db_username,
                'DB_PASSWORD' => $this->db_password,
                'SESSION_DRIVER' => 'database',
                'CACHE_STORE' => 'database',
                'CACHE_DRIVER' => 'database',
            ]);

            $this->step = 4;
        } catch (\Throwable $e) {
            Log::error('Livewire installer migration threw an exception.', ['error' => $e->getMessage()]);
            $this->migrateSuccess = false;
            $this->migrateOutput = 'Migration failed. Check the server logs.';
            $this->errorMessage = 'Migration failed. Check the server logs.';
        }
    }

    protected function createAdminAndFinish(): void
    {
        $this->ensureInstallerOpen();

        $this->app_url = $this->normalizeAppUrl($this->app_url);

        $this->validate([
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        try {
            config([
                'database.default' => $this->db_connection,
                "database.connections.{$this->db_connection}.host" => $this->db_host,
                "database.connections.{$this->db_connection}.port" => $this->db_port,
                "database.connections.{$this->db_connection}.database" => $this->db_database,
                "database.connections.{$this->db_connection}.username" => $this->db_username,
                "database.connections.{$this->db_connection}.password" => $this->db_password,
            ]);

            if (! Schema::hasTable('users')) {
                $this->errorMessage = 'Users table is missing. Go back and re-run migrations.';

                return;
            }

            if (User::query()->where('email', $this->admin_email)->exists()) {
                $this->addError(
                    'admin_email',
                    'That email address already exists. Choose a different email; the installer never changes an existing account.'
                );

                return;
            }

            User::create([
                'name' => $this->admin_name,
                'email' => $this->admin_email,
                'password' => $this->admin_password,
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]);

            Installer::applyRecommendedEnv();
            Installer::writeEnv([
                'APP_NAME' => $this->app_name,
                'APP_URL' => $this->app_url,
                'DB_CONNECTION' => $this->db_connection,
                'DB_HOST' => $this->db_host,
                'DB_PORT' => $this->db_port,
                'DB_DATABASE' => $this->db_database,
                'DB_USERNAME' => $this->db_username,
                'DB_PASSWORD' => $this->db_password,
            ]);

            Installer::lock();
            $this->step = 5;
        } catch (\Throwable $e) {
            Log::error('Livewire installer failed to create the initial administrator.', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Failed to create the administrator account. Check the server logs.';
        }
    }

    protected function ensureInstallerOpen(): void
    {
        if (Installer::isInstalled()) {
            abort(403, 'Application is already installed.');
        }
    }

    public function render()
    {
        return view('livewire.install-wizard');
    }
}
