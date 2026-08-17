<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class Investor extends Model
{
    use Tenantable;
    protected $fillable = ['name', 'user_id'];

    public function fundings()
    {
        return $this->hasMany(InvestorFunding::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transactions()
    {
        return $this->hasMany(InvestorTransaction::class);
    }

    /**
     * Total Capital = Total Deposits - Total Withdrawals (Wallet Size)
     */
    public function getTotalCapitalAttribute()
    {
        $deposits = $this->transactions()->where('type', 'DEPOSIT')->sum('amount');
        $profits = $this->transactions()->where('type', 'PROFIT')->sum('amount');
        $withdrawals = $this->transactions()->where('type', 'WITHDRAW')->sum('amount');
        return ($deposits + $profits) - $withdrawals;
    }

    /**
     * Active Deployment = Sum of capital currently locked in active (non-finished) IPOs
     */
    public function getActiveDeploymentAttribute()
    {
        $totalDeducted = 0;
        foreach ($this->fundings()->with('placement.ipo', 'placement.allocation')->get() as $funding) {
            // Only count if IPO is NOT yet finished
            if ($funding->placement->ipo && $funding->placement->ipo->step < 4) {
                if ($funding->placement->allocation && $funding->placement->capital_allocated > 0) {
                    $ratio = $funding->amount_funded / $funding->placement->capital_allocated;
                    $totalDeducted += $funding->placement->allocation->total_used * $ratio;
                } else {
                    $totalDeducted += $funding->amount_funded;
                }
            }
        }
        return $totalDeducted;
    }

    /**
     * Available Balance = Wallet Size - Active Deployment
     */
    public function getAvailableBalanceAttribute()
    {
        return $this->total_capital - $this->active_deployment;
    }

    /**
     * Total Profit earned by the investor
     */
    public function getTotalProfitAttribute()
    {
        return $this->transactions()->where('type', 'PROFIT')->sum('amount');
    }

    /**
     * Total Gross Deposit (Modal Disetor)
     */
    public function getTotalDepositAttribute()
    {
        return $this->transactions()->where('type', 'DEPOSIT')->sum('amount');
    }
}

