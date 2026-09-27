<div class="bg-white shadow-xl rounded-2xl overflow-hidden">
    {{-- Progress --}}
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between text-sm font-medium text-gray-500">
            <span class="{{ $step >= 1 ? 'text-indigo-600' : '' }}">1. Requirements</span>
            <span class="{{ $step >= 2 ? 'text-indigo-600' : '' }}">2. Database</span>
            <span class="{{ $step >= 3 ? 'text-indigo-600' : '' }}">3. Admin Account</span>
            <span class="{{ $step >= 4 ? 'text-indigo-600' : '' }}">4. Done</span>
        </div>
        <div class="mt-3 h-2 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-indigo-600 transition-all duration-300" style="width: {{ ($step / 4) * 100 }}%"></div>
        </div>
    </div>

    <div class="px-6 py-8">
        @if($errorMessage)
            <div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ $errorMessage }}
            </div>
        @endif

        {{-- Step 1: Requirements --}}
        @if($step === 1)
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Server Requirements</h2>
            <ul class="space-y-3">
                @foreach($requirements as $key => $check)
                    <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <span class="text-sm text-gray-700">{{ $check['label'] }}</span>
                        <span class="inline-flex items-center gap-2 text-sm font-medium
                            {{ $check['ok'] ? 'text-green-600' : 'text-red-600' }}">
                            @if($check['ok'])
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            @endif
                            {{ $check['value'] }}
                        </span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8 flex justify-between">
                <button type="button" wire:click="refreshRequirements"
                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Re-check
                </button>
                <button type="button" wire:click="nextStep" @disabled(!$requirementsMet)
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Continue
                </button>
            </div>
        @endif

        {{-- Step 2: Migrations --}}
        @if($step === 2)
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Database Setup</h2>
            <p class="text-sm text-gray-600 mb-6">
                Ensure your <code class="bg-gray-100 px-1 rounded">.env</code> database credentials are correct.
                Click below to run all pending migrations.
            </p>

            @if($migrateOutput)
                <pre class="mb-4 bg-gray-900 text-gray-100 text-xs rounded-xl p-4 overflow-x-auto max-h-48">{{ $migrateOutput }}</pre>
            @endif

            <div class="mt-8 flex justify-between">
                <button type="button" wire:click="previousStep"
                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Back
                </button>
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="nextStep">Run Migrations & Continue</span>
                    <span wire:loading wire:target="nextStep">Running migrations...</span>
                </button>
            </div>
        @endif

        {{-- Step 3: Admin account --}}
        @if($step === 3)
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Create Admin Account</h2>
            <p class="text-sm text-gray-600 mb-6">This account will be used to access the Filament admin panel.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" wire:model="admin_name"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    @error('admin_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" wire:model="admin_email"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    @error('admin_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" wire:model="admin_password"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    @error('admin_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" wire:model="admin_password_confirmation"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <button type="button" wire:click="previousStep"
                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Back
                </button>
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="nextStep">Finish Installation</span>
                    <span wire:loading wire:target="nextStep">Creating account...</span>
                </button>
            </div>
        @endif

        {{-- Step 4: Success --}}
        @if($step === 4)
            <div class="text-center py-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Installation Complete</h2>
                <p class="text-gray-600 mb-6">
                    The installer has been locked. You can no longer access <code class="bg-gray-100 px-1 rounded">/install</code>.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ url('/admin') }}"
                        class="inline-flex justify-center px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">
                        Go to Admin Panel
                    </a>
                    <a href="{{ url('/') }}"
                        class="inline-flex justify-center px-6 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Visit Homepage
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
