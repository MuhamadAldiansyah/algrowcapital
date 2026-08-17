<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ipo = App\Models\Ipo::find(2);
$c = $ipo->placements()->whereHas('allocation', function($q) { $q->where('lot_allocated', '>', 0); })->count();
echo "Allocations with Lots: " . $c . "\n";
echo "Step: " . $ipo->step . "\n";
