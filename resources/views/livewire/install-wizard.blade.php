<div class="bg-white text-gray-900 shadow-xl rounded-2xl overflow-hidden">
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs sm:text-sm font-medium text-gray-500">
            <span class="{{ $step >= 1 ? 'text-indigo-600' : '' }}">1. Requirements</span>
            <span class="{{ $step >= 2 ? 'text-indigo-600' : '' }}">2. Environment</span>
            <span class="{{ $step >= 3 ? 'text-indigo-600' : '' }}">3. Migrate</span>
            <span class="{{ $step >= 4 ? 'text-indigo-600' : '' }}">4. Admin</span>
            <span class="{{ $step >= 5 ? 'text-indigo-600' : '' }}">5. Done</span>
        </div>
        <div class="mt-3 h-2 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-indigo-600 transition-all duration-300" style="width: {{ ($step / 5) * 100 }}%"></div>
        </div>
    </div>

    <div class="px-6 py-8">
        @if($errorMessage)
            <div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm" wire:key="error-{{ md5($errorMessage) }}">
                {{ $errorMessage }}
            </div>
        @endif

        @if($step === 1)
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Server Requirements</h2>
            <ul class="space-y-3">
                @foreach($requirements as $check)
                    <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0 gap-4">
                        <span class="text-sm text-gray-700">{{ $check['label'] }}</span>
                        <span class="text-sm font-medium whitespace-nowrap {{ $check['ok'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $check['ok'] ? '✓' : '✗' }} {{ $check['value'] }}
                        </span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8 flex justify-between gap-3">
                <button type="button" wire:click="refreshRequirements"
                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Re-check
                </button>
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="nextStep">Continue</span>
                    <span wire:loading wire:target="nextStep">Checking...</span>
                </button>
            </div>

            @if(!$requirementsMet)
                <p class="mt-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                    One or more checks failed. On Hostinger SSH run:
                    <code class="block mt-2 text-xs bg-amber-100 p-2 rounded">chmod -R 775 storage bootstrap/cache</code>
                </p>
            @endif
        @endif

        @if($step === 2)
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Environment & Database</h2>
            <p class="text-sm text-gray-600 mb-6">Hostinger usually uses <strong>MySQL</strong>. Use the DB name/user from hPanel → MySQL Databases.</p>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">App Name</label>
                        <input type="text" wire:model="app_name" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                        @error('app_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">App URL</label>
                        <input type="url" wire:model="app_url" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                        @error('app_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Database Driver</label>
                    <select wire:model.live="db_connection" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                        <option value="mysql">MySQL / MariaDB</option>
                        <option value="pgsql">PostgreSQL</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DB Host</label>
                        <input type="text" wire:model="db_host" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border" placeholder="127.0.0.1 or localhost">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DB Port</label>
                        <input type="text" wire:model="db_port" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
                    <input type="text" wire:model="db_database" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                    @error('db_database') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DB Username</label>
                        <input type="text" wire:model="db_username" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DB Password</label>
                        <input type="password" wire:model="db_password" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <button type="button" wire:click="testDatabase" wire:loading.attr="disabled"
                        class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Test Connection
                    </button>
                    @if($dbTestMessage)
                        <span class="text-sm {{ $dbTestSuccess ? 'text-green-600' : 'text-red-600' }}">{{ $dbTestMessage }}</span>
                    @endif
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <button type="button" wire:click="previousStep" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Back</button>
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="nextStep">Save & Continue</span>
                    <span wire:loading wire:target="nextStep">Saving...</span>
                </button>
            </div>
        @endif

        @if($step === 3)
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Run Database Migrations</h2>
            <p class="text-sm text-gray-600 mb-6">Creates all tables using the credentials you saved.</p>
            @if($migrateOutput)
                <pre class="mb-4 bg-gray-900 text-gray-100 text-xs rounded-xl p-4 overflow-x-auto max-h-56 whitespace-pre-wrap">{{ $migrateOutput }}</pre>
            @endif
            <div class="mt-8 flex justify-between">
                <button type="button" wire:click="previousStep" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Back</button>
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="nextStep">Run Migrations & Continue</span>
                    <span wire:loading wire:target="nextStep">Migrating...</span>
                </button>
            </div>
        @endif

        @if($step === 4)
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Create Admin Account</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" wire:model="admin_name" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                    @error('admin_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" wire:model="admin_email" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                    @error('admin_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" wire:model="admin_password" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                    @error('admin_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" wire:model="admin_password_confirmation" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2 border">
                </div>
            </div>
            <div class="mt-8 flex justify-between">
                <button type="button" wire:click="previousStep" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Back</button>
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="nextStep">Finish Installation</span>
                    <span wire:loading wire:target="nextStep">Creating...</span>
                </button>
            </div>
        @endif

        @if($step === 5)
            <div class="text-center py-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Installation Complete</h2>
                <p class="text-gray-600 mb-6">Installer is locked. Log in to the admin panel.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ url('/admin') }}" class="inline-flex justify-center px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">Go to Admin</a>
                    <a href="{{ url('/') }}" class="inline-flex justify-center px-6 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Homepage</a>
                </div>
            </div>
        @endif
    </div>
</div>
