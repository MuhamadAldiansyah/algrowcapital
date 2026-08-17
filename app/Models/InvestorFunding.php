<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestorFunding extends Model
{
    protected $fillable = [
        'investor_id',
        'ipo_account_placement_id',
        'amount_funded',
        'share_pct',
    ];

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    public function placement()
    {
        return $this->belongsTo(IpoAccountPlacement::class, 'ipo_account_placement_id');
    }
}
