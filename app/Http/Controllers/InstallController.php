<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Installer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class InstallController extends Controller
{
    public function index(Request $request)
    {
        if (Installer::isInstalled()) {
            abort(403, 'Application is already installed.');
        }

        Installer::ensureStorageDirectories();
        Installer::ensureEnvFile();
        Installer::ensureAppKey();

        $step = (int) $request->session()->get('install_step', 1);

        return view('install.wizard', [
            'step' => $step,
            'requirements' => Installer::requirements(),
            'requirementsMet' => Installer::allRequirementsMet(),
            'errorMessage' => $request->session()->get('install_error'),
            'dbTestMessage' => $request->session()->get('db_test_message'),
            'dbTestSuccess' => $request->session()->get('db_test_success'),
            'migrateOutput' => $request->session()->get('migrate_output'),
            'old' => $request->session()->get('install_input', [
                'app_name' => 'Alternova',
                'app_url' => rtrim($request->getSchemeAndHttpHost(), '/'),
                'db_connection' => 'mysql',
                'db_host' => '127.0.0.1',
                'db_port' => '3306',
                'db_database' => '',
                'db_username' => '',
                'db_password' => '',
                'admin_name' => '',
                'admin_email' => '',
            ]),
        ]);
    }

    public function next(Request $request)
    {
        if (Installer::isInstalled()) {
            abort(403, 'Application is already installed.');
        }

        $step = (int) $request->session()->get('install_step', 1);
        $request->session()->forget('install_error');

        if ($step === 1) {
            return $this->handleStep1($request);
        }
        if ($step === 2) {
            return $this->handleStep2($request);
        }
        if ($step === 3) {
            return $this->handleStep3($request);
        }
        if ($step === 4) {
            return $this->handleStep4($request);
        }

        return redirect()->route('install.index');
    }

    public function back(Request $request)
    {
        $step = (int) $request->session()->get('install_step', 1);
        if ($step > 1 && $step < 5) {
            $request->session()->put('install_step', $step - 1);
        }
        $request->session()->forget(['install_error', 'db_test_message', 'migrate_output']);

        return redirect()->route('install.index');
    }

    public function recheck(Request $request)
    {
        Installer::ensureStorageDirectories();

        return redirect()->route('install.index');
    }

    protected function handleStep1(Request $request)
    {
        Installer::ensureStorageDirectories();

        if (! Installer::allRequirementsMet()) {
            $failed = Installer::failedRequirementLabels();
            $request->session()->flash('install_error', 'Fix these before continuing: ' . implode('; ', $failed));

            return redirect()->route('install.index');
        }

        Installer::ensureEnvFile();
        Installer::ensureAppKey();
        $request->session()->put('install_step', 2);

        return redirect()->route('install.index');
    }

    protected function handleStep2(Request $request)
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url'],
            'db_connection' => ['required', 'in:pgsql,mysql'],
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'string'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
            'action' => ['nullable', 'string'],
        ]);

        $request->session()->put('install_input', array_merge(
            $request->session()->get('install_input', []),
            $data
        ));

        $test = Installer::testDatabaseConnection(
            $data['db_connection'],
            $data['db_host'],
            $data['db_port'],
            $data['db_database'],
            $data['db_username'],
            $data['db_password'] ?? ''
        );

        if (($data['action'] ?? '') === 'test') {
            $request->session()->flash('db_test_success', $test['success']);
            $request->session()->flash('db_test_message', $test['message']);

            return redirect()->route('install.index');
        }

        if (! $test['success']) {
            $request->session()->flash('install_error', 'Database connection failed: ' . $test['message']);
            $request->session()->flash('db_test_success', false);
            $request->session()->flash('db_test_message', $test['message']);

            return redirect()->route('install.index');
        }

        try {
            Installer::writeEnv([
                'APP_NAME' => $data['app_name'],
                'APP_URL' => $data['app_url'],
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'DB_CONNECTION' => $data['db_connection'],
                'DB_HOST' => $data['db_host'],
                'DB_PORT' => $data['db_port'],
                'DB_DATABASE' => $data['db_database'],
                'DB_USERNAME' => $data['db_username'],
                'DB_PASSWORD' => $data['db_password'] ?? '',
                'CACHE_DRIVER' => 'file',
                'SESSION_DRIVER' => 'file',
                'QUEUE_CONNECTION' => 'sync',
                'SCOUT_DRIVER' => 'collection',
            ]);

            Artisan::call('config:clear');

            $request->session()->put('install_step', 3);
            $request->session()->flash('db_test_success', true);
            $request->session()->flash('db_test_message', 'Connection successful. Environment saved.');
        } catch (\Throwable $e) {
            $request->session()->flash('install_error', 'Failed to write .env: ' . $e->getMessage());
        }

        return redirect()->route('install.index');
    }

    protected function handleStep3(Request $request)
    {
        $input = $request->session()->get('install_input', []);

        try {
            $conn = $input['db_connection'] ?? 'mysql';
            config([
                'database.default' => $conn,
                "database.connections.{$conn}.host" => $input['db_host'] ?? '127.0.0.1',
                "database.connections.{$conn}.port" => $input['db_port'] ?? '3306',
                "database.connections.{$conn}.database" => $input['db_database'] ?? '',
                "database.connections.{$conn}.username" => $input['db_username'] ?? '',
                "database.connections.{$conn}.password" => $input['db_password'] ?? '',
            ]);

            $result = Installer::runArtisan('migrate', ['--force' => true]);
            $request->session()->flash('migrate_output', $result['output'] ?: '(no output)');

            if (! $result['success']) {
                $request->session()->flash('install_error', 'Migration failed. Check the output below.');

                return redirect()->route('install.index');
            }

            $request->session()->put('install_step', 4);
        } catch (\Throwable $e) {
            $request->session()->flash('install_error', 'Migration error: ' . $e->getMessage());
            $request->session()->flash('migrate_output', $e->getMessage());
        }

        return redirect()->route('install.index');
    }

    protected function handleStep4(Request $request)
    {
        $data = $request->validate([
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $input = $request->session()->get('install_input', []);

        try {
            $conn = $input['db_connection'] ?? config('database.default');
            config([
                'database.default' => $conn,
                "database.connections.{$conn}.host" => $input['db_host'] ?? config("database.connections.{$conn}.host"),
                "database.connections.{$conn}.port" => $input['db_port'] ?? config("database.connections.{$conn}.port"),
                "database.connections.{$conn}.database" => $input['db_database'] ?? config("database.connections.{$conn}.database"),
                "database.connections.{$conn}.username" => $input['db_username'] ?? config("database.connections.{$conn}.username"),
                "database.connections.{$conn}.password" => $input['db_password'] ?? config("database.connections.{$conn}.password"),
            ]);

            if (! Schema::hasTable('users')) {
                $request->session()->flash('install_error', 'Users table missing. Go back and run migrations.');

                return redirect()->route('install.index');
            }

            $existing = User::query()->where('email', $data['admin_email'])->first();
            if ($existing) {
                $existing->update([
                    'name' => $data['admin_name'],
                    'password' => Hash::make($data['admin_password']),
                ]);
            } else {
                User::create([
                    'name' => $data['admin_name'],
                    'email' => $data['admin_email'],
                    'password' => Hash::make($data['admin_password']),
                    'email_verified_at' => now(),
                ]);
            }

            Installer::lock();
            $request->session()->put('install_step', 5);
            $request->session()->forget(['install_error', 'install_input']);
        } catch (\Throwable $e) {
            $request->session()->flash('install_error', 'Failed to create admin: ' . $e->getMessage());
        }

        return redirect()->route('install.index');
    }
}
