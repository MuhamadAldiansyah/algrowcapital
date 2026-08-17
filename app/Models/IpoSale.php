<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpoSale extends Model
{
    protected $fillable = ['ipo_id', 'ipo_account_placement_id', 'sell_price', 'total_return', 'net_profit'];

    public function ipo()
    {
        return $this->belongsTo(Ipo::class);
    }

    public function placement()
    {
        return $this->belongsTo(IpoAccountPlacement::class, 'ipo_account_placement_id');
    }
}
