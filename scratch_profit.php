<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$profit = \App\Models\InvestorTransaction::where('type', 'PROFIT')->where('description', 'like', '%Profit Saham WBSA%')->sum('amount');
echo "Total Profit for WBSA: " . $profit . "\n";
