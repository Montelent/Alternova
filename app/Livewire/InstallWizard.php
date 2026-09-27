<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\Installer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.install')]
class InstallWizard extends Component
{
    public int $step = 1;

    public array $requirements = [];

    public bool $requirementsMet = false;

    // Step 3 – admin account
    public string $admin_name = '';

    public string $admin_email = '';

    public string $admin_password = '';

    public string $admin_password_confirmation = '';

    // Feedback
    public ?string $migrateOutput = null;

    public bool $migrateSuccess = false;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        if (Installer::isInstalled()) {
            abort(403, 'Application is already installed.');
        }

        $this->refreshRequirements();
    }

    public function refreshRequirements(): void
    {
        $this->requirements = Installer::requirements();
        $this->requirementsMet = Installer::allRequirementsMet();
    }

    public function nextStep(): void
    {
        $this->errorMessage = null;

        if ($this->step === 1) {
            $this->refreshRequirements();
            if (! $this->requirementsMet) {
                $this->errorMessage = 'Please fix the failed requirements before continuing.';
                return;
            }
            $this->step = 2;
            return;
        }

        if ($this->step === 2) {
            // Run migrations
            try {
                $result = Installer::runArtisan('migrate', ['--force' => true]);
                $this->migrateOutput = $result['output'];
                $this->migrateSuccess = $result['success'];

                if (! $result['success']) {
                    $this->errorMessage = 'Migration failed. Check the output below.';
                    return;
                }

                $this->step = 3;
            } catch (\Throwable $e) {
                $this->errorMessage = 'Migration error: ' . $e->getMessage();
                $this->migrateOutput = $e->getMessage();
                $this->migrateSuccess = false;
            }
            return;
        }

        if ($this->step === 3) {
            $this->createAdminAndFinish();
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
            $this->errorMessage = null;
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
            // Ensure users table exists (migrations should have run)
            if (! Schema::hasTable('users')) {
                $this->errorMessage = 'Users table is missing. Please go back and re-run migrations.';
                return;
            }

            $existing = User::where('email', $this->admin_email)->first();

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

            // Optional: mark as admin if you later add a role column / Spatie Permission
            // For now the first user is the super admin by convention.

            Installer::lock();

            $this->step = 4; // success
        } catch (\Throwable $e) {
            $this->errorMessage = 'Failed to create admin user: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.install-wizard');
    }
}
