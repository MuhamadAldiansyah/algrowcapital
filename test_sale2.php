<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ipo = App\Models\Ipo::find(2);
foreach($ipo->placements as $p) {
    if($p->sale) {
        echo "Placement ID: " . $p->id . " | Total Return: " . $p->sale->total_return . " | Net Profit: " . $p->sale->net_profit . "\n";
        break; // just print one
    }
}
