<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\Installer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.install')]
class InstallWizard extends Component
{
    public int $step = 1;

    public array $requirements = [];

    public bool $requirementsMet = false;

    // Hostinger / most shared hosts use MySQL
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
        if (Installer::isInstalled()) {
            abort(403, 'Application is already installed.');
        }

        $this->app_url = rtrim(request()->getSchemeAndHttpHost(), '/');
        Installer::ensureStorageDirectories();
        Installer::ensureEnvFile();
        Installer::ensureAppKey();
        $this->refreshRequirements();
    }

    public function refreshRequirements(): void
    {
        Installer::ensureStorageDirectories();
        $this->requirements = Installer::requirements();
        $this->requirementsMet = Installer::allRequirementsMet();
    }

    public function updatedDbConnection(string $value): void
    {
        $this->db_port = $value === 'mysql' ? '3306' : '5432';
    }

    public function testDatabase(): void
    {
        $this->validate([
            'db_connection' => ['required', 'in:pgsql,mysql'],
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'string'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
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
        $this->errorMessage = null;

        if ($this->step === 1) {
            $this->refreshRequirements();

            if (! $this->requirementsMet) {
                $failed = Installer::failedRequirementLabels();
                $this->errorMessage = 'Fix these before continuing: ' . implode('; ', $failed);

                return;
            }

            if (! Installer::ensureEnvFile()) {
                $this->errorMessage = '.env.example is missing. Cannot create .env automatically.';

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
        if ($this->step > 1 && $this->step < 5) {
            $this->step--;
            $this->errorMessage = null;
            $this->dbTestMessage = null;
        }
    }

    protected function saveEnvironmentAndContinue(): void
    {
        $this->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url'],
            'db_connection' => ['required', 'in:pgsql,mysql'],
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'string'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
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
            $this->errorMessage = 'Database connection failed: ' . $test['message'];

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
                'DB_CONNECTION' => $this->db_connection,
                'DB_HOST' => $this->db_host,
                'DB_PORT' => $this->db_port,
                'DB_DATABASE' => $this->db_database,
                'DB_USERNAME' => $this->db_username,
                'DB_PASSWORD' => $this->db_password,
                'CACHE_DRIVER' => 'file',
                'SESSION_DRIVER' => 'file',
                'QUEUE_CONNECTION' => 'sync',
                'SCOUT_DRIVER' => 'collection',
            ]);

            Artisan::call('config:clear');

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
            $this->errorMessage = 'Failed to write .env: ' . $e->getMessage();
        }
    }

    protected function runMigrationsAndContinue(): void
    {
        try {
            config([
                'database.default' => $this->db_connection,
                "database.connections.{$this->db_connection}.host" => $this->db_host,
                "database.connections.{$this->db_connection}.port" => $this->db_port,
                "database.connections.{$this->db_connection}.database" => $this->db_database,
                "database.connections.{$this->db_connection}.username" => $this->db_username,
                "database.connections.{$this->db_connection}.password" => $this->db_password,
            ]);

            $result = Installer::runArtisan('migrate', ['--force' => true]);
            $this->migrateOutput = $result['output'] ?: '(no output)';
            $this->migrateSuccess = $result['success'];

            if (! $result['success']) {
                $this->errorMessage = 'Migration failed. Check the output below.';

                return;
            }

            $this->step = 4;
        } catch (\Throwable $e) {
            $this->migrateSuccess = false;
            $this->migrateOutput = $e->getMessage();
            $this->errorMessage = 'Migration error: ' . $e->getMessage();
        }
    }

    protected function createAdminAndFinish(): void
    {
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

            $existing = User::query()->where('email', $this->admin_email)->first();

            if ($existing) {
                $existing->update([
                    'name' => $this->admin_name,
                    'password' => Hash::make($this->admin_password),
                ]);
            } else {
                User::create([
                    'name' => $this->admin_name,
                    'email' => $this->admin_email,
                    'password' => Hash::make($this->admin_password),
                    'email_verified_at' => now(),
                ]);
            }

            Installer::lock();
            $this->step = 5;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Failed to create admin user: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.install-wizard');
    }
}
