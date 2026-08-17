<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$inv = App\Models\Investor::first();
echo json_encode($inv->transactions->toArray());
