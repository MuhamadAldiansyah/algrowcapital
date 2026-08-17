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

// Forward to the standard Laravel public index.php
require __DIR__ . '/../public/index.php';
