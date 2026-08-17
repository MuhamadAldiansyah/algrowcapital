@extends('layouts.app')

@section('title', 'Pembagian Profit')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
    <div>
        <p class="text-emerald-400 opacity-75 small mb-0">Laporan pembagian profit secara transparan berdasarkan asal modal (Owner vs Investor).</p>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">
    <!-- Total Profit Keseluruhan -->
    <div class="col-12 col-md-4">
        <div class="card stat-node h-100 border-0 shadow-lg glow-bg-emerald hover-translate transition-all" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body p-3 p-md-4 position-relative overflow-hidden">
                <i class="fa-solid fa-money-bill-trend-up position-absolute opacity-10" style="font-size: 5rem; right: -10px; bottom: -10px; color: #10b981;"></i>
                <div class="small text-white fw-bold mb-1 opacity-75 d-flex align-items-center gap-2">
                    TOTAL PROFIT KOTOR
                </div>
                <h4 class="fw-bold text-white mb-0 ticker-font text-break">Rp {{ number_format($grandTotalProfit, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    
    <!-- Porsi Mitra -->
    <div class="col-12 col-md-4">
        <div class="card stat-node h-100 border-0 shadow-lg hover-translate transition-all" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body p-3 p-md-4 position-relative overflow-hidden">
                <i class="fa-solid fa-users position-absolute opacity-10" style="font-size: 5rem; right: -10px; bottom: -10px; color: #10b981;"></i>
                <div class="small text-white fw-bold mb-1 opacity-75 d-flex align-items-center gap-2">
                    PORSI MITRA
                </div>
                <h4 class="fw-bold text-white mb-0 ticker-font text-break">Rp {{ number_format($grandMitraProfit, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>

    <!-- Porsi Investor Eksternal -->
    <div class="col-12 col-md-4">
        <div class="card stat-node h-100 border-0 shadow-lg hover-translate transition-all" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body p-3 p-md-4 position-relative overflow-hidden">
                <i class="fa-solid fa-user-tie position-absolute opacity-10" style="font-size: 5rem; right: -10px; bottom: -10px; color: #10b981;"></i>
                <div class="small text-white fw-bold mb-1 opacity-75 d-flex align-items-center gap-2">
                    PORSI INVESTOR LUAR
                </div>
                <h4 class="fw-bold text-white mb-0 ticker-font text-break">Rp {{ number_format($grandInvestorProfit, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

@if(count($pendingDistributions) > 0)
<div class="card stat-node border-0 shadow-lg mb-4" style="border: 1px solid rgba(16, 185, 129, 0.3) !important;">
    <div class="card-header bg-black bg-opacity-20 border-bottom border-emerald-900 pt-4 px-4 pb-3">
        <h5 class="fw-bold text-white ticker-font mb-0">
            <i class="fa-solid fa-clock text-emerald-400 me-2"></i>MENUNGGU DISTRIBUSI PROFIT
        </h5>
        <small class="text-white opacity-75">Tentukan persentase bagian Mitra dan bagian Investor secara langsung.</small>
    </div>
    <div class="card-body p-4">
        @foreach($pendingDistributions as $data)
        <div class="glass-card p-4 mb-4 position-relative overflow-hidden">
            <!-- Decorative glossy blur circle -->
            <div class="position-absolute rounded-circle" style="width: 150px; height: 150px; background: rgba(16, 185, 129, 0.05); filter: blur(40px); top: -50px; right: -20px; z-index: 0;"></div>
            
            <div class="position-relative" style="z-index: 1;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        @if($data['ipo']->image_path)
                            <div class="bg-white p-1 rounded-circle shadow-sm d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                <img src="{{ Storage::url($data['ipo']->image_path) }}" alt="{{ $data['ipo']->code }}" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                            </div>
                        @else
                            <div class="rounded-circle bg-gradient-emerald d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 60px; height: 60px; flex-shrink: 0; font-size: 1.5rem;">
                                {{ substr($data['ipo']->code, 0, 2) }}
                            </div>
                        @endif
                        <div>
                            <span class="fw-bold text-white d-block ticker-font fs-4" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">{{ $data['ipo']->code }}</span>
                            <span class="badge bg-emerald-500 bg-opacity-25 text-emerald-400 border border-emerald-500 border-opacity-50 mt-1">{{ $data['ipo']->name }}</span>
                        </div>
                    </div>
                    <div class="text-md-end bg-black bg-opacity-40 p-3 rounded-3 border border-emerald-900 shadow-inner">
                        <div class="small text-emerald-400 fw-bold tracking-wider mb-1">TOTAL PROFIT KOTOR</div>
                        <div class="ticker-font fw-bold text-white fs-3" style="text-shadow: 0 0 10px rgba(16, 185, 129, 0.5);">Rp {{ number_format($data['total_profit'], 0, ',', '.') }}</div>
                    </div>
                </div>

                <form id="distributeForm{{ $data['ipo']->id }}" action="{{ route('profit-distribution.distribute', $data['ipo']->id) }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold tracking-wider mb-2">PORSI MITRA <span class="text-emerald-400">(%)</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3">
                            <input type="number" id="mitra_fee_{{ $data['ipo']->id }}" name="mitra_fee_pct" class="form-control glass-input fw-bold text-white pct-input" placeholder="0" required min="0" max="100" value="50" data-id="{{ $data['ipo']->id }}">
                            <span class="input-group-text glass-input-addon border-start-0">%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold tracking-wider mb-2">PORSI INVESTOR <span class="text-emerald-400">(%)</span></label>
                        <div class="input-group input-group-lg shadow-sm rounded-3">
                            <input type="number" id="investor_fee_{{ $data['ipo']->id }}" name="investor_fee_pct" class="form-control glass-input fw-bold text-white pct-input" placeholder="0" required min="0" max="100" value="50" data-id="{{ $data['ipo']->id }}">
                            <span class="input-group-text glass-input-addon border-start-0">%</span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="button" class="btn btn-glossy-emerald btn-lg flex-grow-1 fw-bold w-100 rounded-3" data-bs-toggle="modal" data-bs-target="#confirmModal{{ $data['ipo']->id }}">
                            <i class="fa-solid fa-bolt me-2"></i> DISTRIBUSIKAN SEKARANG
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @endforeach
    </div>
</div>
@endif

<div class="card stat-node border-0 shadow-lg mb-4">
    <div class="card-header bg-black bg-opacity-20 border-bottom border-emerald-900 pt-4 px-4 pb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-white ticker-font mb-0">
                <i class="fa-solid fa-list-check text-emerald-500 me-2"></i>RIWAYAT DISTRIBUSI SELESAI
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
                        <th>TOTAL PROFIT KOTOR</th>
                        <th>MITRA</th>
                        <th class="pe-4">INVESTOR EKSTERNAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedDistributions as $data)
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
                        <td class="ticker-font fw-bold text-white">
                            Rp {{ number_format($data['total_profit'], 0, ',', '.') }}
                        </td>
                        <td>
                            <div class="ticker-font text-white opacity-90">Rp {{ number_format($data['mitra_profit'], 0, ',', '.') }}</div>
                            <small class="text-white opacity-50">{{ $data['ipo']->mitra_fee_pct }}%</small>
                        </td>
                        <td class="pe-4">
                            @if($data['investor_profit'] > 0)
                                <div class="ticker-font text-white opacity-90">Rp {{ number_format($data['investor_profit'], 0, ',', '.') }}</div>
                                <small class="text-white opacity-50">{{ $data['ipo']->platform_fee_pct }}%</small>
                            @else
                                <span class="opacity-25">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-white opacity-50">
                            <i class="fa-solid fa-folder-open fs-1 d-block mb-3 opacity-50"></i>
                            Belum ada IPO yang didistribusikan secara manual.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-black bg-opacity-20 border-top border-emerald-900 border-opacity-50">
                    <tr>
                        <td class="ps-4 fw-bold text-white py-3">GRAND TOTAL</td>
                        <td class="fw-bold text-emerald-400 py-3">Rp {{ number_format($grandTotalProfit, 0, ',', '.') }}</td>
                        <td class="fw-bold text-white py-3">Rp {{ number_format($grandMitraProfit, 0, ',', '.') }}</td>
                        <td class="fw-bold text-white pe-4 py-3">Rp {{ number_format($grandInvestorProfit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    .glow-bg-emerald { box-shadow: 0 0 15px rgba(16, 185, 129, 0.15) !important; }
    .news-hover { transition: all 0.2s; }
    .news-hover:hover { background-color: rgba(16, 185, 129, 0.05); }
    
    /* Minimalist Glassmorphism Styles */
    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.25rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .glass-input {
        background: rgba(0, 0, 0, 0.3) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.2) !important;
    }
    .glass-input:focus {
        background: rgba(0, 0, 0, 0.5) !important;
        border-color: rgba(16, 185, 129, 0.4) !important;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2), inset 0 2px 4px rgba(0,0,0,0.2) !important;
    }
    .glass-input-addon {
        background: rgba(0, 0, 0, 0.5) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: rgba(255, 255, 255, 0.7) !important;
    }
    .btn-glossy-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: 1px solid #34d399;
        color: #fff !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3), inset 0 1px 0 rgba(255,255,255,0.2);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .btn-glossy-emerald::after {
        content: '';
        position: absolute;
        top: 0; left: -100%; width: 50%; height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-25deg);
        transition: left 0.7s ease;
    }
    .btn-glossy-emerald:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4), inset 0 1px 0 rgba(255,255,255,0.3);
        background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
    }
    .btn-glossy-emerald:hover::after {
        left: 200%;
    }
    .bg-gradient-emerald {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    }
    .shadow-inner {
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);
    }
    .tracking-wider {
        letter-spacing: 0.05em;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.pct-input');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const id = this.getAttribute('data-id');
                const isMitra = this.id.startsWith('mitra');
                let val = parseInt(this.value) || 0;
                
                if (val > 100) { val = 100; this.value = 100; }
                if (val < 0) { val = 0; this.value = 0; }
                
                if (isMitra) {
                    document.getElementById('investor_fee_' + id).value = 100 - val;
                } else {
                    document.getElementById('mitra_fee_' + id).value = 100 - val;
                }
            });
        });
    });
