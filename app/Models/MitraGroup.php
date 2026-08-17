<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class MitraGroup extends Model
{
    use Tenantable;
    protected $fillable = ['name', 'handler_name'];

    public function accounts()
    {
        return $this->hasMany(MitraAccount::class);
    }
}
