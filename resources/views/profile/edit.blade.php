@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-lg" style="border-radius: 15px;">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-3 border-bottom border-emerald-900 px-4">
                <h5 class="fw-bold mb-0 text-white">
                    <i class="fa-solid fa-user-pen me-2 text-emerald-500"></i> Edit Profil Data Diri
                </h5>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('my-profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-emerald-500 small fw-bold">NAMA LENGKAP</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-emerald-500 small fw-bold">USERNAME</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-white border-bottom border-emerald-900 pb-2 mb-3">Informasi Sekuritas & Bank</h6>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-emerald-500 small fw-bold">SEKURITAS YANG DIPAKAI (PLATFORM)</label>
                            <input type="text" name="sekuritas" class="form-control" value="{{ old('sekuritas', $user->sekuritas) }}" placeholder="Contoh: Ajaib, IPOT, dll">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-emerald-500 small fw-bold">PASSWORD SEKURITAS</label>
                            <input type="text" name="password_sekuritas" class="form-control" value="{{ old('password_sekuritas', $user->password_sekuritas) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-emerald-500 small fw-bold">PIN SEKURITAS</label>
                            <input type="text" name="pin_sekuritas" class="form-control" value="{{ old('pin_sekuritas', $user->pin_sekuritas) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-emerald-500 small fw-bold">NAMA BANK</label>
                            <input type="text" name="bank" class="form-control" value="{{ old('bank', $user->bank) }}" placeholder="Contoh: BCA, Mandiri, dll">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-emerald-500 small fw-bold">NOMOR REKENING</label>
                            <input type="text" name="no_rek" class="form-control" value="{{ old('no_rek', $user->no_rek) }}">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top border-emerald-900 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary-custom px-4">
                            <i class="fa-solid fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
