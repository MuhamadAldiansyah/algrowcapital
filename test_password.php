<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
echo "Before: " . $user->password . "\n";
$user->update(['password' => '12345678']);
echo "After: " . $user->fresh()->password . "\n";
