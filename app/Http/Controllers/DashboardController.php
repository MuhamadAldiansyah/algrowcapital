<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investor;
use App\Models\MitraAccount;
use App\Models\Ipo;
use App\Models\IpoSale;
use App\Models\InvestorTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        if ($user->role === 'investor') {
            $investors = Investor::with(['fundings.placement.allocation', 'fundings.placement.ipo.sales', 'transactions'])
                ->where('user_id', $user->id)
                ->get();
            $totalAkun = \App\Models\MitraAccount::whereHas('placements.fundings.investor', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count();
            $recentTransactions = InvestorTransaction::with('investor')
                ->whereHas('investor', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->latest()->take(8)->get();
        } else {
            $investorsQuery = Investor::with(['fundings.placement.allocation', 'fundings.placement.ipo.sales', 'transactions']);
            $mitraQuery = MitraAccount::where('status', 'aktif');
            $recentTransactionsQuery = InvestorTransaction::with('investor')->latest();
            
            if ($user->role !== 'developer') {
                $investorsQuery->where('tenant_id', $user->tenant_id);
                $mitraQuery->where('tenant_id', $user->tenant_id);
                $recentTransactionsQuery->whereHas('investor', function($q) use ($user) {
                    $q->where('tenant_id', $user->tenant_id);
                });
            }

            $investors = $investorsQuery->get();
            $totalAkun = $mitraQuery->count();
            $recentTransactions = $recentTransactionsQuery->take(8)->get();
        }

        $totalModal = $investors->sum('total_capital');
        $totalAvailable = $investors->sum('available_balance');
        $ipoQuery = Ipo::query();
        if ($user->role !== 'developer') {
            $ipoQuery->where('tenant_id', $user->tenant_id);
        }
        $totalIpo = $ipoQuery->count();

        $ipoSaleQuery = IpoSale::query();
        if ($user->role !== 'developer') {
            $ipoSaleQuery->whereHas('ipo', function($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            });
        }
        $totalNetProfit = $ipoSaleQuery->sum('net_profit');
        
        $roi = $totalModal > 0 ? ($totalNetProfit / $totalModal) * 100 : 0;

        // Fetch profit per IPO for charting
        $ipoProfits = $ipoSaleQuery->with('ipo')->latest()->take(5)->get()->reverse();

        // Active IPOs for tracking
        $allIpos = $ipoQuery->latest()->get();

        // Calculate each investor's actual profit from their recorded PROFIT transactions
        $totalInvestorProfit = 0;
        $investorData = $investors->map(function($investor) use (&$totalInvestorProfit, $user) {
            $individualProfit = $investor->transactions->where('type', 'PROFIT')->sum('amount');

            $totalInvestorProfit += $individualProfit;

            $totalDeposits = $investor->transactions->where('type', 'DEPOSIT')->sum('amount');
            return [
                'name' => $investor->name,
                'capital' => $totalDeposits > 0 ? $totalDeposits : 1, // Prevent division by zero
                'available' => $investor->available_balance,
                'profit' => $individualProfit
            ];
        });

        $roi = $totalModal > 0 ? ($totalInvestorProfit / $totalModal) * 100 : 0;

        // Fetch completed IPOs and calculate their gross & net profit
        $completedIposData = $allIpos->filter(function($ipo) {
            return $ipo->profit_distributed_at !== null;
        })->map(function($ipo) {
            $grossProfit = \App\Models\IpoSale::where('ipo_id', $ipo->id)->sum('net_profit');
            $netProfit = \App\Models\InvestorTransaction::where('type', 'PROFIT')
                            ->where('description', 'like', "Profit Saham {$ipo->code}%")
                            ->whereHas('investor', function($q) use ($ipo) {
                                $q->where('tenant_id', $ipo->tenant_id);
                            })
                            ->sum('amount');
            return [
                'code' => $ipo->code,
                'name' => $ipo->name,
                'image_path' => $ipo->image_path,
                'gross_profit' => $grossProfit,
                'net_profit' => $netProfit,
                'mitra_share' => $grossProfit - $netProfit
            ];
        });

        // Fetch active IPO codes for TradingView
        $activeIpoTickers = $allIpos->filter(function($ipo) {
            return $ipo->step < 4;
        })->map(function($ipo) {
            return [
                'proName' => 'IDX:' . strtoupper($ipo->code),
                'title' => strtoupper($ipo->code)
            ];
        });

        return view('dashboard', [
            'completedIposData' => $completedIposData,
            'totalModal' => $totalModal,
            'totalAvailable' => $totalAvailable,
            'totalAkun' => $totalAkun,
            'totalIpo' => $totalIpo,
            'totalProfit' => $totalInvestorProfit,
            'roi' => $roi,
            'investorData' => $investorData,
            'activeIpoTickers' => $activeIpoTickers->values(),
            'onlyActiveIpos' => $activeIpoTickers->values(),
            'allIpos' => $allIpos
        ]);
    }
}
