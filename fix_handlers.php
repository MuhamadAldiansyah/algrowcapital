<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\Config::set('database.connections.mysql.database', 'algrowcapital');
\Illuminate\Support\Facades\DB::purge('mysql');

$fundings = \App\Models\InvestorFunding::where('investor_id', 3)->with('placement.ipo')->get();
echo "--- Fundings for Muhamad Aldiansyah (ID 3) ---\n";
foreach($fundings as $f) {
    $ipoCode = $f->placement->ipo ? $f->placement->ipo->code : 'NULL';
    $step = $f->placement->ipo ? $f->placement->ipo->step : 'NULL';
    echo "ID: {$f->id}, Ipo: {$ipoCode} (Step: {$step}), Amount: {$f->amount_funded}\n";
}
