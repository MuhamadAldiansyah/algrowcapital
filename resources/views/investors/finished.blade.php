@extends('layouts.app')

@section('title', 'IPO Selesai: ' . $investor->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Riwayat IPO Selesai</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('investors.index') }}">Investor</a></li>
                <li class="breadcrumb-item active">{{ $investor->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('investors.export-finished', $investor) }}" class="btn btn-success btn-sm shadow-sm">
            <i class="fa-solid fa-file-excel me-1"></i> Export Excel
        </a>
        <a href="{{ route('investors.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Total Profit Summary Card -->
<div class="card border-0 shadow-sm mb-4 bg-success bg-opacity-10">
    <div class="card-body p-4 text-center">
        <small class="text-muted text-uppercase fw-bold ls-1 d-block mb-2">Total Keuntungan Seluruh IPO (Investor Share)</small>
        <h2 class="fw-bold text-success-custom mb-0">Rp {{ number_format($investor->total_profit, 0, ',', '.') }}</h2>
        <p class="small text-muted mb-0 mt-2">Total akumulasi profit dari semua emiten yang telah terealisasi jual.</p>
    </div>
</div>

<div class="row">

    @forelse($fundings as $ipoId => $ipoFundings)
    @php
        $firstFunding = $ipoFundings->first();
        $ipo = $firstFunding->placement->ipo;
        
        // Sum totals for the group
        $totalCapitalUsed = 0;
        $totalGrossProfit = 0;
        $totalNetProfit = 0;
        
        foreach($ipoFundings as $funding) {
            $placement = $funding->placement;
            $allocation = $placement->allocation;
            $sale = $placement->sale;
            
            $investorRatio = $funding->amount_funded / $placement->capital_allocated;
            $totalCapitalUsed += ($allocation ? $allocation->total_used * $investorRatio : 0);
            
            $investorGrossProfit = ($sale ? $sale->net_profit : 0) * $investorRatio;
            $totalGrossProfit += $investorGrossProfit;
            
            // Calculate actual net based on share_pct
            $investorNetProfit = $investorGrossProfit * ($funding->share_pct / 100);
            $totalNetProfit += $investorNetProfit;
        }
        
        $totalMitraShare = $totalGrossProfit - $totalNetProfit;
    @endphp
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-custom rounded p-2 me-3 text-center" style="min-width: 70px;">
                        <span class="d-block fw-bold small text-uppercase">Ticker</span>
                        <h5 class="mb-0 fw-bold">{{ $ipo->code }}</h5>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $ipo->name }}</h5>
                        <small class="text-white-50">Selesai • {{ $ipoFundings->count() }} Akun Berpartisipasi</small>
                    </div>
                </div>
                <span class="badge bg-success rounded-pill px-3">Riwayat Selesai</span>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <!-- Left Side: Summary -->
                    <div class="col-md-5 p-4 border-end">
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Total Modal Terpakai</small>
                                    <span class="fw-bold text-dark">Rp {{ number_format($totalCapitalUsed, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-primary bg-opacity-10 rounded text-center">
                                    <small class="text-primary-custom d-block mb-1">Total Profit Kotor</small>
                                    <span class="fw-bold text-primary-custom">Rp {{ number_format($totalGrossProfit, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-muted small text-uppercase mb-3">Ringkasan Pembagian</h6>
                        <div class="list-group list-group-flush border rounded overflow-hidden">
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <small>Total Porsi Mitra</small>
                                <span class="text-danger fw-bold small">- Rp {{ number_format($totalMitraShare, 0, ',', '.') }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-success bg-opacity-10">
                                <div>
                                    <i class="fa-solid fa-star text-success-custom me-2"></i>
                                    <span class="fw-bold">Total Profit Bersih</span>
                                </div>
                                <span class="text-success-custom fw-bold fs-4">Rp {{ number_format($totalNetProfit, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Details per account -->
                    <div class="col-md-7 p-4 bg-light bg-opacity-50">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3">Detail Partisipasi Akun</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Akun</th>
                                        <th>Platform</th>
                                        <th class="text-end">Modal</th>
                                        <th class="text-end">Share %</th>
                                        <th class="text-end">Profit Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ipoFundings as $f)
                                    @php
                                        $f_placement = $f->placement;
                                        $f_ratio = $f->amount_funded / $f_placement->capital_allocated;
                                        $f_capital = ($f_placement->allocation ? $f_placement->allocation->total_used * $f_ratio : 0);
                                        $f_gross = ($f_placement->sale ? $f_placement->sale->net_profit : 0) * $f_ratio;
                                        $f_net = $f_gross * ($f->share_pct / 100);
                                    @endphp
                                    <tr>
                                        <td>{{ $f_placement->mitraAccount->owner_name }}</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary small">{{ $f_placement->mitraAccount->platform }}</span></td>
                                        <td class="text-end small">Rp {{ number_format($f_capital, 0, ',', '.') }}</td>
                                        <td class="text-end small"><span class="badge bg-primary bg-opacity-10 text-primary">{{ number_format($f->share_pct, 0) }}%</span></td>
                                        <td class="text-end fw-bold text-success-custom">Rp {{ number_format($f_net, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm py-5 text-center">
            <div class="mb-3">
                <i class="fa-solid fa-folder-open fs-1 text-muted opacity-25"></i>
            </div>
            <h5 class="text-muted">Belum ada IPO yang berstatus selesai untuk investor ini.</h5>
        </div>
    </div>
    @endforelse
</div>
@endsection
