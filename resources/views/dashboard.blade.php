@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');
    
    .dashboard-cockpit {
        --emerald-glow: #10b981;
        --emerald-dark: #071f11;
        --emerald-border: rgba(16, 185, 129, 0.2);
    }
    
    .stat-node {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border: 1px solid var(--emerald-border);
        border-radius: 16px;
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .stat-node:hover {
        border-color: var(--emerald-glow);
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.1);
        transform: translateY(-2px);
    }

    .ticker-font { font-family: 'JetBrains Mono', monospace; letter-spacing: -0.5px; }
    
    .glow-text-emerald {
        color: var(--emerald-glow);
        text-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
    }

    .welcome-banner {
        background: linear-gradient(135deg, #071f11 0%, #064e3b 100%);
        border: 1px solid var(--emerald-border);
        position: relative;
        overflow: hidden;
    }

    .widget-container {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 12px;
        border: 1px solid rgba(16, 185, 129, 0.1);
    }

    .leaderboard-table { color: #ecfdf5; background: transparent !important; }
    .leaderboard-table thead th { 
        background: rgba(0,0,0,0.3) !important; 
        color: #34d399; 
        border: none; 
        font-size: 0.65rem; 
        letter-spacing: 1px;
        padding: 12px;
    }
    .leaderboard-table tbody tr { 
        border-bottom: 1px solid rgba(16, 185, 129, 0.05); 
        background: transparent !important;
    }
    .leaderboard-table tbody tr:hover { background: rgba(16, 185, 129, 0.05) !important; }
</style>

<div class="dashboard-cockpit">
    <div class="row g-3 mb-4">
        @if(Auth::user()->role !== 'user')
        <!-- TOTAL STATS (Admin Only) -->
        <div class="col-12 col-md-4">
            <div class="stat-node p-4 border-start border-4 border-emerald-500">
                <div class="text-emerald-500 x-small fw-bold mb-2" style="letter-spacing: 1.5px;">COMPLETED IPOS</div>
                <h3 class="fw-bold ticker-font text-white mb-0">{{ $completedIposData->count() }} <span class="fs-6 opacity-50">EVENTS</span></h3>
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="fa-solid fa-flag-checkered fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-node p-4">
                <div class="text-emerald-500 x-small fw-bold mb-2" style="letter-spacing: 1.5px;">ACTIVE NODES</div>
                <h3 class="fw-bold ticker-font text-white mb-0">{{ $totalAkun }} <span class="fs-6 opacity-50">ACCOUNTS</span></h3>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-node p-4">
                <div class="text-emerald-500 x-small fw-bold mb-2" style="letter-spacing: 1.5px;">LIVE IPO EVENTS</div>
                <h3 class="fw-bold ticker-font text-white mb-0">{{ $totalIpo }} <span class="fs-6 opacity-50">LISTINGS</span></h3>
            </div>
        </div>
        @else
        <!-- WELCOME CARD (User Only) -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background-color: #071f11; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold text-white mb-1">Halo, {{ Auth::user()->name }} 👋</h4>
                        @if(Auth::user()->tenant)
                            <div class="text-emerald-500 opacity-75 small fw-bold mt-2"><i class="fa-solid fa-building me-1"></i> {{ Auth::user()->tenant->name }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="d-inline-flex align-items-center bg-black bg-opacity-40 rounded-pill px-3 py-2 border border-emerald-900 border-opacity-50">
                            <span class="bg-emerald-500 rounded-circle me-2" style="width: 8px; height: 8px; box-shadow: 0 0 8px #10b981;"></span>
                            <span class="text-emerald-400 small fw-bold">Sistem Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row mt-2">
        <!-- MAIN CONTENT COLUMN -->
        <div class="col-12">
            @if(in_array(Auth::user()->role, ['owner', 'developer']))
            <!-- Investor Stats (Admin Only) -->
            <div class="stat-node mb-4 overflow-hidden">
                <div class="p-4 border-bottom border-white border-opacity-10 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-emerald-400"><i class="fa-solid fa-trophy me-2"></i>INSTITUTIONAL LEADERBOARD</h6>
                    <span class="badge bg-black bg-opacity-30 text-emerald-500 x-small fw-bold">TOP 5 PERFORMANCE</span>
                </div>
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table leaderboard-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">INVESTOR</th>
                                    <th>PROFIT (RP)</th>
                                    <th class="text-emerald-400">ROI %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($investorData->take(5) as $data)
                                <tr>
                                    <td class="fw-bold ticker-font ps-4">{{ $data['name'] }}</td>
                                    <td class="glow-text-emerald fw-bold ticker-font">Rp {{ number_format($data['profit'], 0, ',', '.') }}</td>
                                    <td>
                                        <div class="fw-bold ticker-font text-emerald-400" style="font-size: 0.85rem;">
                                            {{ ($data['capital'] > 0 && $data['profit'] > 0) ? '+' : '' }}{{ number_format($data['capital'] > 0 ? ($data['profit'] / $data['capital']) * 100 : 0, 1) }}%
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Profit Distribution per IPO (Accordion) -->
            <div class="stat-node mb-4">
                <div class="p-4 border-bottom border-white border-opacity-10 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-emerald-400 x-small text-uppercase tracking-widest"><i class="fa-solid fa-layer-group me-2"></i>LAPORAN PROFIT PER EMITEN (SELESAI)</h6>
                </div>
                <div class="p-4">
                    <div class="accordion" id="accordionIpoProfits" style="--bs-accordion-bg: transparent; --bs-accordion-border-color: rgba(16, 185, 129, 0.2);">
                        @forelse($completedIposData as $index => $ipoData)
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed text-white fw-bold d-flex align-items-center gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIpo{{ $index }}" style="background: rgba(0,0,0,0.2); box-shadow: none;">
                                    @if($ipoData['image_path'])
                                        <img src="{{ Storage::url($ipoData['image_path']) }}" alt="{{ $ipoData['code'] }}" class="rounded bg-white p-1" style="width: 40px; height: 40px; object-fit: contain;">
                                    @else
                                        <div class="rounded bg-emerald-900 d-flex align-items-center justify-content-center text-emerald-400 fw-bold" style="width: 40px; height: 40px; flex-shrink: 0;">
                                            {{ substr($ipoData['code'], 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <span class="ticker-font d-block">{{ $ipoData['code'] }}</span>
                                        <small class="text-emerald-500 opacity-75 fw-normal">{{ $ipoData['name'] }}</small>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseIpo{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#accordionIpoProfits">
                                <div class="accordion-body px-4 py-4" style="background: rgba(16, 185, 129, 0.05);">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="p-3 bg-black bg-opacity-30 rounded border border-emerald-900 text-center">
                                                <small class="d-block text-emerald-500 opacity-75 mb-1" style="font-size: 0.7rem;">TOTAL GROSS PROFIT</small>
                                                <div class="fw-bold ticker-font text-white fs-5">Rp {{ number_format($ipoData['gross_profit'], 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-black bg-opacity-30 rounded border border-emerald-900 text-center">
                                                <small class="d-block text-emerald-500 opacity-75 mb-1" style="font-size: 0.7rem;">PORSI MITRA</small>
                                                <div class="fw-bold ticker-font text-warning fs-5">Rp {{ number_format($ipoData['mitra_share'], 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 bg-black bg-opacity-30 rounded border border-emerald-900 border-opacity-50 text-center position-relative overflow-hidden">
                                                <div class="position-absolute w-100 h-100 top-0 start-0 bg-emerald-900 opacity-20"></div>
                                                <small class="d-block text-emerald-400 mb-1 position-relative" style="font-size: 0.7rem;">NET PROFIT INVESTOR</small>
                                                <div class="fw-bold ticker-font text-emerald-400 fs-5 position-relative">Rp {{ number_format($ipoData['net_profit'], 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-emerald-500 opacity-50">
                            <i class="fa-solid fa-folder-open mb-2 fs-3"></i>
                            <p class="mb-0 small">Belum ada IPO yang selesai (Step 4).</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Watchlist (Public) -->
            <div class="stat-node mb-4">
                <div class="p-3 border-bottom border-white border-opacity-10">
                    <h6 class="fw-bold mb-0 text-emerald-400 x-small"><i class="fa-solid fa-list-check me-2"></i>WATCHLIST & MARKET LIVE</h6>
                </div>
                <div class="p-0" style="height: 450px;">
                    <div class="tradingview-widget-container" style="height: 100%; width: 100%;">
                        <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-market-overview.js" async>
                        {
                            "colorTheme": "dark", "dateRange": "12M", "showChart": true, "locale": "id", "width": "100%", "height": "100%", "isTransparent": true, "showSymbolLogo": true,
                            "tabs": [
                                {
                                    "title": "Watchlist",
                                    "symbols": {!! json_encode(array_map(function($t) { return ["s" => $t['proName']]; }, $activeIpoTickers->toArray())) !!}
                                }
                            ]
                        }
                        </script>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>
</div>

@if(Auth::user()->role === 'user' && empty(Auth::user()->phone))
<div class="modal fade" id="mandatoryPhoneModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="mandatoryPhoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden" style="background: rgba(5, 22, 12, 0.95); backdrop-filter: blur(16px); box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 0 1px rgba(16, 185, 129, 0.2), 0 0 30px rgba(16, 185, 129, 0.1); border-radius: 20px;">
            <div class="modal-header border-0 pb-0 position-relative" style="background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.15) 0%, transparent 70%);">
                <div>
                    <h5 class="modal-title text-white fw-bold ticker-font mb-1" id="mandatoryPhoneModalLabel">
                        <i class="fa-solid fa-phone text-emerald-400 me-2"></i>LENGKAPI PROFIL ANDA
                    </h5>
                    <p class="small text-emerald-500 opacity-75 mb-0">Nomor WhatsApp diperlukan untuk keperluan komunikasi.</p>
                </div>
            </div>
            <form action="{{ route('my-profile.update-phone') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 fw-bold small text-uppercase tracking-wide">Nomor Telepon / WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text bg-black bg-opacity-40 border-emerald-900 border-end-0 text-emerald-500 border-opacity-50">
                                <i class="fa-solid fa-address-book"></i>
                            </span>
                            <input type="text" name="phone" class="form-control bg-black bg-opacity-40 border-emerald-900 border-start-0 text-white shadow-none px-3 py-2 ticker-font fs-6" required placeholder="Contoh: 081234567890">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary-custom px-4 rounded-pill shadow-lg w-100">
                        <i class="fa-solid fa-save me-2"></i> SIMPAN NOMOR
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var myModal = new bootstrap.Modal(document.getElementById('mandatoryPhoneModal'));
        myModal.show();
    });
</script>
@endif


@endsection

@if(Auth::user()->role !== 'user')
@section('scripts')
<script>
    // Custom scripts if any
</script>
@endsection
@endif
