<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ipo = App\Models\Ipo::find(2);
$hasSale = 0;
foreach($ipo->placements as $p) {
    if($p->sale) $hasSale++;
}
echo "Total Placements: " . $ipo->placements->count() . "\n";
echo "Placements with Sale: " . $hasSale . "\n";
