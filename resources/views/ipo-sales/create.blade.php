@extends('layouts.app')

@section('title', 'Input Harga Jual: ' . $ipo->code)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card stat-node border-0 shadow-lg">
            <div class="card-header bg-black bg-opacity-20 pt-5 pb-4 border-bottom border-emerald-900 text-center">
                <i class="fa-solid fa-money-bill-trend-up fs-1 text-emerald-400 mb-3 glow-text-emerald"></i>
                <h4 class="fw-bold text-white ticker-font mb-0">REALISASI PENJUALAN {{ $ipo->code }}</h4>
            </div>
            <div class="card-body p-4">
                @php
                    $totalLots = 0;
                    $totalUsed = 0;
                    foreach($ipo->placements as $p) {
                        if($p->allocation) {
                            $totalLots += $p->allocation->lot_allocated;
                            $totalUsed += $p->allocation->total_used;
                        }
                    }
                @endphp

                <div class="bg-black bg-opacity-40 p-3 rounded-3 border border-emerald-900 border-opacity-50 mb-4 shadow-inner">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-emerald-500 opacity-75">TOTAL JATAH LOT:</small>
                        <span class="fw-bold text-white ticker-font">{{ number_format($totalLots, 0, ',', '.') }} LOT</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-emerald-500 opacity-75">TOTAL MODAL TERPAKAI:</small>
                        <span class="fw-bold text-emerald-400 ticker-font">Rp {{ number_format($totalUsed, 0, ',', '.') }}</span>
                    </div>
                </div>

                <form action="{{ route('ipo-sales.store', $ipo) }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label class="form-label text-emerald-500 fw-bold small opacity-75">HARGA JUAL AKHIR (PER SAHAM)</label>
                        <div class="input-group input-group-lg shadow-sm">
                            <input type="number" name="sell_price" class="form-control bg-black bg-opacity-10 border-emerald-900 text-white ticker-font fs-2 fw-bold" placeholder="0" required autofocus>
                        </div>
                        <div class="form-text mt-3 text-center text-emerald-500 opacity-50 px-3">
                            Sistem akan otomatis menghitung <strong class="text-white">Total Return</strong> dan <strong class="text-white">Net Profit</strong> untuk seluruh akun yang terlibat.
                        </div>
                    </div>

                    <div class="d-grid gap-3 pt-2">
                        <button type="submit" class="btn btn-primary-custom py-3 fw-bold rounded-pill shadow-lg">
                            <i class="fa-solid fa-calculator me-2"></i>SIMPAN & HITUNG PROFIT
                        </button>
                        <a href="{{ route('ipos.show', $ipo->id) }}" class="btn btn-outline-primary-custom py-2 rounded-pill btn-sm">BATAL</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
