@extends('layouts.app')

@section('title', 'Isi Penjatahan Lot: ' . $ipo->code)

@section('content')
<div class="row">
    <div class="col-12 col-xl-10 mx-auto">
        <div class="card stat-node border-0 shadow-lg">
            <div class="card-header bg-warning bg-opacity-20 pt-4 pb-3 border-bottom border-warning border-opacity-50 text-center">
                <i class="fa-solid fa-list-ol fs-2 text-warning mb-3"></i>
                <h4 class="fw-bold text-white mb-0">INPUT PENJATAHAN LOT <span class="text-warning ticker-font">{{ $ipo->code }}</span></h4>
                <p class="text-warning opacity-75 small mb-0 mt-2">Silakan isi jumlah lot yang didapatkan untuk masing-masing akun Anda.</p>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('user-tasks.store-allotment', $ipo) }}" method="POST">
                    @csrf
                    
                    <div class="bg-black bg-opacity-30 p-4 rounded-3 border border-emerald-900 mb-4 shadow-inner">
                        <label class="form-label text-emerald-500 fw-bold small opacity-75">HARGA FINAL IPO (PER SAHAM)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-black bg-opacity-20 border-emerald-900 text-emerald-400 fw-bold">Rp</span>
                            <input type="number" name="final_price_ipo" class="form-control bg-black bg-opacity-10 border-emerald-900 border-start-0 text-white ticker-font fw-bold" value="{{ old('final_price_ipo', $ipo->price) }}" required>
                        </div>
                        <div class="form-text mt-2 text-emerald-500 opacity-50">Pastikan Harga Final IPO sesuai dengan harga penetapan resmi.</div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3 text-emerald-500">NAMA AKUN</th>
                                    <th class="text-emerald-500">MODAL DISETOR</th>
                                    <th class="text-emerald-500" style="width: 200px;">LOT DIDAPAT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($placements as $index => $placement)
                                <tr style="border-bottom: 1px solid rgba(16, 185, 129, 0.1);">
                                    <td class="ps-3 py-3">
                                        <input type="hidden" name="allocations[{{ $index }}][placement_id]" value="{{ $placement->id }}">
                                        <div class="fw-bold text-white">{{ strtoupper($placement->mitraAccount->owner_name) }}</div>
                                        <div class="small text-muted">{{ $placement->mitraAccount->username }} &bull; {{ $placement->mitraAccount->platform }}</div>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold text-emerald-400 ticker-font">Rp {{ number_format($placement->capital_allocated, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="py-3">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="allocations[{{ $index }}][lot_allocated]" class="form-control bg-black text-white border-emerald-900 text-end ticker-font fw-bold" placeholder="0" required min="0">
                                            <span class="input-group-text bg-emerald-900 bg-opacity-30 border-emerald-900 text-emerald-400">LOT</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-grid gap-3 d-md-flex justify-content-md-end pt-2">
                        <a href="{{ route('user-tasks.index') }}" class="btn btn-outline-danger px-4 rounded-pill fw-bold">BATAL</a>
                        <button type="submit" class="btn btn-warning px-5 rounded-pill fw-bold text-black shadow-lg">
                            <i class="fa-solid fa-save me-2"></i> SIMPAN PENJATAHAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
