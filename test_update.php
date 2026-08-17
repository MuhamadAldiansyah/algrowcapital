<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('username', 'admin')->first();
if(!$user) $user = App\Models\User::first();

$validated = [
    'name' => $user->name,
    'username' => $user->username,
    'email' => $user->email,
    'role' => $user->role,
    'status' => $user->status,
    'password' => 'newpassword123'
];

if (!empty($validated['password'])) {
    $validated['password'] = bcrypt($validated['password']);
} else {
    unset($validated['password']);
}

$user->update($validated);
echo "Updated! Check login with 'newpassword123'.\n";
echo "Hash: " . $user->fresh()->password . "\n";
echo "Match: " . (Hash::check('newpassword123', $user->fresh()->password) ? 'YES' : 'NO') . "\n";
