<?php

// Ensure storage directories exist in /tmp
$dirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/storage/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Override Laravel caching paths to use /tmp
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
putenv('APP_SERVICES_CACHE=/tmp/storage/bootstrap/cache/services.php');
$_ENV['APP_SERVICES_CACHE'] = '/tmp/storage/bootstrap/cache/services.php';
putenv('APP_PACKAGES_CACHE=/tmp/storage/bootstrap/cache/packages.php');
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/storage/bootstrap/cache/packages.php';
putenv('APP_CONFIG_CACHE=/tmp/storage/bootstrap/cache/config.php');
$_ENV['APP_CONFIG_CACHE'] = '/tmp/storage/bootstrap/cache/config.php';
putenv('APP_ROUTES_CACHE=/tmp/storage/bootstrap/cache/routes.php');
$_ENV['APP_ROUTES_CACHE'] = '/tmp/storage/bootstrap/cache/routes.php';
putenv('APP_EVENTS_CACHE=/tmp/storage/bootstrap/cache/events.php');
$_ENV['APP_EVENTS_CACHE'] = '/tmp/storage/bootstrap/cache/events.php';

// Force essential serverless environment variables to prevent crashes if Vercel dashboard has empty values
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';
putenv('LOG_CHANNEL=stderr');

$_ENV['CACHE_STORE'] = 'array';
$_SERVER['CACHE_STORE'] = 'array';
putenv('CACHE_STORE=array');

$_ENV['SESSION_DRIVER'] = 'cookie';
$_SERVER['SESSION_DRIVER'] = 'cookie';
putenv('SESSION_DRIVER=cookie');

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// Force Laravel to use the writable /tmp/storage directory
$app->useStoragePath('/tmp/storage');

try {
    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    if ($e instanceof \ArgumentCountError && str_contains($e->getMessage(), 'createDriver')) {
        echo "<h1>Vercel Config Debugger</h1>";
        echo "<h3>Manager crash detected. One of your Vercel Environment Variables is empty.</h3>";
        echo "<b>Trace:</b> " . $e->getTraceAsString() . "<br><br>";
        echo "<b>Current Config Values:</b><pre>";
        print_r([
            'LOG_CHANNEL' => config('logging.default'),
            'CACHE_STORE' => config('cache.default'),
            'SESSION_DRIVER' => config('session.driver'),
            'MAIL_MAILER' => config('mail.default'),
            'DB_CONNECTION' => config('database.default'),
            'QUEUE_CONNECTION' => config('queue.default'),
            'FILESYSTEM_DISK' => config('filesystems.default'),
            'BROADCAST_CONNECTION' => config('broadcasting.default')
        ]);
        echo "</pre>";
        exit;
    }
    throw $e;
}
