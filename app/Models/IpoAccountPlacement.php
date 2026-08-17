<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpoAccountPlacement extends Model
{
    protected $fillable = ['ipo_id', 'mitra_account_id', 'capital_allocated', 'est_lot', 'mitra_share_pct'];

    public function ipo()
    {
        return $this->belongsTo(Ipo::class);
    }

    public function mitraAccount()
    {
        return $this->belongsTo(MitraAccount::class);
    }

    public function fundings()
    {
        return $this->hasMany(InvestorFunding::class, 'ipo_account_placement_id');
    }

    public function allocation()
    {
        return $this->hasOne(IpoAllocation::class);
    }

    public function sale()
    {
        return $this->hasOne(IpoSale::class, 'ipo_account_placement_id');
    }
}
