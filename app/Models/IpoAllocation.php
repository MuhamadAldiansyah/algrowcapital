<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpoAllocation extends Model
{
    protected $fillable = [
        'ipo_account_placement_id', 'lot_allocated', 'final_price_ipo',
        'total_used', 'remaining_capital'
    ];

    public function placement()
    {
        return $this->belongsTo(IpoAccountPlacement::class, 'ipo_account_placement_id');
    }
}
