<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class MitraAccount extends Model
{
    use Tenantable;
    protected $fillable = [
        'mitra_group_id',
        'owner_name',
        'handler_name',
        'platform',
        'username',
        'password',
        'pin',
        'nik',
        'bank_rdn',
        'rdn_account',
        'personal_bank',
        'status',
        'ticker_saham',
        'device',
    ];

    protected $hidden = ['password', 'pin'];

    public function group()
    {
        return $this->belongsTo(MitraGroup::class, 'mitra_group_id');
    }

    public function placements()
    {
        return $this->hasMany(IpoAccountPlacement::class);
    }

    public function fundings()
    {
        return $this->hasMany(Funding::class);
    }
}
