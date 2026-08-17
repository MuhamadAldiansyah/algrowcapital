<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$isOwner = true;
$ipos = \App\Models\Ipo::with(['placements.sale', 'placements.fundings.investor', 'placements.mitraAccount', 'placements.allocation'])->get();
$ipos = $ipos->filter(function($ipo) { return $ipo->step >= 3; });

$portfolioData = [];
$grandTotalModal = 0;
$grandTotalProfitBersih = 0;
$grandTotalFeePlatform = 0;

foreach ($ipos as $ipo) {
    $totalModalEvent = 0;
    $totalProfitKotorEvent = 0;
    $totalProfitMitraEvent = 0;
    $totalProfitBersihInvestorEvent = 0;
    $totalFeePlatformEvent = 0;
    $totalMitra = 0;
    $hasInvolvement = false;

    foreach ($ipo->placements as $p) {
        if ($p->sale && $p->capital_allocated > 0) {
            $hasInvolvement = true;
            $totalMitra++;
            
            foreach ($p->fundings as $f) {
                $ratio = $f->amount_funded / $p->capital_allocated;
                $capital = ($p->allocation ? $p->allocation->total_used * $ratio : 0);
                $gross = $p->sale->net_profit * $ratio;
                
                $isThisOwnerFunding = (strtoupper(trim($f->investor->name)) === 'MUHAMAD ALDIANSYAH' || $f->investor_id == 3);
                
                if ($isThisOwnerFunding) {
                    $netInvestor = $gross * ($f->share_pct / 100);
                    $totalModalEvent += $capital;
                    $totalProfitKotorEvent += $gross;
                    $totalProfitBersihInvestorEvent += $netInvestor;
                    $totalProfitMitraEvent += ($gross * 0.50);
                } else {
                    $mitraPortion = $gross * 0.50;
                    $investorPortion = $gross * ($f->share_pct / 100);
                    $platformFee = $gross - $mitraPortion - $investorPortion;
                    
                    $totalFeePlatformEvent += $platformFee;
                }
            }
        }
    }
    
    if ($hasInvolvement) {
        $portfolioData[] = [
            'ipo' => $ipo->code,
            'total_mitra' => $totalMitra,
            'modal_terpakai' => $totalModalEvent,
            'profit_kotor' => $totalProfitKotorEvent,
            'porsi_mitra' => $totalProfitMitraEvent,
            'profit_bersih' => $totalProfitBersihInvestorEvent,
            'fee_platform' => $totalFeePlatformEvent
        ];
        
        $grandTotalModal += $totalModalEvent;
        $grandTotalProfitBersih += $totalProfitBersihInvestorEvent;
        $grandTotalFeePlatform += $totalFeePlatformEvent;
    }
}
echo json_encode(['portfolio' => $portfolioData, 'grandTotalModal' => $grandTotalModal, 'grandTotalFee' => $grandTotalFeePlatform, 'grandTotalProfit' => $grandTotalProfitBersih], JSON_PRETTY_PRINT);
