<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | file driver can 419 on Livewire under concurrent mobile requests (file lock).
    | Prefer database on shared hosting (Hostinger). Run: php artisan session:table && migrate
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => (int) env('SESSION_LIFETIME', 480),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', false),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    'path' => env('SESSION_PATH', '/'),

    /*
    | Null domain = current host only (correct for alternova.montelent.com).
    | Do not set to .montelent.com unless every subdomain should share login.
    */
    'domain' => env('SESSION_DOMAIN'),

    /*
    | null = Secure flag only when the request is HTTPS (works with TrustProxies).
    | true on a host that does not detect HTTPS → cookies never stick → 419.
    */
    'secure' => env('SESSION_SECURE_COOKIE'),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED', false),

];