</script>

<!-- Modals rendered outside of all layout wrappers to prevent Bootstrap z-index issues -->
@if(count($pendingDistributions) > 0)
    @foreach($pendingDistributions as $data)
    <div class="modal fade" id="confirmModal{{ $data['ipo']->id }}" tabindex="-1" aria-labelledby="confirmModalLabel{{ $data['ipo']->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-black bg-opacity-75" style="backdrop-filter: blur(20px); border: 1px solid rgba(16, 185, 129, 0.2); box-shadow: 0 15px 35px rgba(0,0,0,0.5);">
                <div class="modal-header border-bottom border-emerald-900 border-opacity-50">
                    <h5 class="modal-title text-white fw-bold ticker-font" id="confirmModalLabel{{ $data['ipo']->id }}">
                        <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>KONFIRMASI DISTRIBUSI
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-white">
                    Apakah Anda yakin ingin membagikan profit secara final untuk saham <span class="text-emerald-400 fw-bold">{{ $data['ipo']->code }}</span> sekarang?<br><br>
                    <div class="bg-black bg-opacity-50 p-3 rounded border border-emerald-900">
                        <small class="text-warning opacity-75 d-block">
                            <i class="fa-solid fa-info-circle me-1"></i>Peringatan:<br>
                            Aksi ini <b>tidak dapat dibatalkan</b>. Setelah "Ya" ditekan, saldo otomatis berpindah ke rekening dompet masing-masing Investor secara permanen.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-top border-emerald-900 border-opacity-50">
                    <button type="button" class="btn btn-dark text-white border-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="distributeForm{{ $data['ipo']->id }}" class="btn btn-glossy-emerald fw-bold">Ya, Bagikan Sekarang</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif

@endsection
