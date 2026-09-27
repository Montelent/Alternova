<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Auto-bootstrap .env + APP_KEY (before Laravel boots)
|--------------------------------------------------------------------------
*/

$basePath = __DIR__;
$envPath = $basePath . '/.env';
$examplePath = $basePath . '/.env.example';

if (! is_file($envPath) && is_file($examplePath)) {
    copy($examplePath, $envPath);
}

if (is_file($envPath)) {
    $envContents = file_get_contents($envPath);
    $changed = false;

    // APP_KEY
    $needsKey = ! preg_match('/^APP_KEY=.+$/m', $envContents)
        || preg_match('/^APP_KEY=\s*$/m', $envContents);

    if ($needsKey) {
        $key = 'base64:' . base64_encode(random_bytes(32));
        if (preg_match('/^APP_KEY=.*$/m', $envContents)) {
            $envContents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContents);
        } else {
            $envContents = rtrim($envContents) . "\nAPP_KEY=" . $key . "\n";
        }
        $changed = true;
    }

    // APP_URL from current host
    $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $appUrl = $scheme . '://' . $host;
    if (preg_match('/^APP_URL=.*$/m', $envContents)) {
        $envContents = preg_replace('/^APP_URL=.*$/m', 'APP_URL=' . $appUrl, $envContents);
        $changed = true;
    }

    // Shared hosting defaults: never require Redis/Meilisearch to boot
    $safeDefaults = [
        'CACHE_DRIVER' => 'file',
        'SESSION_DRIVER' => 'file',
        'QUEUE_CONNECTION' => 'sync',
        'SCOUT_DRIVER' => 'collection',
        'MAIL_MAILER' => 'log',
    ];

    foreach ($safeDefaults as $k => $v) {
        // Force file/sync if currently set to redis (connection would fail)
        if (preg_match('/^' . $k . '=(redis|phpredis)\s*$/mi', $envContents)) {
            $envContents = preg_replace('/^' . $k . '=.*$/mi', $k . '=' . $v, $envContents);
            $changed = true;
        } elseif (! preg_match('/^' . $k . '=/m', $envContents)) {
            $envContents = rtrim($envContents) . "\n{$k}={$v}\n";
            $changed = true;
        }
    }

    if ($changed) {
        file_put_contents($envPath, $envContents);
    }
}

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
