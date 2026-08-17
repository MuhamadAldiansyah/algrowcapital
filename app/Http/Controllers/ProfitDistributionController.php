<?php

namespace App\Http\Controllers;

use App\Models\Ipo;
use App\Models\Investor;
use App\Models\InvestorTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitDistributionController extends Controller
{
    public function index()
    {
        $allIpos = Ipo::with(['placements.sale', 'placements.fundings.investor'])
                   ->orderBy('created_at', 'desc')
                   ->get();

        // Step 4: Menunggu Distribusi Profit (Sudah dijual tapi profit_distributed_at null)
        $pendingIpos = $allIpos->filter(function($ipo) {
            return $ipo->step == 4;
        });

        // Step 5: Selesai (Sudah dibagikan)
        $completedIpos = $allIpos->filter(function($ipo) {
            return $ipo->step == 5;
        });

        $pendingDistributions = [];
        $completedDistributions = [];
        
        $grandTotalProfit = 0;
        $grandMitraProfit = 0;
        $grandInvestorProfit = 0;

        // Proses IPO Menunggu Distribusi
        foreach ($pendingIpos as $ipo) {
            $ipoTotalProfit = 0;
            foreach ($ipo->placements as $placement) {
                if ($placement->sale && $placement->sale->net_profit > 0) {
                    $ipoTotalProfit += $placement->sale->net_profit;
                }
            }
            if ($ipoTotalProfit > 0) {
                $pendingDistributions[] = [
                    'ipo' => $ipo,
                    'total_profit' => $ipoTotalProfit,
                ];
            }
        }

        // Proses IPO Sudah Selesai (Riwayat)
        foreach ($completedIpos as $ipo) {
            $ipoTotalProfit = 0;
            foreach ($ipo->placements as $placement) {
                if ($placement->sale && $placement->sale->net_profit > 0) {
                    $ipoTotalProfit += $placement->sale->net_profit;
                }
            }

            if ($ipoTotalProfit > 0) {
                $mitraPct = $ipo->mitra_fee_pct ?? 0;
                $investorPct = $ipo->platform_fee_pct ?? 0;

                $mitraProfit = $ipoTotalProfit * ($mitraPct / 100);
                $investorProfit = $ipoTotalProfit * ($investorPct / 100);

                $completedDistributions[] = [
                    'ipo' => $ipo,
                    'total_profit' => $ipoTotalProfit,
                    'mitra_profit' => $mitraProfit,
                    'investor_profit' => $investorProfit
                ];

                $grandTotalProfit += $ipoTotalProfit;
                $grandMitraProfit += $mitraProfit;
                $grandInvestorProfit += $investorProfit;
            }
        }

        return view('profit-distribution.index', compact(
            'pendingDistributions',
            'completedDistributions',
            'grandTotalProfit',
            'grandMitraProfit',
            'grandInvestorProfit'
        ));
    }

    public function distribute(Request $request, Ipo $ipo)
    {
        $request->validate([
            'mitra_fee_pct' => 'required|numeric|min:0|max:100',
            'investor_fee_pct' => 'required|numeric|min:0|max:100',
        ]);

        $mitraPct = $request->mitra_fee_pct;
        $investorPct = $request->investor_fee_pct;

        if ($mitraPct + $investorPct > 100) {
            return back()->with('error', 'Total persentase Mitra dan Investor tidak boleh lebih dari 100%.');
        }

        if ($ipo->step == 5 || !is_null($ipo->profit_distributed_at)) {
            return back()->with('error', 'Profit IPO ini sudah dibagikan sebelumnya.');
        }

        return DB::transaction(function() use ($ipo, $mitraPct, $investorPct) {
            $ipoTotalProfit = 0;
            $investorCapitalMap = [];
            $totalInvestorCapital = 0;

            foreach ($ipo->placements as $placement) {
                if ($placement->sale && $placement->sale->net_profit > 0) {
                    $ipoTotalProfit += $placement->sale->net_profit;
                }
                
                // Kumpulkan total modal per investor untuk IPO ini
                foreach ($placement->fundings as $funding) {
                    $invId = $funding->investor_id;
                    if (!isset($investorCapitalMap[$invId])) {
                        $investorCapitalMap[$invId] = 0;
                    }
                    $investorCapitalMap[$invId] += $funding->amount_funded;
                    $totalInvestorCapital += $funding->amount_funded;
                }
            }

            if ($ipoTotalProfit <= 0) {
                return back()->with('error', 'IPO ini tidak memiliki profit kotor yang bisa dibagikan.');
            }

            $totalInvestorPool = $ipoTotalProfit * ($investorPct / 100);

            // 2. Bagikan Porsi Investor Luar secara proporsional berdasarkan modal
            if ($totalInvestorPool > 0 && $totalInvestorCapital > 0) {
                foreach ($investorCapitalMap as $investorId => $capital) {
                    // Cek apakah investor ini BUKAN owner
                    $investor = Investor::find($investorId);
                    $ownerUserId = auth()->user()->tenant ? auth()->user()->tenant->owner_id : auth()->user()->id;
                    
                    if ($investor && $investor->user_id != $ownerUserId) {
                        $ratio = $capital / $totalInvestorCapital;
                        $profitForThisInvestor = $totalInvestorPool * $ratio;

                        if ($profitForThisInvestor > 0) {
                            InvestorTransaction::create([
                                'investor_id' => $investorId,
                                'amount' => $profitForThisInvestor,
                                'type' => 'PROFIT',
                                'description' => "Profit Saham {$ipo->code} - Bagi Hasil ({$investorPct}% pool)"
                            ]);
                        }
                    } else if ($investor && $investor->user_id == $ownerUserId) {
                        // Jika Owner ternyata ikut menanam modal juga secara reguler, 
                        // maka owner berhak mendapat porsi dari pool investor sesuai porsi modalnya.
                        $ratio = $capital / $totalInvestorCapital;
                        $profitForThisInvestor = $totalInvestorPool * $ratio;

                        if ($profitForThisInvestor > 0) {
                            InvestorTransaction::create([
                                'investor_id' => $investorId,
                                'amount' => $profitForThisInvestor,
                                'type' => 'PROFIT',
                                'description' => "Profit Saham {$ipo->code} (Sebagai Investor) - Bagi Hasil ({$investorPct}% pool)"
                            ]);
                        }
                    }
                }
            }

            // Update IPO
            $ipo->update([
                'profit_distributed_at' => now(),
                'mitra_fee_pct' => $mitraPct,
                'platform_fee_pct' => $investorPct
            ]);

            return back()->with('success', 'Profit berhasil didistribusikan ke dompet seluruh pihak yang terkait!');
        });
    }
}
