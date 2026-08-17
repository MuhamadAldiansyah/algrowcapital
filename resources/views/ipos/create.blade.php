@extends('layouts.app')

@section('title', 'Tambah Event IPO Baru')

@section('content')
        <div class="card stat-node border-0 shadow-lg">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 mb-3 px-4">
                <h5 class="fw-bold text-white ticker-font"><i class="fa-solid fa-rocket me-2 text-emerald-500"></i>FORM DATA SAHAM IPO</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('ipos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8 mb-4">
                            <label class="form-label text-white">NAMA PERUSAHAAN / SAHAM <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="CONTOH: PT BANK DIGITAL INDONESIA" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label text-white">GAMBAR / LOGO EMITEN</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label text-white">KODE SAHAM (TICKER) <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control fw-bold ticker-font" placeholder="CONTOH: ABCD" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label text-white">HARGA IPO ESTIMASI (RP) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control ticker-font" placeholder="0" required>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-white">TANGGAL IPO / TANGGAL LISTING <span class="text-danger">*</span></label>
                        <input type="date" name="ipo_date" class="form-control" required style="color-scheme: dark;">
                    </div>

                    <div class="d-flex justify-content-between pt-4 gap-2 border-top border-emerald-900 border-opacity-30">
                        <a href="{{ route('ipos.index') }}" class="btn btn-outline-secondary px-3 rounded-pill w-50 w-sm-auto text-center mb-0">BATAL</a>
                        <button type="submit" class="btn btn-primary-custom px-3 rounded-pill shadow-lg fw-bold w-50 w-sm-auto text-center mb-0">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> SIMPAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
@endsection
