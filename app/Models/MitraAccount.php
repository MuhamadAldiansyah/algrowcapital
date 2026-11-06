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
        'phone',
    ];

    public function getPhoneAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->user ? $this->user->phone : null;
    }

    protected $hidden = ['password', 'pin'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

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

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
