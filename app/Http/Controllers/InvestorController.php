<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    public function index()
    {
        $investors = Investor::with(['fundings.placement.allocation'])->paginate(10);
        $ipos = \App\Models\Ipo::orderBy('id', 'desc')->get();
        return view('investors.index', compact('investors', 'ipos'));
    }

    public function create()
    {
        $users = \App\Models\User::where('role', 'investor')->whereDoesntHave('investor')->get();
        return view('investors.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_capital' => 'required|numeric|min:0',
            'create_account' => 'nullable|boolean',
            'username' => 'nullable|required_if:create_account,1|string|max:255|unique:users,username',
            'password' => 'nullable|required_if:create_account,1|string|min:6',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $userId = $validated['user_id'] ?? null;

        if (!empty($validated['create_account'])) {
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['username'] . '@algrow.local',
                'password' => bcrypt($validated['password']),
                'role' => 'investor'
            ]);
            $userId = $user->id;
        }

        $investor = Investor::create([
            'name' => $validated['name'],
            'user_id' => $userId
        ]);
        
        // Record initial deposit transaction
        if ($validated['total_capital'] > 0) {
            $investor->transactions()->create([
                'amount' => $validated['total_capital'],
                'type' => 'DEPOSIT',
                'description' => 'Setoran modal awal'
            ]);
        }

        return redirect()->route('investors.index')->with('success', 'Investor berhasil ditambahkan');
    }

    public function show(Investor $investor)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role === 'user' || ($user->role === 'investor' && $investor->user_id !== $user->id)) {
            abort(403, 'Akses ditolak. Anda hanya bisa melihat profil Anda sendiri.');
        }

        $fundings = $investor->fundings()->with(['placement.ipo', 'placement.mitraAccount', 'placement.allocation', 'placement.sale'])->get();
        
        $totalFunded = $fundings->sum('amount_funded');
        $totalUsedByIpo = 0;
        
        foreach($fundings as $f) {
            if ($f->placement->allocation) {
                $totalUsedByIpo += $f->placement->allocation->total_used;
            }
        }

        $sisaDana = $investor->available_balance;
        $danaKembali = $totalFunded - $totalUsedByIpo; // Money returned after IPO allotment
        $ipos = \App\Models\Ipo::orderBy('id', 'desc')->get();

        return view('investors.show', compact('investor', 'fundings', 'totalFunded', 'sisaDana', 'danaKembali', 'ipos'));
    }

    /**
     * Show form to fund specific placements
     */
    public function fund(Investor $investor)
    {
        // Get placements that don't have funding yet
        $availablePlacements = \App\Models\IpoAccountPlacement::whereDoesntHave('fundings')
            ->with(['ipo', 'mitraAccount'])
            ->get();

        return view('investors.fund', compact('investor', 'availablePlacements'));
    }

    /**
     * Store funding for placements
     */
    public function storeFund(Request $request, Investor $investor)
    {
        $request->validate([
            'placements' => 'required|array',
            'placements.*.id' => 'required|exists:ipo_account_placements,id',
            'placements.*.amount' => 'required|numeric|min:0',
        ]);

        foreach ($request->placements as $pData) {
            if ($pData['amount'] > 0) {
                \App\Models\InvestorFunding::updateOrCreate(
                    ['ipo_account_placement_id' => $pData['id']],
                    ['investor_id' => $investor->id, 'amount_funded' => $pData['amount']]
                );
            }
        }

        return redirect()->route('investors.show', $investor)->with('success', 'Dana investor berhasil dialokasikan ke prospek IPO.');
    }

    /**
     * Handle deposit of funds to investor's available balance
     */
    public function deposit(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255'
        ]);

        $investor->transactions()->create([
            'amount' => $validated['amount'],
            'type' => 'DEPOSIT',
            'description' => $validated['description'] ?? 'Penyetoran modal tambahan'
        ]);

        return redirect()->route('investors.show', $investor)->with('success', 'Penyetoran saldo senilai Rp ' . number_format($validated['amount'], 0, ',', '.') . ' berhasil dicatat.');
    }

    /**
     * Handle withdrawal of funds from investor's available balance
     */
    public function withdraw(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'source_ipo' => 'nullable|string'
        ]);

        $amount = $validated['amount'];
        $available = $investor->available_balance;

        if ($amount > $available) {
            return back()->withErrors(['error' => 'Jumlah penarikan melebihi saldo tersedia (Rp ' . number_format($available, 0, ',', '.') . ').'])->withInput();
        }
        
        $desc = $validated['description'] ?? null;
        if ($request->filled('source_ipo')) {
            $prefix = 'Refund Emiten ' . $request->source_ipo;
            $desc = $desc ? $prefix . ' - ' . $desc : $prefix;
        }

        $investor->transactions()->create([
            'amount' => $amount,
            'type' => 'WITHDRAW',
            'description' => $desc ?: 'Penarikan Saldo'
        ]);

        return redirect()->route('investors.show', $investor)->with('success', 'Penarikan saldo senilai Rp ' . number_format($amount, 0, ',', '.') . ' berhasil dicatat.');
    }


    public function edit(Investor $investor)
    {
        $users = \App\Models\User::where('role', 'investor')
            ->where(function($q) use ($investor) {
                $q->whereDoesntHave('investor')->orWhere('id', $investor->user_id);
            })->get();
        return view('investors.edit', compact('investor', 'users'));
    }

    public function update(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'total_capital' => 'required|numeric|min:0',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $oldCapital = $investor->total_capital; // Uses accessor to get current ledger balance
        $newCapital = $validated['total_capital'];
        $diff = $newCapital - $oldCapital;

        if ($diff != 0) {
            if ($diff < 0) {
                // If decreasing, check if available balance allows it
                if (abs($diff) > $investor->available_balance) {
                    return back()->withErrors(['error' => 'Saldo investor sedang terpakai di IPO aktif. Tidak bisa mengurangi modal melebihi saldo tersedia (Rp ' . number_format($investor->available_balance, 0, ',', '.') . ').'])->withInput();
                }
                
                $investor->transactions()->create([
                    'amount' => abs($diff),
                    'type' => 'WITHDRAW',
                    'description' => 'Penyesuaian modal (Edit Data)'
                ]);
            } else {
                $investor->transactions()->create([
                    'amount' => $diff,
                    'type' => 'DEPOSIT',
                    'description' => 'Penyesuaian modal (Edit Data)'
                ]);
            }
        }

        // Even though accessor overrides it, update database column for integrity
        $investor->update([
            'name' => $validated['name'],
            'user_id' => $validated['user_id'] ?? null
        ]);

        return redirect()->route('investors.index')->with('success', 'Data investor berhasil diperbarui');
    }

    public function destroy(Investor $investor)
    {
        $investor->delete();
        return redirect()->route('investors.index')->with('success', 'Investor berhasil dihapus');
    }

    /**
     * Export finished IPO history to CSV
     */
    public function export(Investor $investor)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role === 'user' || ($user->role === 'investor' && $investor->user_id !== $user->id)) {
            abort(403, 'Akses ditolak. Anda hanya bisa mengekspor data Anda sendiri.');
        }

        $profitPerIpo = [];
        
        // Parse actual profit from recorded transactions
        $profitTransactions = $investor->transactions()->where('type', 'PROFIT')->get();
        foreach($profitTransactions as $tx) {
            preg_match('/Profit Saham ([A-Z0-9]+) -/', $tx->description, $matches);
            if (count($matches) > 1) {
                $ipoCode = $matches[1];
                if(!isset($profitPerIpo[$ipoCode])) {
                    $profitPerIpo[$ipoCode] = [
                        'profit' => 0,
                        'modal' => 0
                    ];
                }
                $profitPerIpo[$ipoCode]['profit'] += $tx->amount;
            }
        }

        // Get used capital for those IPOs
        foreach ($investor->fundings as $funding) {
            $placement = $funding->placement;
            if ($placement->allocation && isset($profitPerIpo[$placement->ipo->code])) {
                $iRatio = $funding->amount_funded / $placement->capital_allocated;
                $profitPerIpo[$placement->ipo->code]['modal'] += ($placement->allocation->total_used * $iRatio);
            }
        }

        $fileName = 'Rincian_Profit_' . str_replace(' ', '_', $investor->name) . '_' . date('Y-m-d') . '.xlsx';
        
        $excelData = [];
        $excelData[] = ['Nama Investor :', strtoupper($investor->name)];
        $excelData[] = []; // Empty row
        
        $b = '<style border="#000000">';
        $c = '</style>';
        
        $excelData[] = [$b.'<b>CODE EMITEN</b>'.$c, $b.'<b>PROFIT</b>'.$c];

        foreach ($profitPerIpo as $code => $data) {
            if ($data['profit'] > 0) {
                $excelData[] = [
                    $b . $code . $c,
                    $b . 'Rp. ' . number_format($data['profit'], 0, ',', '.') . $c
                ];
            }
        }

        if (count($excelData) === 3) {
            $excelData[] = [$b . 'Belum ada profit terealisasi.' . $c, $b . '' . $c];
        }

        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($excelData);
        $xlsx->downloadAs($fileName);
        exit;
    }

    public function portfolio(\App\Models\Investor $investor)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role === 'user' || ($user->role === 'investor' && $investor->user_id !== $user->id)) {
            abort(403, 'Unauthorized access.');
        }

        $isOwner = (strtoupper(trim($investor->name)) === 'MUHAMAD ALDIANSYAH' || $investor->id == 3);

        $portfolioData = [];
        $grandTotalModal = 0;
        $grandTotalProfitBersih = 0;
        $grandTotalFeePlatform = 0;

        if ($isOwner) {
            $ipos = \App\Models\Ipo::with(['placements.sale', 'placements.fundings.investor', 'placements.mitraAccount', 'placements.allocation'])->get();
            $ipos = $ipos->filter(function($ipo) { return $ipo->step >= 3; });
            
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
                            
                            $mitraPct = $ipo->mitra_fee_pct ?? 0;
                            $investorPct = $ipo->platform_fee_pct ?? 0;
                            
                            $totalModalEvent += $capital;
                            $totalProfitKotorEvent += $gross;
                            $totalProfitMitraEvent += $gross * ($mitraPct / 100);
                            $totalProfitBersihInvestorEvent += $gross * ($investorPct / 100);
                        }
                    }
                }
                
                if ($hasInvolvement) {
                    $portfolioData[] = [
                        'ipo' => $ipo,
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
        } else {
            $investor->load(['fundings.placement.ipo', 'fundings.placement.mitraAccount', 'fundings.placement.allocation', 'fundings.placement.sale']);
            
            $fundingsByIpo = $investor->fundings->groupBy(function($f) {
                return $f->placement->ipo_id;
            });

            foreach ($fundingsByIpo as $ipoId => $fundings) {
                $ipo = $fundings->first()->placement->ipo;
                
                $totalModalEvent = 0;
                $totalProfitKotorEvent = 0;
                $totalProfitMitraEvent = 0;
                $totalProfitBersihInvestorEvent = 0;
                $totalMitra = $fundings->count();

                foreach ($fundings as $f) {
                    $p = $f->placement;
                    $ratio = 0;
                    if ($p->capital_allocated > 0) {
                        $ratio = $f->amount_funded / $p->capital_allocated;
                    }
                    
                    $capital = ($p->allocation ? $p->allocation->total_used * $ratio : 0);
                    $gross = ($p->sale ? $p->sale->net_profit * $ratio : 0);
                    
                    $mitraPct = $ipo->mitra_fee_pct ?? 0;
                    $investorPct = $ipo->platform_fee_pct ?? 0;
                    
                    $totalModalEvent += $capital;
                    $totalProfitKotorEvent += $gross;
                    $totalProfitMitraEvent += $gross * ($mitraPct / 100);
                    $totalProfitBersihInvestorEvent += $gross * ($investorPct / 100);
                }

                $portfolioData[] = [
                    'ipo' => $ipo,
                    'total_mitra' => $totalMitra,
                    'modal_terpakai' => $totalModalEvent,
                    'profit_kotor' => $totalProfitKotorEvent,
                    'porsi_mitra' => $totalProfitMitraEvent,
                    'profit_bersih' => $totalProfitBersihInvestorEvent,
                    'fee_platform' => 0
                ];

                $grandTotalModal += $totalModalEvent;
                $grandTotalProfitBersih += $totalProfitBersihInvestorEvent;
            }
        }

        // Sort by id desc
        usort($portfolioData, function($a, $b) {
            return $b['ipo']->id <=> $a['ipo']->id;
        });

        return view('investors.portfolio', compact('investor', 'portfolioData', 'grandTotalModal', 'grandTotalProfitBersih', 'isOwner', 'grandTotalFeePlatform'));
    }

    public function transactions(\App\Models\Investor $investor)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role === 'user' || ($user->role === 'investor' && $investor->user_id !== $user->id)) {
            abort(403, 'Unauthorized access.');
        }

        $transactions = $investor->transactions()->orderBy('created_at', 'desc')->get();
        return view('investors.transactions', compact('investor', 'transactions'));
    }

    public function updateAccount(Request $request, \App\Models\Investor $investor)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role === 'user' || ($user->role === 'investor' && $investor->user_id !== $user->id)) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $investor->user_id,
            'password' => 'nullable|string|min:6',
        ]);

        $investorUser = $investor->user;
        if ($investorUser) {
            $investorUser->username = $request->username;
            if ($request->filled('password')) {
                $investorUser->password = bcrypt($request->password);
            }
            $investorUser->save();
        }

        return back()->with('success', 'Akun login berhasil diperbarui!');
    }
}

