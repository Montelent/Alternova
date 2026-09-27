<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install – Alternova</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 min-h-screen antialiased text-slate-100">
<div class="min-h-screen flex flex-col justify-center py-10 px-4">
    <div class="mx-auto w-full max-w-md mb-6 text-center">
        <h1 class="text-3xl font-bold text-white">Alternova</h1>
        <p class="mt-1 text-sm text-slate-400">First-time installation</p>
    </div>

    <div class="mx-auto w-full max-w-lg bg-white text-gray-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-200 px-5 py-3">
            <div class="flex justify-between text-xs font-medium text-gray-500">
                <span class="{{ $step >= 1 ? 'text-indigo-600' : '' }}">1. Requirements</span>
                <span class="{{ $step >= 2 ? 'text-indigo-600' : '' }}">2. Database</span>
                <span class="{{ $step >= 3 ? 'text-indigo-600' : '' }}">3. Migrate</span>
                <span class="{{ $step >= 4 ? 'text-indigo-600' : '' }}">4. Admin</span>
                <span class="{{ $step >= 5 ? 'text-indigo-600' : '' }}">5. Done</span>
            </div>
            <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600" style="width: {{ ($step / 5) * 100 }}%"></div>
            </div>
        </div>

        <div class="px-5 py-6">
            @if($errorMessage)
                <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                    {{ $errorMessage }}
                </div>
            @endif

            {{-- Step 1 --}}
            @if($step === 1)
                <h2 class="text-xl font-semibold mb-4">Server Requirements</h2>
                <ul class="space-y-2">
                    @foreach($requirements as $check)
                        <li class="flex justify-between text-sm py-2 border-b border-gray-100">
                            <span>{{ $check['label'] }}</span>
                            <span class="font-medium {{ $check['ok'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $check['ok'] ? '✓' : '✗' }} {{ $check['value'] }}
                            </span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 flex justify-between gap-3">
                    <form method="POST" action="{{ route('install.recheck') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 border border-gray-300 rounded-xl text-sm">Re-check</button>
                    </form>
                    <form method="POST" action="{{ route('install.next') }}">
                        @csrf
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium">Continue</button>
                    </form>
                </div>
            @endif

            {{-- Step 2 --}}
            @if($step === 2)
                <h2 class="text-xl font-semibold mb-2">Database</h2>
                <p class="text-sm text-gray-600 mb-4">Use MySQL credentials from hPanel → Databases.</p>
                <form method="POST" action="{{ route('install.next') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">App Name</label>
                        <input name="app_name" value="{{ old('app_name', $old['app_name'] ?? 'Alternova') }}" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">App URL</label>
                        <input name="app_url" value="{{ old('app_url', $old['app_url'] ?? '') }}" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Driver</label>
                        <select name="db_connection" class="w-full border rounded-xl px-3 py-2 text-sm">
                            <option value="mysql" @selected(($old['db_connection'] ?? 'mysql') === 'mysql')>MySQL</option>
                            <option value="pgsql" @selected(($old['db_connection'] ?? '') === 'pgsql')>PostgreSQL</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Host</label>
                            <input name="db_host" value="{{ old('db_host', $old['db_host'] ?? '127.0.0.1') }}" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Port</label>
                            <input name="db_port" value="{{ old('db_port', $old['db_port'] ?? '3306') }}" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Database Name</label>
                        <input name="db_database" value="{{ old('db_database', $old['db_database'] ?? '') }}" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Username</label>
                            <input name="db_username" value="{{ old('db_username', $old['db_username'] ?? '') }}" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Password</label>
                            <input type="password" name="db_password" value="{{ old('db_password', $old['db_password'] ?? '') }}" class="w-full border rounded-xl px-3 py-2 text-sm">
                        </div>
                    </div>
                    @if($dbTestMessage)
                        <p class="text-sm {{ $dbTestSuccess ? 'text-green-600' : 'text-red-600' }}">{{ $dbTestMessage }}</p>
                    @endif
                    <div class="flex justify-between pt-2">
                        <button type="submit" name="action" value="test" class="px-4 py-2 border rounded-xl text-sm">Test Connection</button>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium">Save & Continue</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('install.back') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500">← Back</button>
                </form>
            @endif

            {{-- Step 3 --}}
            @if($step === 3)
                <h2 class="text-xl font-semibold mb-2">Run Migrations</h2>
                <p class="text-sm text-gray-600 mb-4">Creates all database tables.</p>
                @if($migrateOutput)
                    <pre class="mb-4 bg-gray-900 text-gray-100 text-xs rounded-xl p-4 overflow-x-auto max-h-48">{{ $migrateOutput }}</pre>
                @endif
                <div class="flex justify-between">
                    <form method="POST" action="{{ route('install.back') }}">@csrf
                        <button type="submit" class="px-4 py-2 border rounded-xl text-sm">Back</button>
                    </form>
                    <form method="POST" action="{{ route('install.next') }}">@csrf
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium">Run Migrations & Continue</button>
                    </form>
                </div>
            @endif

            {{-- Step 4 --}}
            @if($step === 4)
                <h2 class="text-xl font-semibold mb-4">Create Admin Account</h2>
                <form method="POST" action="{{ route('install.next') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input name="admin_name" value="{{ old('admin_name') }}" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                        @error('admin_name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                        @error('admin_email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Password</label>
                        <input type="password" name="admin_password" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                        @error('admin_password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Confirm Password</label>
                        <input type="password" name="admin_password_confirmation" class="w-full border rounded-xl px-3 py-2 text-sm" required>
                    </div>
                    <div class="flex justify-between pt-2">
                        <a href="#" onclick="event.preventDefault(); document.getElementById('back-form').submit();" class="px-4 py-2 border rounded-xl text-sm">Back</a>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium">Finish Installation</button>
                    </div>
                </form>
                <form id="back-form" method="POST" action="{{ route('install.back') }}" class="hidden">@csrf</form>
            @endif

            {{-- Step 5 --}}
            @if($step === 5)
                <div class="text-center py-4">
                    <div class="mx-auto h-14 w-14 rounded-full bg-green-100 flex items-center justify-center mb-3">
                        <span class="text-green-600 text-2xl">✓</span>
                    </div>
                    <h2 class="text-xl font-bold mb-2">Installation Complete</h2>
                    <p class="text-gray-600 text-sm mb-6">Installer is locked. You can log in to admin.</p>
                    <a href="{{ url('/admin') }}" class="inline-block px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium">Go to Admin</a>
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
