<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestorTransaction extends Model
{
    protected $fillable = ['investor_id', 'amount', 'type', 'description'];

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }
}
