<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$investors = \App\Models\Investor::all();
$totalBalance = 0;
foreach ($investors as $investor) {
    echo "Investor: {$investor->name} | Wallet Balance: {$investor->available_balance}\n";
    $totalBalance += $investor->available_balance;
}
echo "Total Wallet Balance across all investors: {$totalBalance}\n";
