<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
$plain = '12345678';
$bcrypted = bcrypt($plain);
$user->update(['password' => $bcrypted]);

echo "Bcrypted: " . $bcrypted . "\n";
echo "DB: " . $user->fresh()->password . "\n";
echo "Check plain against DB: " . (Hash::check($plain, $user->fresh()->password) ? 'YES' : 'NO') . "\n";
