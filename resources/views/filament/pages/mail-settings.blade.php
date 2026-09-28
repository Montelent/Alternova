<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit">
                Save email settings
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="sendTest" icon="heroicon-o-paper-airplane">
                Save &amp; send test
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">Quick setup — Resend</x-slot>
        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <li>Create an account at <a href="https://resend.com" target="_blank" class="underline">resend.com</a>.</li>
            <li>Add and verify your domain (DNS records).</li>
            <li>Create an API key and paste it above.</li>
            <li>Set <strong>Mail driver</strong> to Resend, set From address on that domain, save, then send a test.</li>
            <li>On the server run: <code class="text-xs">composer require resend/resend-laravel</code> if the package is not installed yet.</li>
        </ol>
    </x-filament::section>

    <x-filament::section class="mt-4">
        <x-slot name="heading">Quick setup — SMTP (e.g. Hostinger)</x-slot>
        <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-300">
            <li>Host often <code class="text-xs">smtp.hostinger.com</code>, port <code class="text-xs">587</code>, encryption TLS</li>
            <li>Username = full mailbox email, password = mailbox password</li>
            <li>From address should match the mailbox or an allowed alias</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
