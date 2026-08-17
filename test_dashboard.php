<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
auth()->loginUsingId(18);
try {
    $controller = new App\Http\Controllers\DashboardController();
    $controller->index();
    echo 'SUCCESS';
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
