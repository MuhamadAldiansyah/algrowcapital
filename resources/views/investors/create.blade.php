@extends('layouts.app')

@section('title', 'Tambah Investor Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card stat-node border-0 shadow-lg">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 mb-3 px-4">
                <h5 class="fw-bold mb-0 text-white ticker-font"><i class="fa-solid fa-user-plus me-2 text-emerald-500"></i>FORM INPUT INVESTOR</h5>
            </div>
            <div class="card-body px-4 pb-4">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 small fw-bold">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('investors.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="form-label">NAMA INVESTOR <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="MASUKKAN NAMA LENGKAP" required>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="create_account_toggle" name="create_account" value="1" {{ old('create_account') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-emerald-400" for="create_account_toggle">BUAT AKUN LOGIN SEKALIGUS</label>
                        </div>
                        
                        <div id="new_account_fields" style="display: none;" class="p-3 bg-black bg-opacity-20 rounded border border-emerald-900 border-opacity-30 mb-3">
                            <div class="mb-3">
                                <label for="username" class="form-label">USERNAME LOGIN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" placeholder="Untuk login portal">
                            </div>
                            <div class="mb-2">
                                <label for="password" class="form-label">PASSWORD <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter">
                            </div>
                        </div>
                        
                        <div id="existing_account_field">
                            <label for="user_id" class="form-label">TAUTKAN KE AKUN EKSISTING (OPSIONAL)</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">-- Tidak Ditautkan --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->username }})</option>
                                @endforeach
                            </select>
                            <div class="form-text mt-1">
                                <i class="fa-solid fa-info-circle me-1"></i> Pilih akun user (role: investor) untuk mengizinkan login ke portal investor.
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="total_capital_display" class="form-label">TOTAL MODAL AWAL (RP) <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control fw-bold ticker-font" id="total_capital_display" placeholder="0" required>
                            <input type="hidden" name="total_capital" id="total_capital" value="{{ old('total_capital') }}">
                        </div>
                        <div class="form-text mt-1">
                            <i class="fa-solid fa-info-circle me-1"></i> Modal ini nantinya bisa dialokasikan ke berbagai Akun Mitra.
                        </div>
                    </div>

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-between gap-2 pt-4 border-top border-emerald-900 border-opacity-30">
                        <a href="{{ route('investors.index') }}" class="btn btn-outline-secondary px-4 rounded-pill w-100 w-sm-auto text-center mb-0">
                            <i class="fa-solid fa-arrow-left me-2"></i> KEMBALI
                        </a>
                        <button type="submit" class="btn btn-primary-custom px-5 rounded-pill shadow-sm w-100 w-sm-auto text-center mb-2 mb-sm-0 fw-bold">
                            <i class="fa-solid fa-save me-2"></i> SIMPAN INVESTOR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('create_account_toggle');
        const newFields = document.getElementById('new_account_fields');
        const existingField = document.getElementById('existing_account_field');

        function updateFields() {
            if (toggle.checked) {
                newFields.style.display = 'block';
                existingField.style.display = 'none';
            } else {
                newFields.style.display = 'none';
                existingField.style.display = 'block';
            }
        }

        toggle.addEventListener('change', updateFields);
        updateFields();

        // Rupiah Formatter
        const displayInput = document.getElementById('total_capital_display');
        const hiddenInput = document.getElementById('total_capital');

        function formatRupiah(angka) {
            let number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if(ribuan){
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            
            return rupiah;
        }

        displayInput.addEventListener('input', function(e){
            let rawValue = this.value.replace(/[^0-9]/g, '');
            this.value = formatRupiah(rawValue);
            hiddenInput.value = rawValue;
        });

        if(hiddenInput.value) {
            displayInput.value = formatRupiah(hiddenInput.value);
        }
    });
</script>
@endsection
