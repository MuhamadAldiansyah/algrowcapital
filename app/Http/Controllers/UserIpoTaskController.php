<?php

namespace App\Http\Controllers;

use App\Models\Ipo;
use App\Models\MitraAccount;
use App\Models\IpoAccountPlacement;
use App\Models\IpoAllocation;
use App\Models\IpoSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserIpoTaskController extends Controller
{
    private function checkMitraAccess()
    {
        $user = Auth::user();
        if ($user && $user->role !== 'user') {
            abort(403, 'Akses ditolak. Halaman Eksekusi Tugas hanya untuk Mitra (Joki).');
        }
    }
    private function getMyAccountIds()
    {
        $user = Auth::user();
        return MitraAccount::where('username', $user->username)
                ->orWhere('owner_name', $user->name)
                ->orWhere('handler_name', $user->name)
                ->pluck('id')->toArray();
    }

    public function index()
    {
        $this->checkMitraAccess();
        $myAccountIds = $this->getMyAccountIds();
        
        $ipos = Ipo::whereHas('placements', function ($q) use ($myAccountIds) {
            $q->whereIn('mitra_account_id', $myAccountIds);
        })->get();

        $tasks = [];
        foreach ($ipos as $ipo) {
            if ($ipo->step === 4) continue; 
            
            $myPlacements = $ipo->placements()->whereIn('mitra_account_id', $myAccountIds)->with('allocation', 'sale', 'mitraAccount')->get();
            
            $needsAllotmentCount = 0;
            $needsSaleCount = 0;

            foreach ($myPlacements as $p) {
                if ($p->capital_allocated <= 0) continue; 

                if (!$p->allocation) {
                    $needsAllotmentCount++;
                } else if (!$p->sale && $p->allocation->lot_allocated > 0) {
                    $needsSaleCount++;
                }
            }

            if ($needsAllotmentCount > 0) {
                $tasks[] = [
                    'ipo' => $ipo,
                    'type' => 'allotment',
                    'count' => $needsAllotmentCount,
                    'label' => 'Tunggu Penjatahan Lot',
                    'color' => 'warning'
                ];
            } else if ($needsSaleCount > 0) {
                $isListing = now()->startOfDay() >= \Carbon\Carbon::parse($ipo->ipo_date)->startOfDay();
                $tasks[] = [
                    'ipo' => $ipo,
                    'type' => 'sale',
                    'count' => $needsSaleCount,
                    'label' => $isListing ? 'Tunggu Realisasi Jual' : 'Menunggu Listing (' . \Carbon\Carbon::parse($ipo->ipo_date)->format('d M') . ')',
                    'color' => $isListing ? 'success' : 'secondary',
                    'disabled' => !$isListing
                ];
            }
        }

        return view('user-tasks.index', compact('tasks'));
    }

    public function editAllotment(Ipo $ipo)
    {
        $this->checkMitraAccess();
        if ($ipo->step === 4) abort(404);
        $myAccountIds = $this->getMyAccountIds();
        $placements = $ipo->placements()
            ->whereIn('mitra_account_id', $myAccountIds)
            ->where('capital_allocated', '>', 0)
            ->doesntHave('allocation')
            ->with('mitraAccount')
            ->get();

        if ($placements->isEmpty()) {
            return redirect()->route('user-tasks.index')->with('success', 'Semua akun Anda di emiten ini sudah mendapatkan penjatahan.');
        }

        return view('user-tasks.allotment', compact('ipo', 'placements'));
    }

    public function storeAllotment(Request $request, Ipo $ipo)
    {
        $this->checkMitraAccess();
        if ($ipo->step === 4) abort(404);
        $request->validate([
            'final_price_ipo' => 'required|numeric|min:0',
            'allocations' => 'required|array',
            'allocations.*.placement_id' => 'required|exists:ipo_account_placements,id',
            'allocations.*.lot_allocated' => 'required|integer|min:0',
        ]);

        $myAccountIds = $this->getMyAccountIds();

        foreach ($request->allocations as $data) {
            $placement = IpoAccountPlacement::with('fundings', 'ipo', 'mitraAccount')->find($data['placement_id']);
            
            if (!in_array($placement->mitra_account_id, $myAccountIds)) {
                continue;
            }

            $totalUsed = $data['lot_allocated'] * 100 * $request->final_price_ipo;
            $remaining = $placement->capital_allocated - $totalUsed;

            IpoAllocation::updateOrCreate(
                ['ipo_account_placement_id' => $placement->id],
                [
                    'lot_allocated' => $data['lot_allocated'],
                    'final_price_ipo' => $request->final_price_ipo,
                    'total_used' => $totalUsed,
                    'remaining_capital' => $remaining
                ]
            );

            if ($remaining > 0 && $placement->capital_allocated > 0) {
                foreach ($placement->fundings as $funding) {
                    $porsiRefund = ($funding->amount_funded / $placement->capital_allocated) * $remaining;
                    
                    \App\Models\InvestorTransaction::updateOrCreate(
                        [
                            'investor_id' => $funding->investor_id,
                            'description' => "Refund Sisa Modal IPO {$placement->ipo->code} ({$placement->mitraAccount->owner_name})"
                        ],
                        [
                            'amount' => $porsiRefund,
                            'type' => 'REFUND'
                        ]
                    );
                }
            }
        }

        return redirect()->route('user-tasks.index')->with('success', 'Hasil penjatahan berhasil disimpan!');
    }

    public function editSale(Ipo $ipo)
    {
        $this->checkMitraAccess();
        if ($ipo->step === 4) abort(404);
        if (now()->startOfDay() < \Carbon\Carbon::parse($ipo->ipo_date)->startOfDay()) {
            return redirect()->route('user-tasks.index')->with('error', "Penjualan belum bisa dilakukan. Emiten {$ipo->code} baru akan listing pada tanggal " . \Carbon\Carbon::parse($ipo->ipo_date)->format('d M Y') . ".");
        }

        $myAccountIds = $this->getMyAccountIds();
        $placements = $ipo->placements()
            ->whereIn('mitra_account_id', $myAccountIds)
            ->whereHas('allocation', function($q) {
                $q->where('lot_allocated', '>', 0);
            })
            ->doesntHave('sale')
            ->with('mitraAccount', 'allocation')
            ->get();

        if ($placements->isEmpty()) {
            return redirect()->route('user-tasks.index')->with('success', 'Semua akun Anda di emiten ini sudah direalisasikan (dijual).');
        }

        return view('user-tasks.sale', compact('ipo', 'placements'));
    }

    public function storeSale(Request $request, Ipo $ipo)
    {
        $this->checkMitraAccess();
        if ($ipo->step === 4) abort(404);
        if (now()->startOfDay() < \Carbon\Carbon::parse($ipo->ipo_date)->startOfDay()) {
            return redirect()->route('user-tasks.index')->with('error', 'Belum memasuki masa listing.');
        }

        $request->validate([
            'sales' => 'required|array',
            'sales.*.placement_id' => 'required|exists:ipo_account_placements,id',
            'sales.*.sell_price' => 'required|numeric|min:0',
        ]);

        $myAccountIds = $this->getMyAccountIds();

        foreach ($request->sales as $data) {
            $placement = IpoAccountPlacement::with('allocation', 'fundings', 'mitraAccount')->find($data['placement_id']);
            
            if (!in_array($placement->mitra_account_id, $myAccountIds) || !$placement->allocation) {
                continue;
            }

            $lot = $placement->allocation->lot_allocated;
            $buyPrice = $placement->allocation->final_price_ipo;
            $sellPrice = $data['sell_price'];
            
            $grossValueBuy = $lot * 100 * $buyPrice;
            $grossValueSell = $lot * 100 * $sellPrice;
            
            $grossProfit = $grossValueSell - $grossValueBuy;
            
            $feeBuy = $grossValueBuy * 0.0015;
            $feeSell = $grossValueSell * 0.0025;
            $totalFee = $feeBuy + $feeSell;
            
            $netProfit = $grossProfit - $totalFee;

            IpoSale::updateOrCreate(
                ['ipo_account_placement_id' => $placement->id],
                [
                    'sell_price' => $sellPrice,
                    'gross_profit' => $grossProfit,
                    'fee_buy' => $feeBuy,
                    'fee_sell' => $feeSell,
                    'net_profit' => $netProfit
                ]
            );

            if ($netProfit != 0 && $placement->capital_allocated > 0) {
                $netProfitAfterAppTax = $netProfit;
                if ($netProfit > 0) {
                    $appTax = $netProfit * 0.0025;
                    $netProfitAfterAppTax = $netProfit - $appTax;
                }

                $investorsShareTotal = $netProfitAfterAppTax * 0.50;

                foreach ($placement->fundings as $funding) {
                    $porsi = $funding->amount_funded / $placement->capital_allocated;
                    $investorProfit = $investorsShareTotal * $porsi;
                    
                    \App\Models\InvestorTransaction::updateOrCreate(
                        [
                            'investor_id' => $funding->investor_id,
                            'description' => "Profit/Loss IPO {$ipo->code} ({$placement->mitraAccount->owner_name})"
                        ],
                        [
                            'amount' => $investorProfit,
                            'type' => 'PROFIT'
                        ]
                    );

                    $modalReturn = $porsi * $grossValueBuy;
                    \App\Models\InvestorTransaction::updateOrCreate(
                        [
                            'investor_id' => $funding->investor_id,
                            'description' => "Pengembalian Modal Terpakai IPO {$ipo->code} ({$placement->mitraAccount->owner_name})"
                        ],
                        [
                            'amount' => $modalReturn,
                            'type' => 'DEPOSIT'
                        ]
                    );
                }
            } else if ($netProfit == 0 && $placement->capital_allocated > 0) {
                // Just return the modal
                foreach ($placement->fundings as $funding) {
                    $porsi = $funding->amount_funded / $placement->capital_allocated;
                    $modalReturn = $porsi * $grossValueBuy;
                    \App\Models\InvestorTransaction::updateOrCreate(
                        [
                            'investor_id' => $funding->investor_id,
                            'description' => "Pengembalian Modal Terpakai IPO {$ipo->code} ({$placement->mitraAccount->owner_name})"
                        ],
                        [
                            'amount' => $modalReturn,
                            'type' => 'DEPOSIT'
                        ]
                    );
                }
            }
        }

        return redirect()->route('user-tasks.index')->with('success', 'Hasil penjualan berhasil disimpan!');
    }

    public function myProfit()
    {
        $this->checkMitraAccess();
        $myAccountIds = $this->getMyAccountIds();
        
        // Ambil semua IPO yang sudah step >= 3 dan terkait dengan akun milik mitra ini
        $allIpos = Ipo::with(['placements' => function($q) use ($myAccountIds) {
            $q->whereIn('mitra_account_id', $myAccountIds)
              ->with('sale', 'mitraAccount');
        }])->orderBy('created_at', 'desc')->get();

        $ipos = $allIpos->filter(function($ipo) {
            return $ipo->step >= 3 && $ipo->placements->count() > 0;
        });

        $distributions = [];
        $grandTotalProfit = 0;
        $grandMitraProfit = 0;

        foreach ($ipos as $ipo) {
            $totalProfitKotorEvent = 0;
            $mitraProfitEvent = 0;
            $mitraPct = $ipo->mitra_fee_pct ?? 50; // Fallback to 50% if not yet distributed/set

            foreach ($ipo->placements as $placement) {
                if ($placement->sale) {
                    $netProfit = $placement->sale->net_profit;
                    $totalProfitKotorEvent += $netProfit;
                    
                    // Joki / Mitra profit berdasarkan setting IPO
                    $mitraProfitEvent += ($netProfit * ($mitraPct / 100));
                }
            }

            if ($totalProfitKotorEvent != 0 || $mitraProfitEvent != 0) {
                $distributions[] = [
                    'ipo' => $ipo,
                    'total_accounts' => $ipo->placements->count(),
                    'total_profit' => $totalProfitKotorEvent,
                    'mitra_profit' => $mitraProfitEvent,
                    'mitra_pct' => $mitraPct,
                ];

                $grandTotalProfit += $totalProfitKotorEvent;
                $grandMitraProfit += $mitraProfitEvent;
            }
        }

        return view('user-tasks.profit', compact('distributions', 'grandTotalProfit', 'grandMitraProfit'));
    }
}
