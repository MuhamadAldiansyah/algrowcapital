@extends('layouts.app')

@section('title', 'Laporan Arus Dana Emiten')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
    <div>
        <p class="text-emerald-400 opacity-75 small mb-0">Pantau perputaran arus dana (uang keluar dan masuk) untuk setiap project IPO Anda.</p>
    </div>
    <a href="{{ route('ipos.export-report') }}" class="btn btn-primary-custom rounded-pill shadow-lg fw-bold px-4">
        <i class="fa-solid fa-file-excel me-2"></i>EKSPOR LAPORAN
    </a>
</div>

<div class="card stat-node border-0 shadow-lg mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">EMITEN / Ticker</th>
                        <th>STATUS</th>
                        <th>MODAL AWAL INVESTOR</th>
                        <th>MODAL TERPAKAI</th>
                        <th>HASIL PENJUALAN (MASUK)</th>
                        <th>NET PROFIT</th>
                        <th class="text-center pe-4">STATUS DANA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $data)
                        @php
                            $ipo = $data->ipo;
                            $totalCapital = $data->total_capital;
                            $totalUsed = $data->total_used;
                            $totalSales = $data->total_sales;
                            $totalProfit = $data->total_profit;
                            $totalInvestorProfit = $data->total_investor_profit;
                            
                            $isComplete = ($ipo->profit_distributed_at !== null);
                        @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($ipo->image_path)
                                    <img src="{{ Storage::url($ipo->image_path) }}" alt="{{ $ipo->code }}" class="rounded bg-white p-1" style="width: 40px; height: 40px; object-fit: contain;">
                                @else
                                    <div class="rounded bg-emerald-900 d-flex align-items-center justify-content-center text-emerald-400 fw-bold" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        {{ substr($ipo->code, 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="fw-bold text-white d-block ticker-font">{{ $ipo->code }}</span>
                                    <small class="text-emerald-500 opacity-50">{{ $ipo->name }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $ipo->status_color }} bg-opacity-20 text-white border border-{{ $ipo->status_color }} border-opacity-25 px-2 py-1" style="font-size: 0.7rem;">
                                {{ strtoupper($ipo->status_label) }}
                            </span>
                        </td>
                        <td class="ticker-font">
                            @if($totalCapital > 0)
                                <span class="text-info">Rp {{ number_format($totalCapital, 0, ',', '.') }}</span>
                            @else
                                <span class="text-emerald-500 opacity-50">-</span>
                            @endif
                        </td>
                        <td class="ticker-font">
                            @if($totalUsed > 0)
                                <span class="text-danger">Rp {{ number_format($totalUsed, 0, ',', '.') }}</span>
                            @else
                                <span class="text-emerald-500 opacity-50">-</span>
                            @endif
                        </td>
                        <td class="ticker-font">
                            @if($totalSales > 0)
                                <span class="text-success-custom">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
                            @else
                                <span class="text-emerald-500 opacity-50">-</span>
                            @endif
                        </td>
                        <td class="ticker-font fw-bold text-emerald-400">
                            @if($totalProfit != 0)
                                Rp {{ number_format($totalProfit, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            @if($ipo->step < 3)
                                <span class="badge bg-black bg-opacity-40 text-white border border-emerald-900">MENUNGGU PENJUALAN</span>
                            @elseif($isComplete)
                                <span class="badge bg-success bg-opacity-20 text-white border border-success border-opacity-50"><i class="fa-solid fa-check-circle me-1"></i> CLEAR / SELESAI</span>
                                <div class="x-small text-white opacity-50 ticker-font mt-1">Profit Didistribusikan</div>
                            @else
                                <span class="badge bg-warning bg-opacity-20 text-white border border-warning border-opacity-50 mb-1">BELUM SELESAI</span>
                                <div class="x-small text-white opacity-75 ticker-font">Menunggu Distribusi Profit</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa-solid fa-folder-open fs-1 mb-3 text-emerald-500 opacity-20"></i>
                            <h5 class="text-white">Belum ada data emiten.</h5>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .news-hover { transition: background-color 0.2s; }
    .news-hover:hover { background-color: rgba(16, 185, 129, 0.05); }
    .x-small { font-size: 0.7rem; }
</style>
@endsection
