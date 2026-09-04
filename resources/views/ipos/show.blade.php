@extends('layouts.app')

@section('title', 'Detail Event IPO: ' . $ipo->code)

@section('content')
<div class="mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
    <a href="{{ route('ipos.index') }}" class="btn btn-sm btn-outline-secondary border-emerald-900 text-emerald-500 px-3 rounded-pill hover-emerald shadow-sm">
        <i class="fa-solid fa-arrow-left me-2"></i> KEMBALI KE DAFTAR
    </a>
    <div class="badge bg-{{ $ipo->status_color }} bg-opacity-10 text-{{ $ipo->status_color == 'primary' ? 'emerald-400' : ($ipo->status_color == 'warning' ? 'warning' : 'white') }} border border-{{ $ipo->status_color }} border-opacity-25 px-3 py-2 fs-6 shadow-sm rounded-pill mt-2 mt-sm-0">
        STATUS: {{ strtoupper($ipo->status_label) }}
    </div>
</div>

<!-- Step Progress Indicator -->
<div class="card stat-node border-0 shadow-lg mb-4">
    <div class="card-body py-4">
        <div class="row text-center position-relative">
            <!-- Line connector -->
            <div class="position-absolute top-50 start-0 translate-middle-y w-100 px-5 d-none d-md-block" style="z-index: 0;">
                <div style="border-top: 2px dashed rgba(16, 185, 129, 0.25);"></div>
            </div>
            
            @php $steps = [
                ['icon' => 'fa-plus-circle', 'label' => 'Modal', 'desc' => 'Pesan Saham', 'url' => '#', 'modal' => $ipo->placements()->count() === 0 ? '#selectMitrasModal' : null, 'min_step' => 1],
                ['icon' => 'fa-hand-holding-dollar', 'label' => 'Jatah', 'desc' => 'Allotment', 'url' => route('ipos.allotment-bulk', $ipo), 'min_step' => 2],
                ['icon' => 'fa-money-bill-trend-up', 'label' => 'Jual', 'desc' => 'Realisasi', 'url' => route('ipo-sales.create', $ipo), 'min_step' => 3],
                ['icon' => 'fa-check-double', 'label' => 'Selesai', 'desc' => 'Refund/Profit', 'url' => '#', 'min_step' => 4]
            ]; @endphp

            @foreach($steps as $index => $s)
            @php 
                $currentStep = $index + 1; 
                $isCurrent = $ipo->step == $currentStep;
                
                $isClickable = $ipo->step >= $s['min_step'];
                $actualUrl = $isClickable ? $s['url'] : '#';
                
                $circleBgClass = $isCurrent ? 'bg-success text-white shadow-success' : 'text-white opacity-25 border border-emerald-900';
            @endphp
            <div class="col position-relative" style="z-index: 1;">
                <a href="{{ $actualUrl }}" class="text-decoration-none d-block {{ ($isClickable && $actualUrl !== '#') ? 'hover-translate' : '' }}" 
                   {!! !$isClickable ? 'style="cursor: not-allowed;" title="Selesaikan langkah sebelumnya terlebih dahulu"' : '' !!}
                   {!! isset($s['modal']) ? 'data-bs-toggle="modal" data-bs-target="'.$s['modal'].'"' : '' !!}>
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center shadow-sm transition-all
                        {{ $circleBgClass }}" 
                        style="width: 50px; height: 50px; {{ $isCurrent ? '' : 'background-color: #05160c;' }}">
                        <i class="fa-solid {{ $s['icon'] }} fs-5"></i>
                        @if(!$isClickable)
                            <div class="position-absolute bg-dark rounded-circle d-flex align-items-center justify-content-center border border-emerald-900" style="width: 18px; height: 18px; bottom: 0; right: 0;">
                                <i class="fa-solid fa-lock text-emerald-500" style="font-size: 0.5rem;"></i>
                            </div>
                        @endif
                    </div>
                    <h6 class="fw-bold mb-0 {{ $isCurrent ? 'text-success' : 'text-white opacity-25' }} small mt-1 ticker-font">
                        {{ strtoupper($s['label']) }}
                    </h6>
                    <small class="text-white opacity-50 d-none d-md-block" style="font-size: 0.65rem;">{{ strtoupper($s['desc']) }}</small>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sidebar: IPO Info -->
    <div class="col-md-4">
        <div class="card stat-node border-0 shadow-lg mb-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="p-4 text-white position-relative overflow-hidden border-bottom border-emerald-900" style="background: linear-gradient(135deg, #064e3b 0%, #022c22 100%);">
                    <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                        <div>
                            <h1 class="fw-bold mb-0 ticker-font">{{ $ipo->code }}</h1>
                            <p class="mb-0 opacity-75 small text-emerald-300">{{ $ipo->name }}</p>
                        </div>
                        <div class="text-end">
                            <div id="live-pulse-badge" class="badge bg-black bg-opacity-50 rounded-pill px-3 py-2 border border-emerald-500 border-opacity-25 d-none shadow-sm">
                                <span class="spinner-grow spinner-grow-sm text-success me-1" role="status" style="width: 8px; height: 8px;"></span>
                                <span class="small fw-bold text-uppercase" style="font-size: 0.65rem;">Live</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div id="live-price-container" class="mb-4 text-center d-none bg-black bg-opacity-40 p-3 rounded-3 border border-emerald-900 shadow-inner">
                        <div class="display-6 fw-bold text-white mb-0 ticker-font" id="live-price-display">Rp -</div>
                        <div class="fw-bold" id="live-change-display">--%</div>
                        <div class="text-white opacity-50 mt-1" style="font-size: 0.6rem;">LAST UPDATED: <span id="live-time-display">-</span></div>
                    </div>

                    <div class="mb-3 d-flex justify-content-between border-bottom border-emerald-900 border-opacity-30 pb-2">
                        <span class="text-white small opacity-75">HARGA PENAWARAN</span>
                        <span class="fw-bold text-white ticker-font">Rp {{ number_format($ipo->price, 0, ',', '.') }}</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between border-bottom border-emerald-900 border-opacity-30 pb-2">
                        <span class="text-white small opacity-75">LISTING DATE</span>
                        <span class="fw-bold text-white ticker-font">{{ strtoupper(date('d M Y', strtotime($ipo->ipo_date))) }}</span>
                    </div>
                    
                    <div id="live-roi-container" class="mt-4 p-3 bg-black bg-opacity-40 rounded-3 border border-emerald-900 shadow-inner d-none">
                        <div class="text-center">
                            <small class="text-white d-block text-uppercase mb-1 opacity-75" style="font-size: 0.6rem;">Real-time Performance (ROI)</small>
                            <h3 class="fw-bold mb-0 ticker-font" id="live-roi-display">--%</h3>
                        </div>
                    </div>

                    <div class="mt-4 p-4 text-center stat-node border-emerald-500 border-opacity-10 shadow-lg">
                        <small class="text-emerald-500 d-block text-uppercase mb-2 fw-bold" style="font-size: 0.65rem;">Total Modal Pesanan</small>
                        <h4 class="fw-bold text-white mb-0 ticker-font">Rp {{ number_format($placements->sum('capital_allocated'), 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>


        <!-- Next Action Card -->
        <div class="card stat-node border-0 shadow-lg mb-4 border-start border-4 border-{{ $ipo->status_color }}">
            <div class="card-body p-4">
                <h6 class="fw-bold text-white mb-3 ticker-font">STATUS SEKARANG:</h6>
                @if($ipo->step == 1)
                    <p class="small text-white opacity-75 mb-4">Belum ada dana yang dipesan? Mulai alokasikan modal dari investor ke akun mitra.</p>
                    @if($ipo->placements()->count() === 0)
                        <button type="button" class="btn btn-primary-custom w-100 py-3 fw-bold rounded-pill shadow-lg" data-bs-toggle="modal" data-bs-target="#selectMitrasModal">
                            <i class="fa-solid fa-plus-circle me-2"></i>PILIH MITRA & TAMBAH PESANAN
                        </button>
                    @else
                        <div class="d-flex gap-2 mb-4">
                            <button type="button" class="btn btn-outline-warning flex-grow-1 py-3 fw-bold rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#selectMitrasModal">
                                <i class="fa-solid fa-pen-to-square me-2"></i>UBAH PILIHAN MITRA
                            </button>
                            <button type="button" class="btn btn-outline-danger px-4 py-3 fw-bold rounded-pill shadow-sm btn-reset-action" 
                                    data-url="{{ route('ipos.reset-placements', $ipo) }}" 
                                    data-title="RESET PILIHAN MITRA?" 
                                    data-text="Semua akun Mitra yang telah dipilih (termasuk modal yang sudah diinput) akan dihapus. Anda harus memilih ulang dari awal."
                                    title="Reset Semua Pilihan Mitra">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                        
                                            @endif
                @elseif($ipo->step == 2)
                    <p class="small text-white opacity-75 mb-4">Masa pemesanan selesai. Sekarang masukkan berapa jatah Lot yang didapat di setiap akun mitra.</p>
                    <div class="d-flex gap-2 mb-3">
                        <a href="{{ route('ipos.allotment-bulk', $ipo) }}" class="btn btn-warning text-dark flex-grow-1 py-3 fw-bold rounded-pill shadow-lg">
                            <i class="fa-solid fa-hand-holding-dollar me-2"></i>INPUT JATAH SEKALIGUS
                        </a>
                        <button type="button" class="btn btn-outline-danger px-4 py-3 fw-bold rounded-pill shadow-sm btn-reset-action" 
                                data-url="{{ route('ipos.reset-allotments', $ipo) }}" 
                                data-title="RESET SEMUA JATAH LOT?" 
                                data-text="Semua data jatah lot yang telah di-input akan dihapus. Anda dapat mengisi ulang kembali."
                                title="Reset Semua Jatah">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                @elseif($ipo->step == 3)
                    <p class="small text-white opacity-75 mb-4">Semua jatah sudah di-input! Masukkan harga jual saham (Realisasi) untuk menghitung profit per akun.</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('ipo-sales.create', $ipo) }}" class="btn btn-primary-custom flex-grow-1 py-3 fw-bold rounded-pill shadow-lg">
                            <i class="fa-solid fa-chart-line me-2"></i>INPUT HASIL PENJUALAN
                        </a>
                        <button type="button" class="btn btn-outline-danger px-4 py-3 fw-bold rounded-pill shadow-sm btn-reset-action" 
                                data-url="{{ route('ipos.reset-sales', $ipo) }}" 
                                data-title="RESET HASIL PENJUALAN?" 
                                data-text="Semua data hasil penjualan yang telah di-input akan dihapus. Anda dapat mengisi ulang kembali."
                                title="Reset Semua Penjualan">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                @elseif($ipo->step == 4)
                    <p class="small text-white opacity-75 mb-4">Seluruh proses selesai! Dana sisa dan profit telah dikembalikan ke investor.</p>
                    <div class="text-center p-4 bg-black bg-opacity-40 rounded-3 border border-success border-opacity-30 shadow-sm mt-3">
                        <small class="text-white d-block mb-1 opacity-75">TOTAL NET PROFIT GABUNGAN</small>
                        <h3 class="fw-bold text-success ticker-font mb-0 shadow-sm">Rp {{ number_format($ipo->sales->sum('net_profit'), 0, ',', '.') }}</h3>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main List: Placements -->
    <div class="col-md-8">
        <div class="card stat-node border-0 shadow-lg">
            <div class="card-header bg-black bg-opacity-20 border-bottom border-emerald-900 pt-4 px-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h5 class="fw-bold text-white ticker-font mb-0">
                    <i class="fa-solid fa-server me-2 text-emerald-500"></i>DETAIL RINCIAN PER AKUN
                    <span class="badge bg-black bg-opacity-40 text-white border border-emerald-900 border-opacity-50 ms-2" style="font-size: 0.75rem;">{{ $placements->count() }} AKUN</span>
                </h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('ipos.export-placements', $ipo) }}" class="btn btn-sm btn-outline-success px-3 rounded-pill shadow-sm">
                        <i class="fa-solid fa-file-excel me-1"></i> EKSPOR
                    </a>
                    @if($ipo->canEdit())
                        @if($ipo->step == 1)
                            <button type="button" class="btn btn-sm btn-outline-warning px-3 rounded-pill shadow-sm btn-reset-action" 
                                    data-url="{{ route('ipos.reset-capitals', $ipo) }}" 
                                    data-title="RESET INPUT MODAL?" 
                                    data-text="Semua input modal (kapital dan lot) pada daftar di bawah ini akan di-nol-kan, tetapi daftar akun mitra tetap dipertahankan."
                                    title="Kosongkan Semua Input Modal">
                                <i class="fa-solid fa-eraser me-1"></i> RESET MODAL
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-primary-custom px-3 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#selectMitrasModal">
                            <i class="fa-solid fa-pen-to-square me-1"></i> UBAH / TAMBAH MITRA
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @if($ipo->step == 1 && $placements->count() > 0)
                <form action="{{ route('ipos.store-placement', $ipo) }}" method="POST">
                    @csrf
                    
                    <div class="bg-black bg-opacity-40 p-3 mb-0 border-bottom border-emerald-900">
                        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                            <span class="text-emerald-400 fw-bold small"><i class="fa-solid fa-bolt me-1"></i> TANGAN ROBOT:</span>
                            <div class="d-flex flex-wrap gap-2 w-100 align-items-center">
                                <select id="bulk_investor_mini" class="form-select form-select-sm bg-black border-emerald-900 text-white flex-grow-1" style="min-width: 140px; max-width: 250px;">
                                    <option value="">-- INVESTOR --</option>
                                    @foreach($investors as $investor)
                                        <option value="{{ $investor->id }}">{{ strtoupper($investor->name) }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group input-group-sm flex-grow-1" style="min-width: 90px; max-width: 120px;">
                                    <input type="number" id="bulk_lot_mini" class="form-control bg-black border-emerald-900 text-white fw-bold" placeholder="Lot">
                                    <span class="input-group-text bg-emerald-900 bg-opacity-40 text-emerald-400 border-emerald-900">LOT</span>
                                </div>
                                <div class="input-group input-group-sm flex-grow-1" style="min-width: 110px; max-width: 140px;" title="Kosongkan untuk mengisi semua sisa akun">
                                    <input type="number" id="bulk_limit_mini" class="form-control bg-black border-emerald-900 text-white fw-bold" placeholder="Semua">
                                    <span class="input-group-text bg-emerald-900 bg-opacity-40 text-emerald-400 border-emerald-900">AKUN</span>
                                </div>
                                <input type="hidden" id="bulk_share_mini" value="50">
                                <button type="button" id="btn_apply_mini" class="btn btn-sm btn-primary-custom rounded-pill px-4 w-100 mt-2 mt-md-0 w-md-auto ms-md-auto" style="max-width: fit-content;">TERAPKAN</button>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="d-block d-md-none small text-emerald-500 opacity-75 mb-2 px-3 mt-3">
                    <i class="fa-solid fa-arrows-left-right me-1"></i> Geser tabel ke samping untuk melihat menu lainnya
                </div>
                <div class="table-responsive">
                    <table id="placementTable" class="table align-middle text-white mb-0 text-nowrap" style="width:100%">
                        <thead class="bg-black bg-opacity-20">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>AKUN MITRA</th>
                                <th>PEMODAL / INVESTOR</th>
                                @if($ipo->step < 3)
                                <th>PESANAN (LOT)</th>
                                @endif
                                @if($ipo->step > 1)
                                    <th>JATAH LOT</th>
                                @endif
                                @if($ipo->step > 2)
                                    <th>STATUS DANA</th>
                                    <th>HASIL JUAL</th>
                                    <th>NET PROFIT</th>
                                @endif
                                <th class="text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($placements as $placement)
                            <tr class="news-hover mitra-row" data-index="{{ $loop->index }}">
                                <td class="ps-4 text-white opacity-50">{{ $loop->iteration }}</td>
                                <td>
                                    @if($ipo->step == 1)
                                        <input type="hidden" name="allocations[{{ $loop->index }}][account_id]" value="{{ $placement->mitra_account_id }}">
                                        <input type="hidden" name="allocations[{{ $loop->index }}][est_lot]" value="0">
                                    @endif
                                    <div class="fw-bold text-white">{{ strtoupper($placement->mitraAccount->owner_name) }}</div>
                                    <span class="badge bg-black bg-opacity-40 text-emerald-500 border border-emerald-900 border-opacity-30 small fw-normal">{{ strtoupper($placement->mitraAccount->platform) }}</span>
                                </td>
                                <td>
                                    @if($ipo->step == 1)
                                        @php
                                            $existingFundings = $placement->fundings->isEmpty() ? collect([new \App\Models\InvestorFunding(['amount_funded' => 0, 'share_pct' => 50])]) : $placement->fundings;
                                        @endphp
                                        <div class="investor-list" id="investor_list_{{ $loop->index }}">
                                            @foreach($existingFundings as $fIndex => $f)
                                                <div class="investor-item mb-2">
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <select name="allocations[{{ $loop->parent->index }}][investors][{{ $fIndex }}][investor_id]" class="form-select form-select-sm bg-black bg-opacity-40 border-emerald-900 text-white investor-select" style="max-width: 150px;">
                                                            <option value="">-- PILIH --</option>
                                                            @foreach($investors as $investor)
                                                                <option value="{{ $investor->id }}" 
                                                                        data-balance="{{ $investor->computed_balance }}"
                                                                        data-initial-funding="{{ $f->investor_id == $investor->id ? $f->amount_funded : 0 }}"
                                                                        {{ $f->investor_id == $investor->id ? 'selected' : '' }}>
                                                                    {{ strtoupper($investor->name) }} (Rp {{ number_format($investor->computed_balance, 0, '', '.') }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="allocations[{{ $loop->parent->index }}][investors][{{ $fIndex }}][share_pct]" class="investor-share-input" value="50">
                                                    </div>
                                                    <div class="balance-label small mt-1 opacity-75" style="font-size: 0.65rem;"></div>
                                                    <div class="validation-msg small text-danger fw-bold mt-1" style="display:none; font-size: 0.65rem;"></div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        @foreach($placement->fundings as $f)
                                            @php
                                                $investorProfit = 0;
                                                if($placement->sale && $placement->sale->net_profit != 0) {
                                                    $shareOfNet = ($f->amount_funded / $placement->capital_allocated) * $placement->sale->net_profit;
                                                    $investorProfit = $shareOfNet * ($f->share_pct / 100);
                                                }
                                            @endphp
                                            <div class="small mb-1 d-flex align-items-center justify-content-between">
                                                <span>
                                                    <i class="fa-solid fa-user-tag me-1 text-white opacity-50"></i> 
                                                    <span class="text-white fw-bold">{{ strtoupper($f->investor->name) }}</span>
                                                </span>
                                                <div class="text-end">
                                                    @if($ipo->step < 3)
                                                        <span class="text-white opacity-50 small">Rp {{ number_format($f->amount_funded, 0, ',', '.') }}</span>
                                                    @endif
                                                    @if($investorProfit != 0)
                                                        <span class="badge bg-{{ $investorProfit > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $investorProfit > 0 ? 'success' : 'danger' }} ms-1 border border-{{ $investorProfit > 0 ? 'success' : 'danger' }} border-opacity-25" style="font-size: 0.65rem;">
                                                            {{ $investorProfit > 0 ? '+' : '' }}Rp {{ number_format($investorProfit, 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                                @if($ipo->step < 3)
                                <td class="ticker-font">
                                    @if($ipo->step == 1)
                                        <div class="input-group input-group-sm mb-1" style="max-width: 130px;">
                                            <input type="number" 
                                                   class="form-control form-control-sm bg-black bg-opacity-40 border-emerald-900 text-white fw-bold lot-modal-input" 
                                                   value="{{ $placement->fundings->first() && $placement->fundings->first()->amount_funded > 0 ? floor($placement->fundings->first()->amount_funded / ($ipo->price * 100)) : '' }}" 
                                                   placeholder="Lot">
                                            <span class="input-group-text bg-emerald-900 bg-opacity-40 text-emerald-400 border-emerald-900">LOT</span>
                                        </div>
                                        <input type="hidden" name="allocations[{{ $loop->index }}][investors][0][capital]" class="capital-input" value="{{ $placement->fundings->first() ? $placement->fundings->first()->amount_funded : '' }}">
                                        <div class="small text-white opacity-75 capital-display" style="font-size: 0.65rem;">
                                            Rp {{ $placement->fundings->first() && $placement->fundings->first()->amount_funded > 0 ? number_format($placement->fundings->first()->amount_funded, 0, ',', '.') : '0' }}
                                        </div>
                                    @else
                                        <div class="fw-bold text-white small">Rp {{ number_format($placement->capital_allocated, 0, ',', '.') }}</div>
                                        <div class="small text-white opacity-75" style="font-size: 0.65rem;">
                                            {{ floor($placement->capital_allocated / ($ipo->price * 100)) }} LOT
                                        </div>
                                    @endif
                                </td>
                                @endif
                                @if($ipo->step > 1)
                                <td>
                                    @if($placement->allocation)
                                        <div class="fw-bold text-emerald-400 ticker-font small">
                                            {{ number_format($placement->allocation->lot_allocated, 0, ',', '.') }} LOT
                                        </div>
                                    @else
                                        <span class="text-warning italic opacity-75 small">PENDING</span>
                                    @endif
                                </td>
                                @endif
                                @if($ipo->step > 2)
                                <td>
                                    @if($placement->allocation)
                                        <div class="d-flex flex-column gap-1" style="font-size: 0.7rem; min-width: 140px;">
                                            <div class="d-flex justify-content-between align-items-center bg-black bg-opacity-40 px-2 py-1 rounded border border-danger border-opacity-25" title="Modal Terpakai">
                                                <span class="text-danger opacity-75"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Used</span>
                                                <span class="text-danger fw-bold ticker-font">Rp {{ number_format($placement->allocation->total_used, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center bg-emerald-900 bg-opacity-20 px-2 py-1 rounded border border-emerald-900 border-opacity-50" title="Sisa/Refund">
                                                <span class="text-white opacity-75"><i class="fa-solid fa-rotate-left me-1"></i>Sisa</span>
                                                <span class="text-emerald-400 fw-bold ticker-font">Rp {{ number_format($placement->allocation->remaining_capital, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-white opacity-25">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($placement->sale)
                                        <div class="fw-bold text-white small">Rp {{ number_format($placement->sale->total_return, 0, ',', '.') }}</div>
                                        <div class="small text-emerald-500" style="font-size: 0.65rem;">@ {{ number_format($placement->sale->sell_price, 0, ',', '.') }}</div>
                                    @elseif($placement->allocation && $placement->allocation->lot_allocated == 0)
                                        <div class="fw-bold text-white small opacity-50">Rp 0</div>
                                    @else
                                        <span class="text-white opacity-25">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($placement->sale)
                                        <div class="fw-bold small {{ $placement->sale->net_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $placement->sale->net_profit >= 0 ? '+' : '' }}Rp {{ number_format($placement->sale->net_profit, 0, ',', '.') }}
                                        </div>
                                        @if($placement->allocation && $placement->allocation->total_used > 0)
                                            <small class="opacity-50 {{ $placement->sale->net_profit >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.6rem;">
                                                {{ number_format(($placement->sale->net_profit / $placement->allocation->total_used) * 100, 2) }}% ROI
                                            </small>
                                        @endif
                                    @elseif($placement->allocation && $placement->allocation->lot_allocated == 0)
                                        <div class="fw-bold small text-white opacity-50">Rp 0</div>
                                    @else
                                        <span class="text-white opacity-25">-</span>
                                    @endif
                                </td>
                                @endif
                                <td class="text-end pe-4">
                                    @if($ipo->step == 1)
                                        <button type="button" 
                                                class="btn btn-sm btn-success text-white px-3 rounded-pill btn-kick-row ms-2" 
                                                data-url="{{ route('ipos.destroy-row-placement', ['ipo' => $ipo->id, 'account' => $placement->mitra_account_id]) }}"
                                                data-account-name="{{ $placement->mitraAccount->owner_name }}"
                                                title="Hapus / Keluarkan Mitra ini dari daftar IPO">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    @elseif($ipo->step == 2)
                                        @if($placement->allocation)
                                            <a href="{{ route('ipo-allocations.create', $placement) }}" class="btn btn-sm btn-success text-white rounded-circle shadow-sm hover-glow d-inline-flex align-items-center justify-content-center mx-1" style="width: 35px; height: 35px;" title="Update Jatah">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('ipo-allocations.create', $placement) }}" class="btn btn-sm btn-success text-white rounded-circle shadow-sm hover-glow d-inline-flex align-items-center justify-content-center mx-1" style="width: 35px; height: 35px;" title="Input Jatah">
                                                <i class="fa-solid fa-bolt"></i>
                                            </a>
                                        @endif
                                    @elseif($ipo->step >= 3)
                                        @if($placement->sale)
                                            <a href="{{ route('ipo-sales.create', $ipo) }}" class="btn btn-sm btn-success text-white rounded-circle shadow-sm hover-glow d-inline-flex align-items-center justify-content-center mx-1" style="width: 35px; height: 35px;" title="Update Jual">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('ipo-sales.create', $ipo) }}" class="btn btn-sm btn-success text-white rounded-circle shadow-sm hover-glow d-inline-flex align-items-center justify-content-center mx-1" style="width: 35px; height: 35px;" title="Input Jual">
                                                <i class="fa-solid fa-bolt"></i>
                                            </a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($ipo->step == 1 && $placements->count() > 0)
                    <div class="p-4 bg-black bg-opacity-20 text-center border-top border-emerald-900">
                        <button type="submit" class="btn btn-primary-custom px-5 py-3 rounded-pill shadow-lg fw-bold ticker-font">
                            <i class="fa-solid fa-save me-2"></i> SIMPAN ALOKASI MODAL
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .shadow-inner {
        box-shadow: inset 0 2px 6px 0 rgba(0, 0, 0, 0.4);
    }
    .hover-glow:hover {
        box-shadow: 0 0 15px rgba(25, 135, 84, 0.4) !important;
        background-color: #198754 !important;
        color: white !important;
    }
    .glow-bg-primary { box-shadow: 0 0 10px rgba(16, 185, 129, 0.3); }
    .glow-bg-success { box-shadow: 0 0 10px rgba(16, 185, 129, 0.3); }
    .glow-bg-warning { box-shadow: 0 0 10px rgba(255, 193, 7, 0.3); }
    .glow-bg-info { box-shadow: 0 0 10px rgba(13, 202, 240, 0.3); }
    .hover-emerald:hover { color: #10b981 !important; transform: translateX(-3px); transition: all 0.2s; }
</style>

<style>
    .hover-emerald:hover { color: #10b981 !important; }
    .mitra-row:last-child { border-bottom: none !important; }
    .balance-label { font-size: 0.65rem; }
    .investor-item { transition: all 0.3s ease; }
    .investor-item:hover { border-color: rgba(16, 185, 129, 0.4) !important; background: rgba(16, 185, 129, 0.05) !important; }
    .swal2-popup { background: #05160c !important; color: white !important; border: 1px solid #064e3b !important; border-radius: 20px !important; }
    .swal2-title { color: white !important; font-family: 'JetBrains Mono', monospace !important; }
    .swal2-html-container { color: rgba(255, 255, 255, 0.7) !important; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ticker = "{{ $ipo->code }}";
        const ipoPrice = {{ $ipo->price }};
        
        function fetchLivePrice() {
            fetch(`/ticker-live/${ticker}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('live-pulse-badge').classList.remove('d-none');
                        document.getElementById('live-price-container').classList.remove('d-none');
                        document.getElementById('live-roi-container').classList.remove('d-none');
                        
                        document.getElementById('live-price-display').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(data.current_price)}`;
                        
                        const changeEl = document.getElementById('live-change-display');
                        changeEl.innerText = `${data.change_pct > 0 ? '+' : ''}${data.change_pct}%`;
                        changeEl.className = `fw-bold ticker-font ${data.change_pct >= 0 ? 'text-success' : 'text-danger'}`;
                        
                        document.getElementById('live-time-display').innerText = data.last_update.toUpperCase();
                        
                        // ROI calculation against IPO Price
                        const roi = ((data.current_price - ipoPrice) / ipoPrice) * 100;
                        const roiEl = document.getElementById('live-roi-display');
                        roiEl.innerText = `${roi > 0 ? '+' : ''}${roi.toFixed(2)}%`;
                        roiEl.className = `fw-bold mb-0 ticker-font ${roi >= 0 ? 'text-success' : 'text-danger'}`;
                    }
                })
                .catch(err => console.error('Error fetching live price:', err));
        }

        fetchLivePrice();
        setInterval(fetchLivePrice, 60000);

    const globalInitialFunding = {!! json_encode($totalsByInvestor ?? []) !!};
    
    $(document).ready(function() {
        // Auto format Rupiah
        function formatRupiah(value) {
            let number_string = value.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        }

        $(document).on('keyup', '.rupiah-input, .capital-input', function(e) {
            $(this).val(formatRupiah($(this).val()));
            checkAllBalances();
        });

        $(document).on('change', '.investor-select', function() {
            checkAllBalances();
        });

        // Initialize balances on load
        setTimeout(checkAllBalances, 100);

        // Mini Tangan Robot (Apply to All with Limit)
        $('#btn_apply_mini').on('click', function() {
            const bulkInv = $('#bulk_investor_mini').val();
            const bulkLot = $('#bulk_lot_mini').val();
            const bulkLimit = parseInt($('#bulk_limit_mini').val()) || 0;
            const bulkShare = $('#bulk_share_mini').val();
            
            if (!bulkInv && !bulkLot) return; // do nothing if both empty

            // 1. Hitung berapa jumlah akun kosong (belum terisi) saat ini
            let emptyCount = 0;
            $('.investor-item').each(function() {
                const select = $(this).find('.investor-select');
                if (!select.val() || select.val() === "") {
                    emptyCount++;
                }
            });

            // 2. Validasi Jika Semua Sudah Terisi
            if (emptyCount === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Semua Akun Sudah Terisi',
                    text: 'Tidak ada akun kosong tersisa. Jika ingin mengubah/mengulang, gunakan tombol [RESET PILIHAN MITRA] berbentuk tong sampah merah di atas.',
                    background: '#05160c',
                    color: '#fff',
                    confirmButtonColor: '#10b981'
                });
                return;
            }

            // 3. Validasi Human Error: Limit tidak boleh melebihi sisa akun kosong
            if (bulkLimit > emptyCount) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kelebihan Input Akun!',
                    text: `Anda memasukkan angka ${bulkLimit} akun, padahal sisa akun yang kosong (belum dimodali) hanya ada ${emptyCount} akun. Silakan koreksi input Anda!`,
                    background: '#05160c',
                    color: '#fff',
                    confirmButtonColor: '#10b981'
                });
                return;
            }

            let applied = 0;
            $('.investor-item').each(function() {
                if (bulkLimit > 0 && applied >= bulkLimit) return false; // break loop if limit reached
                
                const select = $(this).find('.investor-select');
                const lotInput = $(this).closest('.news-hover').find('.lot-modal-input');
                
                // Hanya isi baris yang KOSONG (Investor belum dipilih)
                if (!select.val() || select.val() === "") {
                    if (bulkInv) select.val(bulkInv);
                    
                    if (bulkLot) {
                        lotInput.val(bulkLot);
                        const capital = parseFloat(bulkLot) * ipoPrice * 100;
                        const td = lotInput.closest('td');
                        td.find('.capital-input').val(capital);
                        td.find('.capital-display').text('Rp ' + capital.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                    }

                    if (bulkShare) {
                        $(this).find('.investor-share-input').val(bulkShare);
                    }
                    
                    applied++;
                }
            });
            
            if(applied > 0) {
                checkAllBalances();
                saveFormState();
                
                Swal.fire({
                    icon: 'success',
                    title: 'BAM! BEKERJA KILAT!',
                    text: `Tangan robot berhasil memodali ${applied} akun mitra. Sisa akun kosong: ${emptyCount - applied}.`,
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#05160c',
                    color: '#fff',
                    iconColor: '#10b981',
                    customClass: { popup: 'border border-emerald-900 border-opacity-50' }
                });
            }
        });

        
        // --- Dynamic Row Management ---
        $(document).on('click', '.add-investor', function() {
            const mitraIndex = $(this).data('mitra-index');
            const list = $(`#investor_list_${mitraIndex}`);
            const rowCount = list.find('.investor-item').length;
            
            // Clone first row and clear inputs
            const firstRow = list.find('.investor-item').first();
            const newRow = firstRow.clone();
            
            // Update names for the new row
            newRow.find('select, input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[investors]\[\d+]/, `[investors][${rowCount}]`);
                    $(this).attr('name', newName).val('');
                }
            });
            
            // Add remove button if not exists
            if (newRow.find('.remove-investor').length === 0) {
                newRow.find('.col-md-1').append('<button type="button" class="btn btn-sm btn-outline-danger border-0 remove-investor rounded-circle"><i class="fa-solid fa-xmark"></i></button>');
            }
            
            list.append(newRow);
            checkAllBalances();
        });

        $(document).on('click', '.remove-investor', function() {
            const row = $(this).closest('.investor-item');
            const mitraRow = $(this).closest('.mitra-row');
            row.remove();
            calculateMitraRow(mitraRow.data('index'));
            checkAllBalances();
        });

        // --- Calculation Logic ---

        function calculateMitraRow(index) {
            const row = $(`.mitra-row[data-index="${index}"]`);
            let totalCapital = 0;
            
            row.find('.capital-input').each(function() {
                totalCapital += parseFloat($(this).val()) || 0;
            });
            
            row.find('.total-capital-label').text('Rp ' + totalCapital.toLocaleString('id-ID'));
            
            // Update Lot
            const lot = Math.floor(totalCapital / ipoPrice / 100);
            row.find('.lot-input').val(lot);
        }

        // Init calculations
        $('.mitra-row').each(function() {
            calculateMitraRow($(this).data('index'));
        });

        $(document).on('input', '.capital-input', function() {
            const index = $(this).closest('.mitra-row').data('index');
            calculateMitraRow(index);
        });

        $(document).on('input', '.lot-modal-input', function() {
            const lot = parseFloat($(this).val()) || 0;
            const newTotalCapital = lot * ipoPrice * 100;
            
            const tr = $(this).closest('tr');
            const capitalInput = tr.find('.capital-input');
            const capitalDisplay = tr.find('.capital-display');
            
            capitalInput.val(newTotalCapital);
            capitalDisplay.text('Rp ' + newTotalCapital.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            
            checkAllBalances();
        });

        // --- Global Balance Guard Logic ---

        function checkAllBalances() {
            const investorTotals = {};
            const investorPools = {};
            
            // First pass: sum all spent amounts
            $('.investor-item').each(function() {
                const select = $(this).find('.investor-select');
                const row = $(this).closest('.news-hover');
                const input = row.find('.capital-input');
                const id = select.val();
                if (!id) return;

                const cap = parseFloat(input.val().replace(/\./g, '')) || 0;
                investorTotals[id] = (investorTotals[id] || 0) + cap;

                if (!investorPools[id]) {
                    const selected = select.find('option:selected');
                    const balance = parseFloat(selected.data('balance')) || 0;
                    const initialTotal = parseFloat(globalInitialFunding[id]) || 0;
                    investorPools[id] = balance + initialTotal;
                }
            });

            // Update dropdown option text for all selects globally so the remaining balance is accurate
            $('.investor-select').each(function() {
                $(this).find('option').each(function() {
                    const optId = $(this).val();
                    if (!optId) return; // skip placeholder
                    
                    if (!investorPools[optId]) {
                        const balance = parseFloat($(this).data('balance')) || 0;
                        const initialTotal = parseFloat(globalInitialFunding[optId]) || 0;
                        investorPools[optId] = balance + initialTotal;
                    }

                    const spent = investorTotals[optId] || 0;
                    const remaining = investorPools[optId] - spent;
                    
                    // The original text was like "BUDI (Rp 1.000.000)"
                    // We extract the name part before " ("
                    const textParts = $(this).text().split(' (Rp');
                    const name = textParts[0].trim();
                    $(this).text(`${name} (Rp ${remaining.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0})})`);
                });
            });

            // Update balance labels and validation
            $('.investor-item').each(function() {
                const select = $(this).find('.investor-select');
                const row = $(this).closest('.news-hover');
                const input = row.find('.capital-input');
                const lbl = $(this).find('.balance-label');
                const msg = $(this).find('.validation-msg');
                const id = select.val();

                if (!id) {
                    lbl.text('');
                    msg.hide();
                    input.removeClass('is-invalid');
                    return;
                }

                const totalSpent = investorTotals[id];
                const maxAllowed = investorPools[id];
                const remaining = maxAllowed - totalSpent;

                lbl.html(`SISA SALDO: <span class="fw-bold ticker-font ${remaining < 0 ? 'text-danger' : 'text-emerald-400'}">Rp ${remaining.toLocaleString('id-ID')}</span>`);

                if (totalSpent > maxAllowed) {
                    input.addClass('is-invalid');
                    const shortfall = totalSpent - maxAllowed;
                    msg.html(`<i class="fa-solid fa-circle-xmark me-1"></i>SALDO TIDAK CUKUP! (Kekurangan: Rp ${shortfall.toLocaleString('id-ID')})`).show();
                } else {
                    input.removeClass('is-invalid');
                    msg.hide();
                }
            });
        }

        $(document).on('change', '.investor-select', function() {
            checkAllBalances();
            saveFormState();
        });

        // When LOT is typed, update capital automatically
        $(document).on('input', '.lot-modal-input', function() {
            const lot = parseFloat($(this).val()) || 0;
            const capital = lot * ipoPrice * 100;
            const td = $(this).closest('td');
            
            td.find('.capital-input').val(capital);
            td.find('.capital-display').text('Rp ' + capital.toLocaleString('id-ID'));
            
            checkAllBalances();
            saveFormState();
        });

        // Remove old capital input event since it's hidden now
        // $(document).on('input', '.capital-input', function() { ... });

        $(document).on('change', 'select[name$="[share_pct]"]', function() {
            saveFormState();
        });

        // --- Form Auto-Save (LocalStorage) ---
        function saveFormState() {
            const formData = {};
            $('form[action*="store-placement"] select[name^="allocations"], form[action*="store-placement"] input[name^="allocations"]').each(function() {
                if ($(this).attr('name')) {
                    formData[$(this).attr('name')] = $(this).val();
                }
            });
            localStorage.setItem('ipo_form_v2_{{ $ipo->id }}', JSON.stringify(formData));
        }

        function restoreFormState() {
            const saved = localStorage.getItem('ipo_form_v2_{{ $ipo->id }}');
            if (saved) {
                try {
                    const formData = JSON.parse(saved);
                    let restored = false;
                    for (const name in formData) {
                        const el = $(`[name="${name}"]`);
                        if (el.length && el.val() !== formData[name]) {
                            el.val(formData[name]);
                            restored = true;
                        }
                    }
                    if (restored) {
                        $('.capital-input').each(function() {
                            const cap = parseFloat($(this).val()) || 0;
                            const lot = Math.floor(cap / (ipoPrice * 100));
                            const td = $(this).closest('td');
                            td.find('.lot-modal-input').val(lot > 0 ? lot : '');
                            td.find('.capital-display').text('Rp ' + cap.toLocaleString('id-ID'));
                        });

                        $('.rupiah-input').each(function() {
                            $(this).val(formatRupiah($(this).val()));
                        });
                        checkAllBalances();
                    }
                } catch (e) {
                    console.error("Failed to restore form state", e);
                }
            }
        }

        // Restore on load
        restoreFormState();

        // Clear local storage when the main form is submitted
        $('form[action*="store-placement"]').on('submit', function() {
            localStorage.removeItem('ipo_form_{{ $ipo->id }}');
        });

        checkAllBalances();

        // --- Row Confirmation ---

        $(document).on('click', '.btn-confirm-row', function() {
            const btn = $(this);
            const index = btn.data('mitra-index');
            const accName = btn.data('account-name').toUpperCase();
            const row = $(`.mitra-row[data-index="${index}"]`);
            
            if (row.find('.is-invalid').length > 0) {
                Swal.fire('Error', 'Selesaikan error saldo terlebih dahulu!', 'error');
                return;
            }

            const totalCap = row.find('.total-capital-label').text();
            const estLot = row.find('.lot-input').val();
            const ticker = "{{ $ipo->code }}";

            Swal.fire({
                title: 'KONFIRMASI ALOKASI',
                html: `Apakah anda yakin ingin membeli <b>${ticker}</b> senilai <b>${totalCap}</b> (${estLot} Lot) di akun <b>${accName}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'YA, FIX BELI!',
                cancelButtonText: 'BATAL'
            }).then((result) => {
                if (result.isConfirmed) {
                    saveRow(index);
                }
            });
        });

        $(document).on('click', '.btn-reset-row', function() {
            const btn = $(this);
            const accId = btn.data('account-id');
            const accName = btn.data('account-name').toUpperCase();
            const url = "{{ route('ipos.destroy-row-placement', [$ipo->id, ':accId']) }}".replace(':accId', accId);

            Swal.fire({
                title: 'RESET ALOKASI?',
                text: `Seluruh dana investor untuk akun ${accName} akan dikembalikan ke saldo wallet mereka.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'YA, RESET DATA',
                cancelButtonText: 'BATAL'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            Swal.fire({title:'BERHASIL!', text:response.message, icon:'success', timer:1500, showConfirmButton:false}).then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('GAGAL!', 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });

        function saveRow(index) {
            const row = $(`.mitra-row[data-index="${index}"]`);
            const btn = row.find('.btn-confirm-row');
            
            let totalInvestorShare = 0;
            row.find('.investor-share-input').each(function() {
                totalInvestorShare += parseFloat($(this).val()) || 0;
            });

            if (totalInvestorShare > 100) {
                Swal.fire('Error', 'Total bagi hasil investor tidak boleh melebihi 100%!', 'error');
                return;
            }

            const calculatedMitraShare = 100 - totalInvestorShare;
            
            const formData = {
                _token: "{{ csrf_token() }}",
                account_id: row.find('input[name*="[account_id]"]').val(),
                est_lot: row.find('.lot-input').val(),
                mitra_share_pct: calculatedMitraShare,
                investors: []
            };

            row.find('.investor-item').each(function() {
                formData.investors.push({
                    investor_id: $(this).find('.investor-select').val(),
                    capital: $(this).find('.capital-input').val(),
                    share_pct: $(this).find('.investor-share-input').val()
                });
            });

            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>PROCESSING...');

            $.ajax({
                url: "{{ route('ipos.store-row-placement', $ipo) }}",
                method: "POST",
                data: formData,
                success: function(response) {
                    Swal.fire({title:'TERSIPAN!', text:response.message, icon:'success', timer:1500, showConfirmButton:false}).then(() => {
                        location.reload(); 
                    });
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-shield-check me-1"></i> KONFIRMASI DANA');
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                    Swal.fire('GAGAL!', msg, 'error');
                }
            });
        }

        function lockRow(index) {
            const row = $(`.mitra-row[data-index="${index}"]`);
            row.find('.investor-select, .capital-input, .investor-share-input, .add-investor, .remove-investor').prop('disabled', true);
            row.find('.lot-input').prop('disabled', false);
            row.css('background', 'rgba(16, 185, 129, 0.05)');
        }

        $(document).on('click', '.btn-unlock-row', function() {
            const index = $(this).data('mitra-index');
            const row = $(`.mitra-row[data-index="${index}"]`);
            
            Swal.fire({
                title: 'BUKA KUNCI MODAL?',
                text: "Anda perlu melakukan konfirmasi ulang jika dana diubah.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0ea5e9',
                confirmButtonText: 'YA, BUKA KUNCI'
            }).then((result) => {
                if (result.isConfirmed) {
                    row.find('.investor-select, .capital-input, .investor-share-input, .add-investor, .remove-investor').prop('disabled', false);
                    row.css('background', 'transparent');
                    $(this).addClass('d-none');
                    row.find('.btn-confirm-row').removeClass('d-none');
                }
            });
        });

        $('.mitra-row').each(function() {
            const index = $(this).data('index');
            const hasData = $(this).find('.btn-unlock-row').is(':visible');
            if (hasData) lockRow(index);
        });

        // Bulk Apply Logic (Tangan Robot)
        $('#btn-bulk-apply').on('click', function() {
            const lot = $('#bulk_lot').val();
            const investorId = $('#bulk_investor').val();
            const sharePct = $('#bulk_share').val();

            if (!lot || lot <= 0) {
                Swal.fire({ icon: 'warning', title: 'Isi Jatah Lot terlebih dahulu', background: '#05160c', color: '#fff' });
                return;
            }

            $('.mitra-row').each(function() {
                const row = $(this);
                // Skip if row is locked (btn-confirm-row is hidden)
                if (row.find('.btn-confirm-row').hasClass('d-none')) {
                    return; 
                }

                // Set lot
                const lotInput = row.find('.lot-input');
                lotInput.val(lot);
                
                // Remove extra investors, keep only the first one
                row.find('.investor-item:not(:first)').remove();
                
                // Set investor
                const firstInvestorItem = row.find('.investor-item').first();
                if (investorId) {
                    firstInvestorItem.find('.investor-select').val(investorId).trigger('change');
                } else {
                    firstInvestorItem.find('.investor-select').val('').trigger('change');
                }
                firstInvestorItem.find('.investor-share-input').val(sharePct);
                
                // Trigger input to auto-calculate capital
                lotInput.trigger('input');
            });

            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Formulir di layar berhasil diisi!', showConfirmButton: false, timer: 2000, background: '#05160c', color: '#fff' });
        });

        // Bulk Reset Logic
        $('#btn-bulk-reset').on('click', function() {
            Swal.fire({
                title: 'Reset Semua Baris?',
                text: 'Ini akan mengosongkan Lot dan Investor pada semua baris yang belum dikunci di halaman ini.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'YA, RESET SEMUA'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('.mitra-row').each(function() {
                        const row = $(this);
                        if (row.find('.btn-confirm-row').hasClass('d-none')) return;

                        const lotInput = row.find('.lot-input');
                        lotInput.val(0);
                        
                        row.find('.investor-item:not(:first)').remove();
                        
                        const firstInvestorItem = row.find('.investor-item').first();
                        firstInvestorItem.find('.investor-select').val('').trigger('change');
                        firstInvestorItem.find('.investor-share-input').val('50');
                        
                        lotInput.trigger('input');
                    });
                    $('#bulk_lot').val('');
                    $('#bulk_investor').val('').trigger('change');
                    $('#bulk_share').val('50');
                    
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Baris berhasil di-reset', showConfirmButton: false, timer: 1500, background: '#05160c', color: '#fff' });
                }
            });
        });

        // Global Database Reset Logic
        $('#btn-reset-all-db').on('click', function() {
            Swal.fire({
                title: 'RESET SEMUA ALOKASI?',
                text: "PERINGATAN! Seluruh dana investor dari SEMUA Mitra untuk IPO ini akan ditarik dan dikembalikan ke saldo wallet mereka. Aksi ini tidak dapat dibatalkan.",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#064e3b',
                confirmButtonText: 'YA, RESET SEMUA DATA'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = $(this);
                    const originalText = btn.html();
                    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: "{{ route('ipos.reset-placements', $ipo) }}",
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            Swal.fire({title:'BERHASIL!', text:response.message, icon:'success', timer:2000, showConfirmButton:false, background: '#05160c', color: '#fff'}).then(() => {
                                location.reload(); 
                            });
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(originalText);
                            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                            Swal.fire({title:'GAGAL!', text:msg, icon:'error', background: '#05160c', color: '#fff'});
                        }
                    });
                }
            });
        });

        // Hapus (Kick) Mitra Row
        $('.btn-kick-row').on('click', function() {
            const url = $(this).data('url');
            const name = $(this).data('account-name');
            const tr = $(this).closest('.mitra-row');

            Swal.fire({
                title: 'Keluarkan Mitra?',
                html: `Keluarkan <b>${name}</b> dari daftar IPO ini? <br><small>(Jika sudah ada modal, modal akan di-refund)</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#3f3f46',
                confirmButtonText: 'Ya, Keluarkan!',
                cancelButtonText: 'Batal',
                background: '#05160c',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, background: '#05160c', color: '#fff', didOpen: () => { Swal.showLoading(); }});
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.close();
                            tr.fadeOut(400, function() { 
                                $(this).remove(); 
                                
                                // Update badge count
                                let currentText = $('#mitra-count-badge').text();
                                let count = parseInt(currentText);
                                if (!isNaN(count) && count > 0) {
                                    $('#mitra-count-badge').text((count - 1) + ' MITRA TERPILIH');
                                }
                            });
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: response.message || 'Mitra dikeluarkan', showConfirmButton: false, timer: 2000, background: '#05160c', color: '#fff' });
                        },
                        error: function(xhr) {
                            let msg = 'Gagal menghapus mitra.';
                            if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: msg, background: '#05160c', color: '#fff' });
                        }
                    });
                }
            });
        });
    });

        // Live Price Updater (if needed)
        // ... (existing code for live price)
        
        // Generic Action Reset Logic
        $('.btn-reset-action').on('click', function() {
            const url = $(this).data('url');
            const title = $(this).data('title');
            const text = $(this).data('text');
            
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#3f3f46',
                confirmButtonText: 'Ya, Reset Semua!',
                cancelButtonText: 'Batal',
                background: '#05160c',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Meriset Data...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        background: '#05160c',
                        color: '#fff',
                        didOpen: () => { Swal.showLoading(); }
                    });

                    // Send DELETE request
                    $.ajax({
                        url: url,
                        type: 'POST', // using POST with _method=DELETE
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Pilihan Mitra berhasil direset.',
                                background: '#05160c',
                                color: '#fff',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let msg = 'Terjadi kesalahan sistem.';
                            if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: msg,
                                background: '#05160c',
                                color: '#fff'
                            });
                        }
                    });
                }
            });
        });

    });
</script>


@endsection

<!-- Select Mitras Modal -->
<div class="modal fade" id="selectMitrasModal" tabindex="-1" aria-labelledby="selectMitrasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-black border border-emerald-900 shadow-lg">
            <form action="{{ route('ipos.select-mitras', $ipo) }}" method="POST">
                @csrf
                <div class="modal-header border-emerald-900 border-opacity-50">
                    <h5 class="modal-title fw-bold text-white ticker-font" id="selectMitrasModalLabel">PILIH AKUN MITRA</h5>
                    <button type="button" class="btn-close btn-close-white opacity-50" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-white opacity-75 small mb-4">Centang akun Mitra yang akan digunakan untuk memesan saham di IPO ini.</p>
                    
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3">
                        <div class="form-check custom-checkbox mb-0">
                            <input class="form-check-input border-emerald-500" type="checkbox" id="selectAllMitras" onchange="$('.mitra-checkbox').prop('checked', this.checked)">
                            <label class="form-check-label text-white fw-bold" for="selectAllMitras">
                                Pilih Semua Mitra
                            </label>
                        </div>
                        <div class="input-group input-group-sm" style="max-width: 300px;">
                            <span class="input-group-text bg-transparent border-emerald-900 text-emerald-500"><i class="fa-solid fa-search"></i></span>
                            <input type="text" class="form-control border-emerald-900 border-start-0 bg-black text-white" id="searchMitraInput" placeholder="Ketik min. 4 huruf...">
                        </div>
                    </div>
                    <hr class="border-emerald-900 border-opacity-50">
                    
                    <div id="mitraSummaryContainer" class="text-center py-4 bg-black bg-opacity-40 rounded-3 border border-emerald-900 border-opacity-50">
                        <i class="fa-solid fa-users text-emerald-500 opacity-50 mb-2 fs-2"></i>
                        <h5 class="text-white mb-1"><span id="mitraSelectedCount" class="text-emerald-400 fw-bold ticker-font">0</span> Mitra Terpilih</h5>
                        <p class="text-white opacity-50 small mb-0">dari total {{ \App\Models\MitraAccount::where('status', 'aktif')->count() }} akun mitra aktif.</p>
                        <p class="text-emerald-500 opacity-75 small mt-2 mb-0"><i class="fa-solid fa-info-circle me-1"></i> Gunakan kolom pencarian di atas untuk mengecualikan atau menambah mitra tertentu.</p>
                    </div>
                    
                    <div class="pe-2" id="mitraListContainer" style="display: none; max-height: 500px; overflow-y: auto; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px;">
                        @php
                            $allMitras = \App\Models\MitraAccount::where('status', 'aktif')->get();
                            $selectedMitraIds = $ipo->placements->pluck('mitra_account_id')->toArray();
                        @endphp
                        @foreach($allMitras as $mitra)
                        <div class="form-check custom-checkbox bg-black bg-opacity-40 p-2 rounded border border-emerald-900 border-opacity-30 d-flex align-items-center mitra-item" style="height: 100%;">
                            <input class="form-check-input border-emerald-500 ms-1 mitra-checkbox flex-shrink-0" type="checkbox" name="mitra_ids[]" value="{{ $mitra->id }}" id="mitra_{{ $mitra->id }}" {{ in_array($mitra->id, $selectedMitraIds) ? 'checked' : '' }} onchange="updateMitraCount()">
                            <label class="form-check-label text-emerald-300 ms-2 text-break mitra-label" style="font-size: 0.75rem; line-height: 1.2;" for="mitra_{{ $mitra->id }}">
                                {{ $mitra->owner_name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <script>
                    function updateMitraCount() {
                        const count = document.querySelectorAll('.mitra-checkbox:checked').length;
                        document.getElementById('mitraSelectedCount').innerText = count;
                    }
                    
                    document.addEventListener('DOMContentLoaded', function() {
                        const searchInput = document.getElementById('searchMitraInput');
                        const summaryContainer = document.getElementById('mitraSummaryContainer');
                        const listContainer = document.getElementById('mitraListContainer');
                        
                        // Initial count
                        updateMitraCount();
                        
                        // Select All Event
                        document.getElementById('selectAllMitras').addEventListener('change', function() {
                            const isChecked = this.checked;
                            document.querySelectorAll('.mitra-checkbox').forEach(function(cb) {
                                cb.checked = isChecked;
                            });
                            updateMitraCount();
                        });
                        
                        if(searchInput) {
                            searchInput.addEventListener('input', function() {
                                const query = this.value.toLowerCase().trim();
                                
                                if (query.length < 4) {
                                    summaryContainer.style.display = 'block';
                                    listContainer.style.display = 'none';
                                } else {
                                    summaryContainer.style.display = 'none';
                                    listContainer.style.display = 'grid';
                                    
                                    const items = document.querySelectorAll('.mitra-item');
                                    items.forEach(function(item) {
                                        const label = item.querySelector('.mitra-label').textContent.toLowerCase();
                                        if (label.indexOf(query) !== -1) {
                                            item.classList.remove('d-none');
                                            item.classList.add('d-flex');
                                        } else {
                                            item.classList.remove('d-flex');
                                            item.classList.add('d-none');
                                        }
                                    });
                                }
                            });
                            
                            // Autofocus on modal open
                            const modalElement = document.getElementById('selectMitrasModal');
                            if (modalElement) {
                                modalElement.addEventListener('shown.bs.modal', function () {
                                    searchInput.focus();
                                });
                            }
                        }
                    });
                </script>
                <div class="modal-footer border-emerald-900 border-opacity-50">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-bold">Lanjut <i class="fa-solid fa-arrow-right ms-1"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<!-- DataTables CSS & JS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // DataTable Initialization
        $('#placementTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100, "Semua"]],
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data",
                "emptyTable": "<div class='text-center py-5 text-white opacity-50'><i class='fa-solid fa-folder-open fs-1 d-block mb-3'></i>Belum ada modal yang dialokasikan ke akun mitra.</div>", "zeroRecords": "<div class='text-center py-5 text-white opacity-50'><i class='fa-solid fa-folder-open fs-1 d-block mb-3'></i>Data tidak ditemukan</div>",
                "info": "Hal. _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data",
                "infoFiltered": "(filter dari _MAX_ total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "ordering": false
        });
    });
</script>

<style>
    /* DataTables Dark Emerald Overrides */
    div.dataTables_wrapper div.dataTables_length select,
    div.dataTables_wrapper div.dataTables_filter input {
        background-color: #05160c;
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: white;
        border-radius: 0.375rem;
    }
    div.dataTables_wrapper div.dataTables_length select:focus,
    div.dataTables_wrapper div.dataTables_filter input:focus {
        border-color: #10b981;
        outline: none;
        box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
    }
    .dataTables_wrapper .page-item .page-link {
        background-color: #05160c !important;
        border-color: rgba(16, 185, 129, 0.2) !important;
        color: #10b981 !important;
    }
    .dataTables_wrapper .page-item.active .page-link {
        background-color: rgba(16, 185, 129, 0.2) !important;
        border-color: rgba(16, 185, 129, 0.5) !important;
        color: #34d399 !important;
    }
    .dataTables_wrapper .page-item.disabled .page-link {
        background-color: #05160c !important;
        border-color: rgba(16, 185, 129, 0.1) !important;
        color: rgba(255, 255, 255, 0.2) !important;
    }
    div.dataTables_wrapper div.dataTables_info,
    div.dataTables_wrapper div.dataTables_length label,
    div.dataTables_wrapper div.dataTables_filter label {
        color: rgba(255, 255, 255, 0.7) !important;
        font-size: 0.85rem;
    }
    .dataTables_wrapper {
        padding: 1rem;
    }
    .dataTables_wrapper .row {
        margin-bottom: 1rem;
    }
</style>
@endsection
