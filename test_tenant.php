<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\Illuminate\Support\Facades\DB::select('SHOW TABLES') as $t) {
    $table = array_values((array)$t)[0];
    echo $table . ': ' . (\Illuminate\Support\Facades\Schema::hasColumn($table, 'tenant_id') ? 'YES' : 'NO') . "\n";
}
