<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | file is the most reliable default on shared hosting (Hostinger).
    | Switch to database after confirming the sessions table exists:
    | SESSION_DRIVER=database
    |
    | file can 419 under heavy concurrent Livewire requests if storage is not writable.
    |
    */

    'driver' => env('SESSION_DRIVER', 'file'),

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
    | Leave SESSION_SECURE_COOKIE unset in .env unless you are sure.
    */
    'secure' => env('SESSION_SECURE_COOKIE'),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED', false),

];
