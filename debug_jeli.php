<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ipo = \App\Models\Ipo::where('code', 'JELI')->first();
if(!$ipo) { 
    echo 'IPO not found'; 
} else { 
    $totalCapital = $ipo->placements()->sum('capital_allocated');
    $totalInvestorProfit = \App\Models\InvestorTransaction::where('type', 'PROFIT')
                        ->where('description', 'like', "%Profit Saham {$ipo->code}%")
                        ->sum('amount');
    $totalWithdrawn = \App\Models\InvestorTransaction::where('type', 'WITHDRAW')
                        ->where('description', 'like', "%Refund Emiten {$ipo->code}%")
                        ->sum('amount');
    
    $expectedReturn = $ipo->step >= 3 ? ($totalCapital + $totalInvestorProfit) : $totalCapital;
    $unreturned = $expectedReturn - $totalWithdrawn;
    
    echo "Total Capital: $totalCapital \n";
    echo "Total Investor Profit: $totalInvestorProfit \n";
    echo "Total Withdrawn: $totalWithdrawn \n";
    echo "Expected Return: $expectedReturn \n";
    echo "Unreturned: $unreturned \n";
}
