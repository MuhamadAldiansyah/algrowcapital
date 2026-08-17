@extends('layouts.app')

@section('title', 'Pembagian Profit Saya')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
    <div>
        <p class="text-emerald-400 opacity-75 small mb-0">Rincian bagi hasil dari seluruh akun yang ditugaskan kepada Anda.</p>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">
    <!-- Total Profit Keseluruhan dari Akun yang dihandle -->
    <div class="col-12 col-md-6">
        <div class="card stat-node h-100 border-0 shadow-lg glow-bg-success hover-translate transition-all" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body p-3 p-md-4 position-relative overflow-hidden">
                <i class="fa-solid fa-money-bill-trend-up position-absolute opacity-10" style="font-size: 5rem; right: -10px; bottom: -10px; color: #10b981;"></i>
                <div class="small text-white fw-bold mb-1 opacity-75 d-flex align-items-center gap-2">
                    TOTAL PROFIT KOTOR (AKUN SAYA)
                </div>
                <h4 class="fw-bold text-white mb-0 ticker-font text-break">Rp {{ number_format($grandTotalProfit, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    
    <!-- Porsi Mitra (50%) -->
    <div class="col-12 col-md-6">
        <div class="card stat-node h-100 border-0 shadow-lg glow-bg-primary hover-translate transition-all" style="border-left: 4px solid #3b82f6 !important;">
            <div class="card-body p-3 p-md-4 position-relative overflow-hidden">
                <i class="fa-solid fa-wallet position-absolute opacity-10" style="font-size: 5rem; right: -10px; bottom: -10px; color: #3b82f6;"></i>
                <div class="small text-primary fw-bold mb-1 opacity-75 d-flex align-items-center gap-2">
                    TOTAL HAK PROFIT SAYA (50%)
                </div>
                <h4 class="fw-bold text-white mb-0 ticker-font text-break">Rp {{ number_format($grandMitraProfit, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card stat-node border-0 shadow-lg mb-4">
    <div class="card-header bg-black bg-opacity-20 border-bottom border-emerald-900 pt-4 px-4 pb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-white ticker-font mb-0">
                <i class="fa-solid fa-list text-emerald-500 me-2"></i>RINCIAN PER EMITEN
            </h5>
            <div class="d-block d-md-none text-emerald-400 small opacity-75">
                <i class="fa-solid fa-arrows-left-right me-1"></i> Geser Tabel
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle text-white mb-0 text-nowrap">
                <thead class="bg-black bg-opacity-40">
                    <tr>
                        <th class="ps-4">EMITEN</th>
                        <th class="text-center">AKUN</th>
                        <th>PROFIT KOTOR (AKUN SAYA)</th>
                        <th class="pe-4">HAK PROFIT SAYA (50%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distributions as $data)
                    <tr class="news-hover">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($data['ipo']->image_path)
                                    <img src="{{ Storage::url($data['ipo']->image_path) }}" alt="{{ $data['ipo']->code }}" class="rounded bg-white p-1" style="width: 40px; height: 40px; object-fit: contain;">
                                @else
                                    <div class="rounded bg-emerald-900 d-flex align-items-center justify-content-center text-emerald-400 fw-bold" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        {{ substr($data['ipo']->code, 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="fw-bold text-white d-block ticker-font">{{ $data['ipo']->code }}</span>
                                    <small class="text-emerald-500 opacity-50">{{ $data['ipo']->name }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-black bg-opacity-40 text-emerald-500 border border-emerald-900">{{ $data['total_accounts'] }} Akun</span>
                        </td>
                        <td class="ticker-font text-white opacity-75">
                            Rp {{ number_format($data['total_profit'], 0, ',', '.') }}
                        </td>
                        <td class="ticker-font fw-bold text-primary pe-4">
                            Rp {{ number_format($data['mitra_profit'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-white opacity-50">
                            <i class="fa-solid fa-folder-open fs-1 d-block mb-3 opacity-50"></i>
                            Belum ada IPO yang menghasilkan profit penjualan untuk akun Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .glow-bg-success { box-shadow: 0 0 15px rgba(16, 185, 129, 0.2) !important; }
    .glow-bg-primary { box-shadow: 0 0 15px rgba(59, 130, 246, 0.2) !important; }
    .news-hover { transition: all 0.2s; }
    .news-hover:hover { background-color: rgba(16, 185, 129, 0.05); }
</style>
@endsection
