<?php

namespace App\Http\Controllers;

use App\Models\Ipo;
use App\Models\MitraAccount;
use App\Models\IpoAccountPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class IpoController extends Controller
{
    public function index()
    {
        $ipos = Ipo::latest('ipo_date')->paginate(10);
        return view('ipos.index', compact('ipos'));
    }

    public function report()
    {
        $ipos = Ipo::with(['placements.sale', 'placements.allocation'])->orderByDesc('id')->get();
        
        $reportData = $ipos->map(function($ipo) {
            $totalCapital = $ipo->placements->sum('capital_allocated');
            $totalUsed = $ipo->placements->sum(function($p) { return $p->allocation ? $p->allocation->total_used : 0; });
            $totalSales = $ipo->placements->sum(function($p) { return $p->sale ? $p->sale->total_return : 0; });
            $totalProfit = $ipo->placements->sum(function($p) { return $p->sale ? $p->sale->net_profit : 0; });
            
            // Investor's actual share of the profit
            $totalInvestorProfit = \App\Models\InvestorTransaction::where('type', 'PROFIT')
                                ->where('description', 'like', "%Profit Saham {$ipo->code}%")
                                ->sum('amount');
            
            // Total Refund/Withdrawals for this IPO
            $totalWithdrawn = \App\Models\InvestorTransaction::where('type', 'WITHDRAW')
                                ->where('description', 'like', "%Refund Emiten {$ipo->code}%")
                                ->sum('amount');
                                
            return (object) [
                'ipo' => $ipo,
                'total_capital' => $totalCapital,
                'total_used' => $totalUsed,
                'total_sales' => $totalSales,
                'total_profit' => $totalProfit,
                'total_investor_profit' => $totalInvestorProfit,
                'total_withdrawn' => $totalWithdrawn,
            ];
        });

        return view('ipos.report', compact('reportData'));
    }

    public function create()
    {
        return view('ipos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:ipos,code',
            'price' => 'required|numeric|min:0',
            'ipo_date' => 'required|date',
        ]);

        Ipo::create($validated);

        return redirect()->route('ipos.index')->with('success', 'Event IPO berhasil ditambahkan.');
    }

    public function show(Ipo $ipo)
    {
        $placements = $ipo->placements()
            ->with(['mitraAccount', 'fundings.investor', 'allocation', 'sale'])
            ->get();
            
        // For the placement UI merged in show
        $accounts = MitraAccount::where('status', 'aktif')
                    ->whereHas('placements', function($q) use ($ipo) {
                        $q->where('ipo_id', $ipo->id);
                    })->get();

        $investors = \App\Models\Investor::with('fundings.placement.allocation')->get();
        
        // Pre-compute available balance to prevent N+1 query explosion in the view loop
        foreach ($investors as $investor) {
            $investor->computed_balance = $investor->available_balance;
        }
        
        // Calculate current total allocated in THIS IPO per investor (for global tracking)
        $totalsByInvestor = \App\Models\InvestorFunding::whereHas('placement', function($q) use ($ipo) {
            $q->where('ipo_id', $ipo->id);
        })->groupBy('investor_id')
          ->selectRaw('investor_id, sum(amount_funded) as total')
          ->pluck('total', 'investor_id');

        return view('ipos.show', compact('ipo', 'placements', 'accounts', 'investors', 'totalsByInvestor'));
    }

    public function edit(Ipo $ipo)
    {
        if (!$ipo->canEdit()) {
            return redirect()->route('ipos.index')->with('error', 'Event IPO yang sudah selesai tidak dapat diedit lagi.');
        }
        return view('ipos.edit', compact('ipo'));
    }

    public function update(Request $request, Ipo $ipo)
    {
        if (!$ipo->canEdit()) {
            return redirect()->route('ipos.index')->with('error', 'Event IPO yang sudah selesai tidak dapat diubah.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:ipos,code,' . $ipo->id,
            'price' => 'required|numeric|min:0',
            'ipo_date' => 'required|date',
        ]);

        $ipo->update($validated);

        return redirect()->route('ipos.index')->with('success', 'Event IPO berhasil diperbarui.');
    }

    public function destroy(Ipo $ipo)
    {
        if (!$ipo->canDelete()) {
            return redirect()->route('ipos.index')->with('error', 'Event IPO tidak dapat dihapus karena sudah memiliki alokasi modal atau sudah selesai.');
        }

        // Cleanup Profit Transactions for this IPO
        \App\Models\InvestorTransaction::where('description', 'like', "Profit Saham {$ipo->code} %")->delete();

        // Cleanup Placements and related data
        foreach ($ipo->placements as $placement) {
            $placement->fundings()->delete();
            if ($placement->allocation) $placement->allocation()->delete();
            if ($placement->sale) $placement->sale()->delete();
        }
        $ipo->placements()->delete();

        if ($ipo->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($ipo->image_path);
        }

        $ipo->delete();
        return redirect()->route('ipos.index')->with('success', 'Event IPO beserta seluruh datanya berhasil dihapus.');
    }
    /**
     * POST: Select participating mitras for this IPO
     */
    public function selectMitras(Request $request, Ipo $ipo)
    {
        $request->validate([
            'mitra_ids' => 'nullable|array',
            'mitra_ids.*' => 'exists:mitra_accounts,id'
        ]);

        $selectedIds = $request->mitra_ids ?? [];

        // Create empty placements for newly selected
        foreach ($selectedIds as $id) {
            IpoAccountPlacement::firstOrCreate(
                ['ipo_id' => $ipo->id, 'mitra_account_id' => $id],
                ['capital_allocated' => 0, 'est_lot' => 0, 'mitra_share_pct' => 50]
            );
        }

        // Find placements that were UNSELECTED and delete them (including their fundings)
        $unselectedPlacements = $ipo->placements()
                                    ->whereNotIn('mitra_account_id', $selectedIds)
                                    ->get();
        
        foreach ($unselectedPlacements as $placement) {
            \App\Models\InvestorTransaction::where('type', 'REFUND')
                ->where('description', "Refund Sisa Modal IPO {$ipo->code} ({$placement->mitraAccount->owner_name})")
                ->delete();

            $placement->fundings()->delete();
            $placement->delete();
        }

        return redirect()->route('ipos.show', $ipo)->with('success', 'Pilihan Mitra berhasil diperbarui.');
    }

    /**
     * Store modal allocation for accounts
     */
    public function storePlacement(Request $request, Ipo $ipo)
    {
        // Bersihkan format ribuan (titik) dari input capital
        if ($request->has('allocations')) {
            $allocations = $request->input('allocations');
            foreach ($allocations as $idx => $alloc) {
                if (isset($alloc['investors'])) {
                    foreach ($alloc['investors'] as $i => $inv) {
                        if (isset($inv['capital'])) {
                            $allocations[$idx]['investors'][$i]['capital'] = str_replace('.', '', $inv['capital']);
                        }
                    }
                }
            }
            $request->merge(['allocations' => $allocations]);
        }

        $request->validate([
            'allocations' => 'required|array',
            'allocations.*.account_id' => 'required|exists:mitra_accounts,id',
            'allocations.*.est_lot' => 'nullable|numeric|min:0',
            'allocations.*.mitra_share_pct' => 'nullable|numeric|min:0|max:100',
            'allocations.*.investors' => 'nullable|array',
            'allocations.*.investors.*.investor_id' => 'nullable|exists:investors,id',
            'allocations.*.investors.*.capital' => 'nullable|numeric|min:0',
            'allocations.*.investors.*.share_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach ($request->allocations as $data) {
            $validInvestors = collect($data['investors'] ?? [])->filter(function($inv) {
                return !empty($inv['investor_id']) && !empty($inv['capital']) && $inv['capital'] > 0;
            });
            
            $estLot = $data['est_lot'] ?? 0;

            $totalCapital = $validInvestors->sum('capital');
            
            $investorsShare = collect($validInvestors)->sum('share_pct');

            if ($totalCapital > 0 && $investorsShare > 100) {
                $accName = MitraAccount::find($data['account_id'])->owner_name;
                return back()->withErrors(['error' => "Total bagi hasil investor untuk akun {$accName} melebihi 100%. (Saat ini: {$investorsShare}%)"])->withInput();
            }

            $placement = IpoAccountPlacement::updateOrCreate(
                ['ipo_id' => $ipo->id, 'mitra_account_id' => $data['account_id']],
                [
                    'capital_allocated' => $totalCapital, 
                    'est_lot' => $estLot,
                    'mitra_share_pct' => 100 - $investorsShare
                ]
            );

                // Re-sync fundings
                $placement->fundings()->delete();
                foreach ($validInvestors as $invData) {
                    $investor = \App\Models\Investor::find($invData['investor_id']);
                    
                    // True Available Pool = Current available balance + what was previously in THIS Ipo
                    // (Since current funding is part of active_deployment)
                    $alreadyInThisIpoForThisInvestor = \App\Models\InvestorFunding::where('investor_id', $investor->id)
                        ->whereHas('placement', function($q) use ($ipo) {
                            $q->where('ipo_id', $ipo->id);
                        })->sum('amount_funded');

                    $maxAvailable = $investor->available_balance + $alreadyInThisIpoForThisInvestor;

                    if ($invData['capital'] > $maxAvailable) {
                        return back()->withErrors(['error' => "Saldo {$investor->name} tidak mencukupi untuk alokasi baru. (Maksimal tersedia: Rp " . number_format($maxAvailable, 0, ',', '.') . ")"])->withInput();
                    }

                    $placement->fundings()->create([
                        'investor_id' => $invData['investor_id'],
                        'amount_funded' => $invData['capital'],
                        'share_pct' => $invData['share_pct']
                    ]);
                }
            // Clean up fundings if capital is 0
            if ($totalCapital == 0) {
                $placement->fundings()->delete();
            }
        }

        if ($request->has('stay')) {
            return back()->with('success', 'Semua baris di halaman ini berhasil disimpan.');
        }

        return redirect()->route('ipos.show', $ipo)->with('success', 'Alokasi modal dan pembagian hasil berhasil disimpan.');
    }

    /**
     * AJAX: Bulk store placement for all unfilled Mitra Accounts
     */
    public function storeBulkPlacement(Request $request, Ipo $ipo)
    {
        $request->validate([
            'est_lot' => 'required|numeric|min:1',
            'investor_id' => 'nullable|exists:investors,id',
            'share_pct' => 'required|numeric|min:0|max:100'
        ]);

        $lot = $request->est_lot;
        $investorId = $request->investor_id;
        $sharePct = $request->share_pct;
        $capital = $lot * $ipo->price * 100;

        $investor = $investorId ? \App\Models\Investor::find($investorId) : null;

        return DB::transaction(function() use ($ipo, $lot, $investor, $capital, $sharePct) {
            // Find all active mitras that do NOT have a placement for this IPO yet
            $unfilledAccounts = MitraAccount::where('status', 'aktif')
                ->whereDoesntHave('placements', function($q) use ($ipo) {
                    $q->where('ipo_id', $ipo->id);
                })->get();

            if ($unfilledAccounts->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Semua akun Mitra aktif sudah memiliki alokasi modal.'], 400);
            }

            if ($investor) {
                $totalNeeded = $unfilledAccounts->count() * $capital;
                if ($investor->available_balance < $totalNeeded) {
                    return response()->json([
                        'success' => false, 
                        'message' => "Saldo Investor tidak mencukupi untuk memodali {$unfilledAccounts->count()} akun. (Butuh: Rp " . number_format($totalNeeded, 0, ',', '.') . ")"
                    ], 400);
                }
            }

            foreach ($unfilledAccounts as $account) {
                $placement = $ipo->placements()->create([
                    'mitra_account_id' => $account->id,
                    'capital_allocated' => $investor ? $capital : 0,
                    'est_lot' => $lot,
                    'mitra_share_pct' => 100 - $sharePct,
                ]);

                if ($investor) {
                    $placement->fundings()->create([
                        'investor_id' => $investor->id,
                        'amount_funded' => $capital,
                        'share_pct' => $sharePct
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => "Berhasil memodali {$unfilledAccounts->count()} akun Mitra secara massal!"]);
        });
    }

    /**
     * AJAX: Store a single row placement (Per Account confirmation)
     */
    public function storeRowPlacement(Request $request, Ipo $ipo)
    {
        try {
            $data = $request->validate([
                'account_id' => 'required|exists:mitra_accounts,id',
                'est_lot' => 'required|numeric|min:0',
                'mitra_share_pct' => 'nullable|numeric|min:0|max:100',
                'investors' => 'required|array|min:1',
                'investors.*.investor_id' => 'required|exists:investors,id',
                'investors.*.capital' => 'required|numeric|min:0',
                'investors.*.share_pct' => 'required|numeric|min:0|max:100',
            ]);

            return DB::transaction(function() use ($data, $ipo) {
                $totalCapital = collect($data['investors'])->sum('capital');
                $investorsShare = collect($data['investors'])->sum('share_pct');

                if ($investorsShare > 100) {
                    return response()->json(['success' => false, 'message' => "Total bagi hasil investor tidak boleh lebih dari 100%. (Saat ini: {$investorsShare}%)"], 422);
                }

                // Balance Check
                foreach ($data['investors'] as $invData) {
                    $investor = \App\Models\Investor::find($invData['investor_id']);
                    
                    // Already in this IPO for this investor (from ALL placements excluding the current account being updated)
                    $otherAllocationsInIpo = \App\Models\InvestorFunding::where('investor_id', $investor->id)
                        ->whereHas('placement', function($q) use ($ipo, $data) {
                            $q->where('ipo_id', $ipo->id)->where('mitra_account_id', '!=', $data['account_id']);
                        })->sum('amount_funded');

                    $maxAvailable = $investor->available_balance + \App\Models\InvestorFunding::where('investor_id', $investor->id)->whereHas('placement', function($q) use ($ipo, $data){ $q->where('ipo_id', $ipo->id)->where('mitra_account_id', $data['account_id']); })->sum('amount_funded');
                    
                    if ($invData['capital'] > $maxAvailable) {
                        return response()->json(['success' => false, 'message' => "Saldo {$investor->name} tidak mencukupi. (Max: Rp " . number_format($maxAvailable, 0, ',', '.') . ")"], 422);
                    }
                }

                $placement = IpoAccountPlacement::updateOrCreate(
                    ['ipo_id' => $ipo->id, 'mitra_account_id' => $data['account_id']],
                    [
                        'capital_allocated' => $totalCapital,
                        'est_lot' => $data['est_lot'],
                        'mitra_share_pct' => 100 - $investorsShare
                    ]
                );

                $placement->fundings()->delete();
                foreach ($data['investors'] as $invData) {
                    $placement->fundings()->create([
                        'investor_id' => $invData['investor_id'],
                        'amount_funded' => $invData['capital'],
                        'share_pct' => $invData['share_pct']
                    ]);
                }

                return response()->json([
                    'success' => true, 
                    'message' => 'Alokasi Berhasil Terkunci!',
                    'total_allocated' => number_format($totalCapital, 0, ',', '.')
                ]);
            });

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Reset (Delete) a single row placement
     */
    public function destroyRowPlacement(Ipo $ipo, MitraAccount $account)
    {
        try {
            $placement = IpoAccountPlacement::where('ipo_id', $ipo->id)
                                            ->where('mitra_account_id', $account->id)
                                            ->first();
            
            if ($placement) {
                \App\Models\InvestorTransaction::where('type', 'REFUND')
                    ->where('description', "Refund Sisa Modal IPO {$ipo->code} ({$placement->mitraAccount->owner_name})")
                    ->delete();

                // Deleting placement will cascade delete fundings if configured,
                // or we manually delete them to be safe.
                $placement->fundings()->delete();
                $placement->delete();
            }

            return response()->json([
                'success' => true, 
                'message' => 'Alokasi berhasil direset dan saldo dikembalikan.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Reset (Delete) ALL placements for this IPO
     */
    public function resetAllPlacements(Ipo $ipo)
    {
        try {
            DB::transaction(function() use ($ipo) {
                \App\Models\InvestorTransaction::where('type', 'REFUND')
                    ->where('description', 'like', "Refund Sisa Modal IPO {$ipo->code}%")
                    ->delete();

                foreach ($ipo->placements as $placement) {
                    $placement->fundings()->delete();
                    $placement->delete();
                }
            });

            return response()->json(['success' => true, 'message' => 'Semua alokasi pada IPO ini berhasil direset. Dana telah dikembalikan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Reset (Delete) ALL allotments for this IPO
     */
    public function resetAllAllotments(Ipo $ipo)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function() use ($ipo) {
                \App\Models\InvestorTransaction::where('type', 'REFUND')
                    ->where('description', 'like', "Refund Sisa Modal IPO {$ipo->code}%")
                    ->delete();

                foreach ($ipo->placements as $placement) {
                    if ($placement->allocation) {
                        $placement->allocation->delete();
                    }
                }
            });

            return response()->json(['success' => true, 'message' => 'Semua Jatah Lot berhasil direset.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Reset (Delete) ALL data for this IPO completely (Master Reset)
     */
    public function resetAllData(Ipo $ipo)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function() use ($ipo) {
                // Hapus transaksi profit yang berasal dari IPO ini
                \App\Models\InvestorTransaction::where('type', 'PROFIT')
                    ->where('description', 'like', "Profit Saham {$ipo->code}%")
                    ->delete();

                // Hapus penarikan manual (WITHDRAW) yang terkait IPO ini
                \App\Models\InvestorTransaction::where('type', 'WITHDRAW')
                    ->where('description', 'like', "%Refund Emiten {$ipo->code}%")
                    ->delete();

                // Hapus pengembalian modal otomatis (REFUND) yang terkait IPO ini
                \App\Models\InvestorTransaction::where('type', 'REFUND')
                    ->where('description', 'like', "Refund Sisa Modal IPO {$ipo->code}%")
                    ->delete();

                foreach ($ipo->placements as $placement) {
                    if ($placement->sale) {
                        $placement->sale->delete();
                    }
                    if ($placement->allocation) {
                        $placement->allocation->delete();
                    }
                    $placement->fundings()->delete();
                    $placement->delete();
                }

                $ipo->update(['step' => 1]);
            });

            return response()->json(['success' => true, 'message' => 'Seluruh data IPO ini berhasil direset dan dana dikembalikan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Reset (Delete) ALL sales for this IPO
     */
    public function resetAllSales(Ipo $ipo)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function() use ($ipo) {
                \App\Models\InvestorTransaction::where('type', 'PROFIT')
                    ->where('description', 'like', "Profit Saham {$ipo->code}%")
                    ->delete();

                foreach ($ipo->placements as $placement) {
                    if ($placement->sale) {
                        $placement->sale->delete();
                    }
                }
            });

            return response()->json(['success' => true, 'message' => 'Semua Hasil Penjualan berhasil direset.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Export placements for a specific IPO to CSV (Excel compatible)
     */
    public function exportPlacements(Ipo $ipo)
    {
        $placements = $ipo->placements()
            ->with(['mitraAccount', 'fundings.investor', 'allocation'])
            ->get();

        $fileName = 'Laporan_Detail_IPO_' . str_replace(' ', '_', $ipo->code) . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($placements, $ipo) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fputs($file, (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // CSV Headers
            fputcsv($file, [
                'AKUN MITRA', 
                'PLATFORM', 
                'PEMODAL (INVESTOR)', 
                'MODAL PESANAN', 
                'ESTIMASI LOT', 
                'JATAH DAPAT (LOT)', 
                'DANA TERPAKAI', 
                'SISA DANA',
                'HASIL JUAL',
                'NET PROFIT'
            ], ';');

            foreach ($placements as $p) {
                // Combine investor names
                $investorNames = $p->fundings->map(function($f) {
                    return $f->investor->name . ' (Rp ' . number_format($f->amount_funded, 0, ',', '.') . ')';
                })->implode('; ');

                fputcsv($file, [
                    $p->mitraAccount->owner_name,
                    $p->mitraAccount->platform,
                    $investorNames,
                    'Rp. ' . number_format($p->capital_allocated, 0, ',', '.'),
                    $p->est_lot,
                    $p->allocation ? $p->allocation->lot_allocated : 0,
                    'Rp. ' . number_format($p->allocation ? $p->allocation->total_used : 0, 0, ',', '.'),
                    'Rp. ' . number_format($p->allocation ? $p->allocation->remaining_capital : 0, 0, ',', '.'),
                    'Rp. ' . number_format($p->sale ? $p->sale->total_return : 0, 0, ',', '.'),
                    'Rp. ' . number_format($p->sale ? $p->sale->net_profit : 0, 0, ',', '.'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function exportReport()
    {
        $ipos = Ipo::with(['placements.sale', 'placements.allocation'])->orderByDesc('id')->get();
        
        $fileName = 'Laporan_Rekap_IPO_' . date('Y-m-d') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($ipos) {
            $file = fopen('php://output', 'w');
            fputs($file, (chr(0xEF) . chr(0xBB) . chr(0xBF))); // Add BOM
            
            fputcsv($file, [
                'EMITEN', 
                'STATUS', 
                'TOTAL AKUN DIMODALI', 
                'MODAL MASUK', 
                'HASIL PENJUALAN', 
                'NET PROFIT', 
                'DANA DITARIK (WITHDRAW)',
                'STATUS DANA'
            ], ';');

            foreach ($ipos as $ipo) {
                $totalCapital = $ipo->placements->sum('capital_allocated');
                $totalSales = $ipo->placements->sum(function($p) { return $p->sale ? $p->sale->total_return : 0; });
                $totalProfit = $ipo->placements->sum(function($p) { return $p->sale ? $p->sale->net_profit : 0; });
                
                $totalInvestorProfit = \App\Models\InvestorTransaction::where('type', 'PROFIT')
                                    ->where('description', 'like', "%Profit Saham {$ipo->code}%")
                                    ->sum('amount');
                
                $totalWithdrawn = \App\Models\InvestorTransaction::where('type', 'WITHDRAW')
                                    ->where('description', 'like', "%Refund Emiten {$ipo->code}%")
                                    ->sum('amount');
                                    
                $expectedReturn = $ipo->step >= 3 ? ($totalCapital + $totalInvestorProfit) : $totalCapital;
                $unreturned = $expectedReturn - $totalWithdrawn;
                if ($unreturned < 100) $unreturned = 0;
                $isComplete = ($ipo->step == 4) && ($unreturned <= 0);

                if($ipo->step < 3) $statusDana = 'MENUNGGU PENJUALAN';
                elseif($isComplete) $statusDana = 'CLEAR / SELESAI';
                else $statusDana = 'BELUM SELESAI (Sisa: ' . $unreturned . ')';

                fputcsv($file, [
                    $ipo->code . ' - ' . $ipo->name,
                    strtoupper($ipo->status_label),
                    $ipo->placements->count(),
                    'Rp. ' . number_format($totalCapital, 0, ',', '.'),
                    'Rp. ' . number_format($totalSales, 0, ',', '.'),
                    'Rp. ' . number_format($totalProfit, 0, ',', '.'),
                    'Rp. ' . number_format($totalWithdrawn, 0, ',', '.'),
                    $statusDana
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function tickerLive($ticker)
    {
        /*
        // ORIGINAL PYTHON SCRIPT LOGIC (KEPT FOR FUTURE REFERENCE)
        // Note: Vercel Serverless PHP doesn't support Python out of the box.
        $scriptPath = base_path('scripts/ticker_live.py');
        $escapedTicker = escapeshellarg($ticker);
        $command = "python \"{$scriptPath}\" {$escapedTicker}";
        
        $output = shell_exec($command);
        $result = json_decode($output, true);
        
        return response()->json($result);
        */

        $cleanTicker = strtoupper(trim($ticker));
        if (!str_ends_with($cleanTicker, '.JK') && $cleanTicker !== '^JKSE') {
            $fullTicker = "{$cleanTicker}.JK";
        } else {
            $fullTicker = $cleanTicker;
        }

        try {
            $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$fullTicker}?interval=1m&range=2d";
            $response = \Illuminate\Support\Facades\Http::get($url);
            
            if (!$response->successful()) {
                return response()->json(["error" => "No data found for {$fullTicker}", "status" => "error"]);
            }

            $data = $response->json();
            $result = $data['chart']['result'][0] ?? null;

            if (!$result || empty($result['indicators']['quote'][0]['close'])) {
                return response()->json(["error" => "No data found for {$fullTicker}", "status" => "error"]);
            }

            $closes = array_filter($result['indicators']['quote'][0]['close'], function($val) { return $val !== null; });
            if (empty($closes)) {
                return response()->json(["error" => "No data found for {$fullTicker}", "status" => "error"]);
            }

            $currentPrice = end($closes);
            $prevClose = $result['meta']['chartPreviousClose'] ?? $currentPrice;
            
            $change = $currentPrice - $prevClose;
            $changePct = $prevClose != 0 ? ($change / $prevClose) * 100 : 0;

            return response()->json([
                "symbol" => $fullTicker,
                "current_price" => round($currentPrice, 2),
                "change" => round($change, 2),
                "change_pct" => round($changePct, 2),
                "status" => "success",
                "last_update" => date("H:M:S")
            ]);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage(), "status" => "error"]);
        }
    }
}
