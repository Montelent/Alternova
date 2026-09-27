<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Auto-bootstrap .env + APP_KEY (before Laravel boots)
|--------------------------------------------------------------------------
| Laravel requires APP_KEY to start. On first visit we create .env from
| .env.example and generate a key so /install can load without SSH.
*/

$basePath = __DIR__;
$envPath = $basePath . '/.env';
$examplePath = $basePath . '/.env.example';

if (! is_file($envPath) && is_file($examplePath)) {
    copy($examplePath, $envPath);
}

if (is_file($envPath)) {
    $envContents = file_get_contents($envPath);
    $needsKey = ! preg_match('/^APP_KEY=.+$/m', $envContents)
        || preg_match('/^APP_KEY=\s*$/m', $envContents)
        || preg_match('/^APP_KEY=$/m', $envContents);

    if ($needsKey) {
        $key = 'base64:' . base64_encode(random_bytes(32));

        if (preg_match('/^APP_KEY=.*$/m', $envContents)) {
            $envContents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContents);
        } else {
            $envContents = rtrim($envContents) . "\nAPP_KEY=" . $key . "\n";
        }

        // Best-effort APP_URL from current host
        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $appUrl = $scheme . '://' . $host;

        if (preg_match('/^APP_URL=.*$/m', $envContents)) {
            $envContents = preg_replace('/^APP_URL=.*$/m', 'APP_URL=' . $appUrl, $envContents);
        }

        file_put_contents($envPath, $envContents);
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
