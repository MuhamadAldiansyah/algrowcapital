<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('username', 'developer')->first();
if (!$user) {
    echo "No developer user found.\n";
    exit;
}

echo "Status: " . var_export($user->status, true) . "\n";
