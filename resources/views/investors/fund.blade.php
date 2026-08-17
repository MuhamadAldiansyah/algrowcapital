@extends('layouts.app')

@section('title', 'Modalin Akun IPO: ' . $investor->name)

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 text-center">
                <i class="fa-solid fa-wallet fs-1 text-primary-custom mb-3"></i>
                <h5 class="fw-bold fs-4 mb-1 text-primary-custom">Rp {{ number_format($investor->total_capital, 0, ',', '.') }}</h5>
                <p class="text-muted small">Total Modal Tersedia pada Investor</p>
                <hr>
                <div class="alert alert-info py-2 text-start small">
                    <i class="fa-solid fa-info-circle me-1"></i> Pilih akun mitra yang akan Anda modali untuk event IPO tertentu.
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                <h5 class="fw-bold">Pilih Placement & Input Modal</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('investors.store-fund', $investor) }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th>IPO & Kode</th>
                                    <th>Akun Mitra</th>
                                    <th style="width: 200px;">Modal Donor (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availablePlacements as $index => $placement)
                                <tr>
                                    <td>
                                        <span class="badge bg-dark">{{ $placement->ipo->code }}</span>
                                        <div class="small text-muted mt-1">{{ $placement->ipo->name }}</div>
                                        <input type="hidden" name="placements[{{ $index }}][id]" value="{{ $placement->id }}">
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $placement->mitraAccount->owner_name }}</div>
                                        <small class="text-muted">{{ $placement->mitraAccount->platform }}</small>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="placements[{{ $index }}][amount]" class="form-control" value="{{ $placement->capital_allocated }}" placeholder="0">
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada antrean placement IPO yang tersedia.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($availablePlacements->count() > 0)
                    <div class="mt-4 d-grid gap-2">
                        <button type="submit" class="btn btn-primary-custom py-2 fw-bold">Simpan Pendanaan</button>
                    </div>
                    @endif
                    <div class="mt-3 text-center">
                        <a href="{{ route('investors.show', $investor) }}" class="btn btn-light border btn-sm">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
