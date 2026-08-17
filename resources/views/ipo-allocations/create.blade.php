@extends('layouts.app')

@section('title', 'Input Hasil Penjatahan: ' . $placement->mitraAccount->owner_name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card stat-node border-0 shadow-lg">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 mb-3 px-4">
                <h5 class="fw-bold text-white ticker-font"><i class="fa-solid fa-hand-holding-dollar me-2 text-emerald-500"></i>HASIL PENJATAHAN {{ $placement->ipo->code }}</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="alert bg-black bg-opacity-40 border border-emerald-900 border-opacity-50 mb-4 p-3 shadow-sm">
                    <small class="text-emerald-500 d-block opacity-75 mb-1">MODAL DIALOKASIKAN:</small>
                    <h4 class="fw-bold text-white ticker-font mb-0">Rp {{ number_format($placement->capital_allocated, 0, ',', '.') }}</h4>
                </div>

                <form action="{{ route('ipo-allocations.store', $placement) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">HARGA IPO FINAL (PER SAHAM) <span class="text-danger">*</span></label>
                        <input type="number" name="final_price_ipo" class="form-control ticker-font" value="{{ $placement->ipo->price }}" required>
                        <div class="form-text text-emerald-500 opacity-50"><i class="fa-solid fa-circle-info me-1"></i>Bisa berubah dari harga estimasi awal.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">JUMLAH LOT YANG DIDAPAT <span class="text-danger">*</span></label>
                        <input type="number" name="lot_allocated" class="form-control ticker-font fs-4 fw-bold text-white" placeholder="0" required autofocus>
                        <div class="form-text fw-bold text-emerald-400 mt-2">
                             Estimasi awal: <span class="ticker-font">{{ number_format($placement->est_lot, 0, ',', '.') }} LOT</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2 pt-4 border-top border-emerald-900 border-opacity-30">
                        <button type="submit" class="btn btn-primary-custom py-2 fw-bold rounded-pill shadow-lg">
                            <i class="fa-solid fa-save me-2"></i>SIMPAN HASIL PENJATAHAN
                        </button>
                        <a href="{{ route('ipos.show', $placement->ipo_id) }}" class="btn btn-outline-primary-custom py-2 rounded-pill btn-sm">BATAL</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
