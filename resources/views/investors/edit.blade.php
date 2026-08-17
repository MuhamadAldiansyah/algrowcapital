@extends('layouts.app')

@section('title', 'Edit Investor: ' . $investor->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card stat-node border-0 shadow-lg">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 mb-3 px-4">
                <h5 class="fw-bold text-white ticker-font"><i class="fa-solid fa-user-pen me-2 text-emerald-500"></i>EDIT DATA INVESTOR</h5>
                <p class="text-emerald-500 opacity-50 small">Perbarui profil atau modal dasar investor.</p>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('investors.update', $investor) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="form-label">NAMA INVESTOR</label>
                        <input type="text" name="name" class="form-control" value="{{ $investor->name }}" required>
                    </div>

                    <div class="mb-4">
                        <label for="user_id" class="form-label">TAUTKAN KE AKUN (OPSIONAL)</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">-- Tidak Ditautkan --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (old('user_id') ?? $investor->user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->username }})</option>
                            @endforeach
                        </select>
                        <div class="form-text mt-1">
                            <i class="fa-solid fa-info-circle me-1"></i> Pilih akun user (role: investor) untuk mengizinkan login ke portal investor.
                        </div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">TOTAL MODAL (RP)</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="total_capital" class="form-control ticker-font fw-bold" value="{{ $investor->total_capital }}" required>
                        </div>
                        <div class="form-text mt-1">
                            <i class="fa-solid fa-info-circle me-1"></i> Modal dasar yang dititipkan investor ke pool.
                        </div>
                    </div>
                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-between gap-2 pt-4 border-top border-emerald-900 border-opacity-30">
                        <a href="{{ route('investors.index') }}" class="btn btn-outline-secondary px-4 rounded-pill w-100 w-sm-auto text-center mb-0">
                            <i class="fa-solid fa-arrow-left me-2"></i> KEMBALI
                        </a>
                        <button type="submit" class="btn btn-primary-custom px-5 rounded-pill shadow-sm w-100 w-sm-auto text-center mb-2 mb-sm-0 fw-bold">
                            <i class="fa-solid fa-save me-2"></i> PERBARUI INVESTOR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
