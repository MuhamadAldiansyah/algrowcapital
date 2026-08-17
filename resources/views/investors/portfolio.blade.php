@extends('layouts.app')

@section('title', 'Partisipasi Emiten - ' . $investor->name)

@section('content')
<div class="d-flex flex-column mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="fw-bold text-white ticker-font mb-0">
            <i class="fa-solid fa-layer-group me-2 text-emerald-500"></i> PARTISIPASI EMITEN ({{ strtoupper($investor->name) }})
        </h4>
    </div>
    @if($investor->tenant)
        <div class="mt-2">
            <span class="badge bg-emerald-900 border border-emerald-500 text-emerald-100 py-2 px-3 shadow-sm rounded-pill" style="font-size: 0.8rem;">
                <i class="fa-solid fa-building me-1 text-emerald-400"></i> Tergabung Dengan: {{ $investor->tenant->name }}
            </span>
        </div>
    @endif
</div>

@if(empty($portfolioData))
<div class="card stat-node border-0 shadow-lg text-center py-5 bg-black bg-opacity-40 border-emerald-900 border">
    <div class="card-body">
        <i class="fa-solid fa-box-open d-block mb-3 fs-1 text-emerald-500 opacity-50"></i>
        <h5 class="text-emerald-500 opacity-75">Belum ada partisipasi di Emiten manapun.</h5>
        <p class="text-emerald-100 opacity-50 small mt-2">Anda belum memodali akun mitra di IPO apapun.</p>
    </div>
</div>
@else
<div class="row g-3 g-md-4 mb-4">
    <!-- Total Modal Terpakai -->
    <div class="col-12 col-md-6">
        <div class="card stat-node h-100 border-0 shadow-lg glow-bg-warning hover-translate transition-all" style="border-left: 4px solid #f59e0b !important;">
            <div class="card-body p-3 p-md-4 position-relative overflow-hidden">
                <i class="fa-solid fa-money-check-dollar position-absolute opacity-10" style="font-size: 5rem; right: -10px; bottom: -10px; color: #f59e0b;"></i>
                <div class="small text-warning fw-bold mb-1 opacity-75 d-flex align-items-center gap-2">
                    TOTAL MODAL DITEMPATKAN
                </div>
                <h4 class="fw-bold text-white mb-0 ticker-font text-break">Rp {{ number_format($grandTotalModal, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    
    <!-- Total Profit Bersih Investor -->
    <div class="col-12 col-md-6">
        <div class="card stat-node h-100 border-0 shadow-lg glow-bg-success hover-translate transition-all" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body p-3 p-md-4 position-relative overflow-hidden">
                <i class="fa-solid fa-wallet position-absolute opacity-10" style="font-size: 5rem; right: -10px; bottom: -10px; color: #10b981;"></i>
                <div class="small text-success-custom fw-bold mb-1 opacity-75 d-flex align-items-center gap-2">
                    @if(isset($isOwner) && $isOwner)
                        TOTAL PROFIT ANDA + FEE PLATFORM
                    @else
                        TOTAL PROFIT BERSIH ANDA
                    @endif
                </div>
                <h4 class="fw-bold text-white mb-0 ticker-font text-break">
                    @if(isset($isOwner) && $isOwner)
                        Rp {{ number_format($grandTotalProfitBersih + $grandTotalFeePlatform, 0, ',', '.') }}
                    @else
                        Rp {{ number_format($grandTotalProfitBersih, 0, ',', '.') }}
                    @endif
                </h4>
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
                        <th class="text-center">AKUN MITRA</th>
                        <th>MODAL DITEMPATKAN</th>
                        <th>PROFIT KOTOR</th>
                        <th>PORSI MITRA (50%)</th>
                        @if(isset($isOwner) && $isOwner)
                        <th>FEE DARI LUAR (25%)</th>
                        <th class="pe-4">PROFIT TOTAL ANDA</th>
                        @else
                        <th class="pe-4">PROFIT ANDA</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($portfolioData as $data)
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
                            <span class="badge bg-black bg-opacity-40 text-emerald-500 border border-emerald-900">{{ $data['total_mitra'] }} Akun</span>
                        </td>
                        <td class="ticker-font text-white opacity-75">
                            Rp {{ number_format($data['modal_terpakai'], 0, ',', '.') }}
                        </td>
                        <td class="ticker-font text-white opacity-75">
                            Rp {{ number_format($data['profit_kotor'], 0, ',', '.') }}
                        </td>
                        <td class="ticker-font text-danger opacity-75">
                            Rp {{ number_format($data['porsi_mitra'], 0, ',', '.') }}
                        </td>
                        @if(isset($isOwner) && $isOwner)
                        <td class="ticker-font text-info opacity-75">
                            Rp {{ number_format($data['fee_platform'], 0, ',', '.') }}
                        </td>
                        <td class="ticker-font fw-bold text-success-custom pe-4">
                            Rp {{ number_format($data['profit_bersih'] + $data['fee_platform'], 0, ',', '.') }}
                        </td>
                        @else
                        <td class="ticker-font fw-bold text-success-custom pe-4">
                            Rp {{ number_format($data['profit_bersih'], 0, ',', '.') }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .glow-bg-success { box-shadow: 0 0 15px rgba(16, 185, 129, 0.2) !important; }
    .glow-bg-warning { box-shadow: 0 0 15px rgba(245, 158, 11, 0.2) !important; }
    .news-hover { transition: all 0.2s; }
    .news-hover:hover { background-color: rgba(16, 185, 129, 0.05); }
    .text-success-custom { color: #34d399 !important; }
</style>
@endif
@endsection
