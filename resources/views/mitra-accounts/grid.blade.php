@extends('layouts.app')

@section('title', 'Katalog Mitra')

@section('content')
<div class="mb-5">
    <div class="row align-items-center">
        <div class="col-md-6 mb-3 mb-md-0">
            <p class="text-emerald-400 opacity-75 small mb-0"><i class="fa-solid fa-info-circle me-1"></i> Klik pada kotak mitra untuk melihat riwayat record saham mereka secara mendalam.</p>
        </div>
        <div class="col-md-6">
            <form action="{{ route('mitra-accounts.grid') }}" method="GET" class="d-flex gap-2 justify-content-md-end">
                <div class="input-group w-75 shadow-lg">
                    <span class="input-group-text border-emerald-900 border-end-0" style="background: rgba(6, 78, 59, 0.3) !important; color: #10b981 !important;"><i class="fa-solid fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-emerald-900 border-start-0 ticker-font" 
                           style="background: rgba(6, 78, 59, 0.2) !important; color: #ffffff !important;"
                           placeholder="CARI MITRA..." value="{{ $search ?? '' }}">
                </div>
                <button type="submit" class="btn btn-primary-custom px-4 rounded-pill">CARI</button>
            </form>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-4 g-4 mb-4">
    @forelse($mitraAccountsByDevice as $device => $accounts)
    <div class="col">
        <div class="card h-100 border-0 shadow-lg stat-node hover-translate transition-all">
            
            <div class="card-body p-4 text-center">
                <div class="bg-black bg-opacity-40 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 border border-emerald-900 shadow-sm" style="width: 70px; height: 70px;">
                    <i class="fa-solid {{ $device == 'TANPA DEVICE' ? 'fa-box-open' : 'fa-mobile-screen-button' }} fs-2 text-emerald-500"></i>
                </div>
                <h5 class="fw-bold text-white mb-1 ticker-font text-uppercase">{{ $device }}</h5>
                <p class="text-emerald-500 small mb-3 opacity-75 fw-bold">{{ $accounts->count() }} AKUN</p>
                
                <div class="text-start mt-3">
                    <p class="text-emerald-500 small mb-2 opacity-50 fw-bold border-bottom border-emerald-900 pb-1">DAFTAR AKUN:</p>
                    <ul class="list-unstyled mb-0">
                        @foreach($accounts->take(6) as $account)
                        <li class="mb-2 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-user text-emerald-500 opacity-50 me-2 small"></i>
                                <div>
                                    <div class="text-white small fw-bold text-uppercase text-truncate" style="max-width: 120px;" title="{{ $account->owner_name }}">{{ $account->owner_name }}</div>
                                    <div class="text-white-50" style="font-size: 0.65rem;">{{ $account->username }}</div>
                                </div>
                            </div>
                            <a href="{{ route('mitra-accounts.show', $account) }}" class="btn btn-sm btn-outline-emerald py-0 px-2 rounded-pill" style="font-size: 0.7rem;">Lihat</a>
                        </li>
                        @endforeach
                        
                        @if($accounts->count() > 6)
                        <div class="collapse" id="collapseAccounts-{{ $loop->index }}">
                            <div class="custom-scrollbar pe-2 mt-2" style="max-height: 220px; overflow-y: auto;">
                                @foreach($accounts->skip(6) as $account)
                                <li class="mb-2 pt-2 border-top border-emerald-900 border-opacity-30 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-user text-emerald-500 opacity-50 me-2 small"></i>
                                        <div>
                                            <div class="text-white small fw-bold text-uppercase text-truncate" style="max-width: 120px;" title="{{ $account->owner_name }}">{{ $account->owner_name }}</div>
                                            <div class="text-white-50" style="font-size: 0.65rem;">{{ $account->username }}</div>
                                        </div>
                                    </div>
                                    <a href="{{ route('mitra-accounts.show', $account) }}" class="btn btn-sm btn-outline-emerald py-0 px-2 rounded-pill" style="font-size: 0.7rem;">Lihat</a>
                                </li>
                                @endforeach
                            </div>
                        </div>
                        
                        <li class="text-center mt-3">
                            <button class="btn btn-sm w-100 rounded-pill p-2 border-emerald-900 border-opacity-30 text-emerald-500 bg-black bg-opacity-40 hover-emerald transition-all" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAccounts-{{ $loop->index }}" aria-expanded="false" aria-controls="collapseAccounts-{{ $loop->index }}" onclick="this.innerHTML = this.getAttribute('aria-expanded') === 'true' ? '+{{ $accounts->count() - 6 }} akun lainnya <i class=\'fa-solid fa-chevron-down ms-1\'></i>' : 'Tutup <i class=\'fa-solid fa-chevron-up ms-1\'></i>'">
                                +{{ $accounts->count() - 6 }} akun lainnya <i class="fa-solid fa-chevron-down ms-1"></i>
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 w-100 py-4">
        <div class="card stat-node p-5 text-center border-0 shadow-lg align-items-center">
            <i class="fa-solid fa-folder-open fs-1 mb-3 text-emerald-500 opacity-20"></i>
            <h5 class="text-white">Belum ada data mitra</h5>
            <p class="text-emerald-500 opacity-50 small mb-0">Belum ada akun mitra yang terdaftar.</p>
        </div>
    </div>
    @endforelse
</div>

<style>
    .transition-all {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-translate:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.15) !important;
        background: rgba(16, 185, 129, 0.05) !important;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(5, 22, 12, 0.5); 
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, 0.3); 
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(16, 185, 129, 0.8); 
    }
</style>
@endsection
