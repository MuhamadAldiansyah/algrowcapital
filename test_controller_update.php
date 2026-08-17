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

$request = Illuminate\Http\Request::create('/users/' . $user->id, 'PUT', [
    'name' => $user->name,
    'username' => $user->username,
    'email' => $user->email,
    'role' => $user->role,
    'status' => 'active',
    'password' => 'dev12345'
]);

$controller = new App\Http\Controllers\UserController();
try {
    $response = $controller->update($request, $user);
    echo "Success! Redirected to: " . $response->getTargetUrl() . "\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation failed:\n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
