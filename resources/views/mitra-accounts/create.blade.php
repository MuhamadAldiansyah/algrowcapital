@extends('layouts.app')

@section('title', 'Tambah Akun Mitra')

@section('content')
<div class="card stat-node border-0 shadow-lg mb-5">
    <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 mb-3 px-4">
        <h5 class="fw-bold text-white ticker-font"><i class="fa-solid fa-id-card me-2 text-emerald-500"></i>FORM REGISTRASI AKUN MITRA</h5>
        <p class="text-emerald-500 opacity-50 small">Daftarkan akun broker baru (Stockbit/Ajaib) ke dalam sistem.</p>
    </div>
    <div class="card-body px-4 pb-4">
        <form action="{{ route('mitra-accounts.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <label class="form-label text-white">NAMA PEMILIK AKUN <span class="text-danger">*</span></label>
                    <input type="text" name="owner_name" class="form-control" placeholder="CONTOH: BUDI SANTOSO" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="form-label text-white">PLATFORM <span class="text-danger">*</span></label>
                    <select name="platform" class="form-select" required>
                        <option value="Stockbit">STOCKBIT</option>
                        <option value="Ajaib">AJAIB</option>
                    </select>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="form-label text-white">USERNAME <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-4">
                    <label class="form-label text-white">PASSWORD <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password" id="pass_input" class="form-control" required>
                        <button type="button" class="btn btn-outline-primary-custom" onclick="togglePassword('pass_input', 'pass_icon')">
                            <i id="pass_icon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-4">
                    <label class="form-label text-white">PIN (ENKRIPSI)</label>
                    <div class="input-group">
                        <input type="password" name="pin" id="pin_input" class="form-control">
                        <button type="button" class="btn btn-outline-primary-custom" onclick="togglePassword('pin_input', 'pin_icon')">
                            <i id="pin_icon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <label class="form-label text-white">NIK (OPSIONAL)</label>
                    <input type="text" name="nik" class="form-control">
                </div>
                <div class="col-md-3 mb-4">
                    <label class="form-label text-white">BANK RDN</label>
                    <input type="text" name="bank_rdn" class="form-control" placeholder="CONTOH: BANK JAGO">
                </div>
                <div class="col-md-3 mb-4">
                    <label class="form-label text-white">NOMOR REKENING RDN</label>
                    <input type="text" name="rdn_account" class="form-control">
                </div>
            </div>

            <hr class="my-4 border-emerald-900 border-opacity-30">
            <h6 class="fw-bold mb-4 text-emerald-400 ticker-font"><i class="fa-solid fa-laptop-code me-2"></i>DEVICE & TRACKING</h6>
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <label class="form-label text-white">DEVICE TYPE</label>
                    <input type="text" name="device" class="form-control" placeholder="HP / LAPTOP">
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-12">
                    <label class="form-label text-white">STATUS <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="aktif">AKTIF</option>
                        <option value="nonaktif">NONAKTIF</option>
                    </select>
                </div>
            </div>

            <div class="d-grid gap-3 d-md-flex justify-content-md-between pt-4 border-top border-emerald-900 border-opacity-30">
                <a href="{{ route('mitra-accounts.index') }}" class="btn btn-outline-primary-custom px-4 rounded-pill order-last order-md-first">KEMBALI</a>
                <button type="submit" class="btn btn-primary-custom px-5 rounded-pill shadow-lg order-first order-md-last">
                    <i class="fa-solid fa-save me-2"></i>SIMPAN AKUN MITRA
                </button>
            </div>
        </form>
    </div>
</div>
@section('scripts')
<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
@endsection
