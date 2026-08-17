<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Tenantable;

class Ipo extends Model
{
    use Tenantable;
    protected $fillable = ['name', 'code', 'price', 'ipo_date', 'image_path', 'profit_distributed_at', 'mitra_fee_pct', 'platform_fee_pct'];

    public function placements()
    {
        return $this->hasMany(IpoAccountPlacement::class);
    }

    public function sales()
    {
        return $this->hasMany(IpoSale::class);
    }

    /**
     * Determine the current workflow step (1-4)
     */
    public function getStepAttribute()
    {
        $totalPlacements = $this->placements()->count();
        
        // 1. Pesan Saham (Placement)
        if ($totalPlacements === 0) {
            return 1; 
        }
        
        // Step 1 only completes if ALL selected mitras have been funded (capital > 0)
        $completedPlacements = $this->placements()->where('capital_allocated', '>', 0)->count();
        if ($completedPlacements < $totalPlacements) {
            return 1;
        }

        $totalAllocations = $this->placements()->has('allocation')->count();
        if ($totalAllocations < $totalPlacements) {
            return 2; // Tunggu Penjatahan (Allotment)
        }

        $allocationsWithLots = $this->placements()->whereHas('allocation', function($q) {
            $q->where('lot_allocated', '>', 0);
        })->count();

        $totalSales = $this->placements()->has('sale')->count();
        
        // Hanya mengharapkan penjualan untuk akun yang mendapatkan jatah lot
        if ($totalSales < $allocationsWithLots) {
            return 3; // Siap Jual (Sale)
        }

        if (is_null($this->profit_distributed_at)) {
            return 4; // Menunggu Distribusi Profit
        }

        return 5; // Selesai
    }

    /**
     * Get a human-readable status label
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->step) {
            1 => 'Pesan Saham',
            2 => 'Isi Penjatahan',
            3 => 'Realisasi Jual',
            4 => 'Menunggu Distribusi',
            5 => 'Selesai',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->step) {
            1 => 'primary',
            2 => 'warning',
            3 => 'info',
            4 => 'dark',
            5 => 'success',
            default => 'secondary',
        };
    }

    /**
     * Check if the IPO can still be edited
     */
    public function canEdit(): bool
    {
        return true;
    }

    public function canDelete(): bool
    {
        return true;
    }
}
