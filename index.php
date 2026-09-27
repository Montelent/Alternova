<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Auto-create a COMPLETE .env before Laravel boots
|--------------------------------------------------------------------------
*/

$basePath = __DIR__;
$envPath = $basePath . '/.env';

if (! is_file($envPath)) {
    $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $appUrl = $scheme . '://' . $host;
    $key = 'base64:' . base64_encode(random_bytes(32));

    $env = <<<ENV
APP_NAME=Alternova
APP_ENV=production
APP_KEY={$key}
APP_DEBUG=false
APP_URL={$appUrl}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

SCOUT_DRIVER=collection

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="\${APP_NAME}"
ENV;

    file_put_contents($envPath, $env);
} else {
    $envContents = file_get_contents($envPath);
    $changed = false;

    // Rebuild if incomplete (missing DB_CONNECTION)
    if (! preg_match('/^DB_CONNECTION=/m', $envContents)) {
        $key = 'base64:' . base64_encode(random_bytes(32));
        if (preg_match('/^APP_KEY=(.+)$/m', $envContents, $m) && trim($m[1]) !== '') {
            $key = trim($m[1]);
        }
        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $appUrl = $scheme . '://' . $host;

        $env = <<<ENV
APP_NAME=Alternova
APP_ENV=production
APP_KEY={$key}
APP_DEBUG=false
APP_URL={$appUrl}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

SCOUT_DRIVER=collection

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="\${APP_NAME}"
ENV;
        file_put_contents($envPath, $env);
        $envContents = $env;
        $changed = true;
    }

    // Force file drivers if redis slipped in
    foreach (['CACHE_DRIVER' => 'file', 'SESSION_DRIVER' => 'file', 'QUEUE_CONNECTION' => 'sync'] as $k => $v) {
        if (preg_match('/^' . $k . '=(redis|phpredis)\s*$/mi', $envContents)) {
            $envContents = preg_replace('/^' . $k . '=.*$/mi', $k . '=' . $v, $envContents);
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
