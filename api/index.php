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

// Clean up any empty environment variables that Vercel might have passed
// This prevents Laravel from trying to load drivers with empty string names ("")
foreach ($_ENV as $key => $value) {
    if (is_string($value) && trim($value) === '') {
        unset($_ENV[$key]);
        putenv($key);
    }
}
foreach ($_SERVER as $key => $value) {
    if (is_string($value) && trim($value) === '') {
        unset($_SERVER[$key]);
        putenv($key);
    }
}

$managers = ['LOG_CHANNEL', 'CACHE_STORE', 'CACHE_DRIVER', 'SESSION_DRIVER', 'DB_CONNECTION', 'QUEUE_CONNECTION', 'FILESYSTEM_DISK', 'BROADCAST_CONNECTION', 'MAIL_MAILER'];
foreach ($managers as $m) {
    if (getenv($m) === '') {
        putenv($m);
    }
}

// Force essential serverless environment variables
putenv('LOG_CHANNEL=stderr');
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';

putenv('CACHE_STORE=array');
$_ENV['CACHE_STORE'] = 'array';
$_SERVER['CACHE_STORE'] = 'array';

putenv('SESSION_DRIVER=cookie');
$_ENV['SESSION_DRIVER'] = 'cookie';
$_SERVER['SESSION_DRIVER'] = 'cookie';

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// Force Laravel to use the writable /tmp/storage directory
$app->useStoragePath('/tmp/storage');

$app->handleRequest(Request::capture());
