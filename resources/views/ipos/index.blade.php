@extends('layouts.app')

@section('title', 'Manajemen Event IPO')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
    <div>
        <p class="text-white opacity-75 small mb-0">Kelola pesanan dan pantau profit saham IPO Anda secara real-time.</p>
    </div>
    <a href="{{ route('ipos.create') }}" class="btn btn-primary-custom btn-sm d-sm-none px-3 shadow-sm w-100 rounded-pill">
        <i class="fa-solid fa-plus me-1"></i> TAMBAH EVENT IPO
    </a>
    <a href="{{ route('ipos.create') }}" class="btn btn-primary-custom d-none d-sm-inline-block px-4 shadow-sm rounded-pill">
        <i class="fa-solid fa-plus me-1"></i> TAMBAH EVENT IPO
    </a>
</div>

<div class="mobile-scroll-wrapper">
    <div class="mobile-scroll-inner">
        <div class="row row-cols-3 g-4">
            @forelse($ipos as $ipo)
            <div class="col">
                <div class="card h-100 border-0 shadow-lg stat-node hover-translate transition-all">
            <!-- Header Section -->
            <div class="card-header bg-black bg-opacity-20 border-bottom border-emerald-900 pt-4 px-4 d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center gap-3">
                    @if($ipo->image_path)
                        <img src="{{ Storage::url($ipo->image_path) }}" alt="{{ $ipo->code }}" class="rounded bg-white p-1" style="width: 50px; height: 50px; object-fit: contain;">
                    @else
                        <div class="rounded bg-emerald-900 d-flex align-items-center justify-content-center text-emerald-400 fw-bold" style="width: 50px; height: 50px; flex-shrink: 0;">
                            {{ substr($ipo->code, 0, 2) }}
                        </div>
                    @endif
                    <div>
                        <span class="badge bg-{{ $ipo->status_color }} bg-opacity-20 text-white border border-{{ $ipo->status_color }} border-opacity-25 mb-2 px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.65rem;">
                            STEP {{ $ipo->step }}: {{ strtoupper($ipo->status_label) }}
                        </span>
                        <h5 class="fw-bold text-white mb-0 d-block ticker-font">{{ $ipo->code }}</h5>
                        <small class="text-white opacity-75">{{ $ipo->name }}</small>
                    </div>
                </div>
                @if($ipo->canEdit() || $ipo->canDelete())
                <div class="dropdown">
                    <button class="btn btn-link text-white opacity-75 p-0 border-0 shadow-none hover-emerald" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-emerald-900 bg-emerald-950">
                        @if($ipo->canEdit())
                        <li><a class="dropdown-item" href="{{ route('ipos.edit', $ipo) }}"><i class="fa-solid fa-edit me-2 small"></i> Edit Data</a></li>
                        @if($ipo->step < 4)
                        <li>
                            <button type="button" class="dropdown-item text-warning btn-reset-ipo" data-url="{{ route('ipos.reset-all', $ipo) }}" data-code="{{ $ipo->code }}">
                                <i class="fa-solid fa-rotate-left me-2 small"></i> Reset Semua Data
                            </button>
                        </li>
                        @endif
                        @endif
                        
                        @if($ipo->canEdit() && $ipo->canDelete())
                        <li><hr class="dropdown-divider border-emerald-900"></li>
                        @endif

                        @if($ipo->canDelete())
                        <li>
                            <form action="{{ route('ipos.destroy', $ipo) }}" method="POST" onsubmit="confirmDelete(event, 'Hapus data IPO ini? Semua data histori terkait akan ikut terhapus.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-trash me-2 small"></i> Hapus</button>
                            </form>
                        </li>
                        @endif
                    </ul>
                </div>
                @endif
            </div>

            <div class="card-body px-4 py-3">
                <!-- Progress Stepper -->
                <div class="mb-4 mt-2">
                    <div class="progress bg-black bg-opacity-40" style="height: 6px;">
                        <div class="progress-bar glow-bg-emerald" role="progressbar" style="width: {{ ($ipo->step / 4) * 100 }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small text-white opacity-50" style="font-size: 0.65rem;">
                        <span class="{{ $ipo->step >= 1 ? 'fw-bold text-emerald-400 opacity-100' : '' }}">SETUP</span>
                        <span class="{{ $ipo->step >= 2 ? 'fw-bold text-emerald-400 opacity-100' : '' }}">JATAH</span>
                        <span class="{{ $ipo->step >= 3 ? 'fw-bold text-emerald-400 opacity-100' : '' }}">JUAL</span>
                        <span class="{{ $ipo->step == 4 ? 'fw-bold text-emerald-400 opacity-100' : '' }}">DONE</span>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-black bg-opacity-40 p-2 rounded-3 border border-emerald-900 text-center">
                            <small class="text-white d-block opacity-75" style="font-size: 0.65rem;">HARGA IPO</small>
                            <span class="fw-bold text-white ticker-font">Rp {{ number_format($ipo->price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-black bg-opacity-40 p-2 rounded-3 border border-emerald-900 text-center">
                            <small class="text-white d-block opacity-75" style="font-size: 0.65rem;">TOTAL MODAL</small>
                            <span class="fw-bold text-white ticker-font">Rp {{ number_format($ipo->placements->sum('capital_allocated'), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between small text-white opacity-75 mb-2">
                    <span>Partisipasi:</span>
                    <span class="text-white fw-bold">{{ $ipo->placements->count() }} Akun Mitra</span>
                </div>
            </div>

            <div class="card-footer bg-transparent border-0 px-4 pb-4">
                @if($ipo->step == 1)
                    <a href="{{ route('ipos.show', $ipo) }}" class="btn btn-primary-custom w-100 py-2 fw-bold rounded-pill">
                        <i class="fa-solid fa-plus me-2"></i>MULAI ALOKASI MODAL
                    </a>
                @elseif($ipo->step == 2)
                    <a href="{{ route('ipos.show', $ipo) }}" class="btn btn-warning text-dark w-100 py-2 fw-bold rounded-pill shadow-sm">
                        <i class="fa-solid fa-hand-holding-dollar me-2"></i>ISI PENJATAHAN
                    </a>
                @elseif($ipo->step == 3)
                    <a href="{{ route('ipos.show', $ipo) }}" class="btn btn-success text-white w-100 py-2 fw-bold rounded-pill glow-bg-emerald-alt">
                        <i class="fa-solid fa-money-bill-trend-up me-2"></i>REALISASI JUAL
                    </a>
                @else
                    <a href="{{ route('ipos.show', $ipo) }}" class="btn btn-outline-primary-custom w-100 py-2 fw-bold rounded-pill">
                        <i class="fa-solid fa-check-circle me-2"></i>RIWAYAT & SELESAI
                    </a>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 py-5 text-center">
        <div class="card stat-node p-5 border-0 shadow-lg align-items-center">
            <i class="fa-solid fa-folder-open fs-1 mb-3 text-emerald-500 opacity-20"></i>
            <h5 class="text-white">Belum ada event IPO yang dibuat.</h5>
            <p class="text-white opacity-75 small">Mulai tambahkan emiten baru untuk membagi alokasi modal investor.</p>
        </div>
    </div>
    @endforelse
        </div>
    </div>
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $ipos->links() }}
</div>

<style>
    @media (max-width: 767.98px) {
        .mobile-scroll-wrapper {
            width: 100%;
            max-width: 100vw;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 1rem;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .mobile-scroll-wrapper::-webkit-scrollbar {
            display: none;
        }
        .mobile-scroll-inner {
            width: 900px; /* Forces exactly 3 cards width */
        }
    }
    .hover-translate:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.15) !important;
    }
    .hover-emerald:hover {
        background-color: rgba(16, 185, 129, 0.1) !important;
        color: #10b981 !important;
    }
    .glow-bg-emerald-alt {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        border: none !important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.btn-reset-ipo').on('click', function() {
        const url = $(this).data('url');
        const code = $(this).data('code');
        
        Swal.fire({
            title: 'RESET TOTAL: ' + code + '?',
            text: 'Seluruh data Modal, Jatah, Jual, dan Profit pada IPO ini akan dihapus permanen. Uang investor akan kembali ke saldo mereka. Anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#3f3f46',
            confirmButtonText: 'Ya, Reset Total!',
            cancelButtonText: 'Batal',
            background: '#05160c',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Meriset Data...', allowOutsideClick: false, background: '#05160c', color: '#fff', didOpen: () => { Swal.showLoading(); }});
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            background: '#05160c',
                            color: '#fff',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let msg = 'Gagal meriset data.';
                        if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: msg, background: '#05160c', color: '#fff' });
                    }
                });
            }
        });
    });
});
</script>
@endsection
