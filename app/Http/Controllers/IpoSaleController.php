<?php

namespace App\Http\Controllers;

use App\Models\IpoSale;
use App\Models\Ipo;
use App\Models\IpoAllocation;
use Illuminate\Http\Request;

class IpoSaleController extends Controller
{
    /**
     * Show form to input sell price for an IPO
     */
    public function create(Ipo $ipo)
    {
        if (!$ipo->canEdit()) {
            return redirect()->route('ipos.show', $ipo)->with('error', 'Data penjualan tidak dapat diubah karena IPO sudah selesai.');
        }
        // Only show accounts that have an allocation (got the shares)
        $placements = $ipo->placements()->whereHas('allocation')->with('mitraAccount', 'allocation', 'sale')->get();
        return view('ipo-sales.bulk', compact('ipo', 'placements'));
    }

    /**
     * Store bulk sell prices and calculate profit per account
     */
    public function store(Request $request, Ipo $ipo)
    {
        if (!$ipo->canEdit()) {
            return redirect()->route('ipos.show', $ipo)->with('error', 'Data penjualan tidak dapat diubah karena IPO sudah selesai.');
        }

        $request->validate([
            'allocations' => 'required|array',
            'allocations.*.placement_id' => 'required|exists:ipo_account_placements,id',
            'allocations.*.sell_price' => 'required|numeric|min:0',
        ]);

        foreach ($request->allocations as $data) {
            $placement = \App\Models\IpoAccountPlacement::with('allocation', 'fundings')->find($data['placement_id']);
            
            if ($placement->allocation) {
                $totalReturn = $placement->allocation->lot_allocated * 100 * $data['sell_price'];
                $taxFee = $totalReturn * 0.0025; // 0.25% transaction tax/fee
                $netProfit = $totalReturn - $placement->allocation->total_used - $taxFee;

                IpoSale::updateOrCreate(
                    ['ipo_account_placement_id' => $placement->id],
                    [
                        'ipo_id' => $ipo->id,
                        'sell_price' => $data['sell_price'],
                        'total_return' => $totalReturn,
                        'net_profit' => $netProfit
                    ]
                );

            }
        }

        return redirect()->route('ipos.show', $ipo->id)->with('success', 'Harga jual per akun disimpan. IPO sekarang masuk ke tahap Menunggu Distribusi Profit.');
    }
}
