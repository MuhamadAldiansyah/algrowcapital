@extends('layouts.app')

@section('title', 'Edit Event IPO: ' . $ipo->code)

@section('content')
        <div class="card stat-node border-0 shadow-lg">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 mb-3 px-4">
                <h5 class="fw-bold text-white ticker-font"><i class="fa-solid fa-pen-to-square me-2 text-emerald-500"></i>EDIT DATA SAHAM IPO</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('ipos.update', $ipo) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-8 mb-4">
                            <label class="form-label">NAMA PERUSAHAAN / SAHAM <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $ipo->name }}" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label">GAMBAR / LOGO EMITEN</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            @if($ipo->image_path)
                                <div class="mt-2">
                                    <small class="text-emerald-500 d-block mb-1">Logo saat ini:</small>
                                    <img src="{{ Storage::url($ipo->image_path) }}" alt="{{ $ipo->code }}" class="img-thumbnail bg-black border-emerald-900" style="max-height: 50px;">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">KODE SAHAM (TICKER) <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control fw-bold ticker-font" value="{{ $ipo->code }}" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">HARGA IPO ESTIMASI (RP) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control ticker-font" value="{{ $ipo->price }}" required>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">TANGGAL IPO / TANGGAL LISTING <span class="text-danger">*</span></label>
                        <input type="date" name="ipo_date" class="form-control" value="{{ date('Y-m-d', strtotime($ipo->ipo_date)) }}" required style="color-scheme: dark;">
                    </div>

                    <div class="d-flex justify-content-between pt-4 gap-2 border-top border-emerald-900 border-opacity-30">
                        <a href="{{ route('ipos.index') }}" class="btn btn-outline-secondary px-3 rounded-pill w-50 w-sm-auto text-center mb-0">BATAL</a>
                        <button type="submit" class="btn btn-primary-custom px-3 rounded-pill shadow-lg fw-bold w-50 w-sm-auto text-center mb-0">
                            <i class="fa-solid fa-save me-1"></i> UPDATE
                        </button>
                    </div>
                </form>
            </div>
        </div>
@endsection
