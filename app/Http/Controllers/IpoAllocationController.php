<?php

namespace App\Http\Controllers;

use App\Models\IpoAllocation;
use App\Models\IpoAccountPlacement;
use Illuminate\Http\Request;

class IpoAllocationController extends Controller
{
    /**
     * Show form to input final lot for a placement
     */
    public function create(IpoAccountPlacement $placement)
    {
        if (!$placement->ipo->canEdit()) {
            return redirect()->route('ipos.show', $placement->ipo_id)->with('error', 'Penjatahan tidak dapat diubah karena IPO sudah selesai.');
        }
        return view('ipo-allocations.create', compact('placement'));
    }

    /**
     * Store final lot and calculate usage
     */
    public function store(Request $request, IpoAccountPlacement $placement)
    {
        if (!$placement->ipo->canEdit()) {
            return redirect()->route('ipos.show', $placement->ipo_id)->with('error', 'Penjatahan tidak dapat diubah karena IPO sudah selesai.');
        }

        $validated = $request->validate([
            'lot_allocated' => 'required|integer|min:0',
            'final_price_ipo' => 'required|numeric|min:0',
        ]);

        $totalUsed = $validated['lot_allocated'] * 100 * $validated['final_price_ipo'];
        $remaining = $placement->capital_allocated - $totalUsed;

        IpoAllocation::updateOrCreate(
            ['ipo_account_placement_id' => $placement->id],
            [
                'lot_allocated' => $validated['lot_allocated'],
                'final_price_ipo' => $validated['final_price_ipo'],
                'total_used' => $totalUsed,
                'remaining_capital' => $remaining
            ]
        );

        // CREATE REFUND TRANSACTIONS
        if ($remaining > 0 && $placement->capital_allocated > 0) {
            foreach ($placement->fundings as $funding) {
                // Proportional refund
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

        return redirect()->route('ipos.show', $placement->ipo_id)->with('success', 'Hasil penjatahan berhasil disimpan. Sisa modal telah otomatis dikembalikan ke saldo investor.');
    }

    /**
     * Show bulk allotment form
     */
    public function bulkCreate(\App\Models\Ipo $ipo)
    {
        $placements = $ipo->placements()->with('mitraAccount', 'allocation')->get();
        return view('ipo-allocations.bulk', compact('ipo', 'placements'));
    }

    /**
     * Store bulk allotment
     */
    public function bulkStore(Request $request, \App\Models\Ipo $ipo)
    {
        $request->validate([
            'final_price_ipo' => 'required|numeric|min:0',
            'allocations' => 'required|array',
            'allocations.*.placement_id' => 'required|exists:ipo_account_placements,id',
            'allocations.*.lot_allocated' => 'required|integer|min:0',
        ]);

        foreach ($request->allocations as $data) {
            $placement = IpoAccountPlacement::with('fundings', 'ipo', 'mitraAccount')->find($data['placement_id']);
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

            // CREATE REFUND TRANSACTIONS
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
        
        return redirect()->route('ipos.show', $ipo)->with('success', 'Seluruh hasil penjatahan berhasil disimpan. Sisa modal otomatis dikembalikan ke saldo investor.');
    }

}
