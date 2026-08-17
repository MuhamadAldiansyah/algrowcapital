@extends('layouts.app')

@section('title', 'Mutasi Wallet: ' . $investor->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-0 ticker-font"><i class="fa-solid fa-file-invoice-dollar me-2 text-emerald-400"></i>MUTASI WALLET</h4>
        <small class="text-white opacity-75">Riwayat Transaksi Investor: <span class="fw-bold text-emerald-400">{{ $investor->name }}</span></small>
    </div>
    <a href="{{ route('investors.show', $investor) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
        <i class="fa-solid fa-arrow-left me-1"></i> KEMBALI
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-node border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle bg-emerald-900 bg-opacity-30 p-3 me-3">
                    <i class="fa-solid fa-wallet fs-3 text-emerald-400"></i>
                </div>
                <div>
                    <small class="text-white opacity-75 d-block fw-bold" style="font-size: 0.75rem;">SALDO AKTIF SAAT INI</small>
                    <strong class="fs-4 text-white ticker-font">Rp {{ number_format($investor->available_balance, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-node border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle bg-black bg-opacity-40 border border-emerald-900 p-3 me-3">
                    <i class="fa-solid fa-arrow-down fs-3 text-white opacity-75"></i>
                </div>
                <div>
                    <small class="text-white opacity-75 d-block fw-bold" style="font-size: 0.75rem;">TOTAL DEPOSIT</small>
                    <strong class="fs-4 text-white ticker-font">Rp {{ number_format($investor->transactions()->where('type', 'DEPOSIT')->sum('amount'), 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-node border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle bg-black bg-opacity-40 border border-emerald-900 p-3 me-3">
                    <i class="fa-solid fa-arrow-up fs-3 text-white opacity-75"></i>
                </div>
                <div>
                    <small class="text-white opacity-75 d-block fw-bold" style="font-size: 0.75rem;">TOTAL PENARIKAN</small>
                    <strong class="fs-4 text-white ticker-font">Rp {{ number_format($investor->transactions()->where('type', 'WITHDRAW')->sum('amount'), 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card stat-node border-0 shadow-lg p-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 datatable" style="font-size: 0.85rem;">
                <thead class="bg-black bg-opacity-40">
                    <tr>
                        <th class="border-emerald-900 border-opacity-30 text-white opacity-75 py-3 px-4">TANGGAL</th>
                        <th class="border-emerald-900 border-opacity-30 text-white opacity-75 py-3 px-4">KETERANGAN</th>
                        <th class="border-emerald-900 border-opacity-30 text-white opacity-75 py-3 px-4">JENIS</th>
                        <th class="border-emerald-900 border-opacity-30 text-end text-white opacity-75 py-3 px-4">NOMINAL (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr class="border-bottom border-emerald-900 border-opacity-20">
                        <td class="px-4 text-white">
                            {{ $tx->created_at->format('d M Y') }}<br>
                            <small class="opacity-50">{{ $tx->created_at->format('H:i:s') }}</small>
                        </td>
                        <td class="px-4 text-white">
                            {{ $tx->description ?: '-' }}
                        </td>
                        <td class="px-4">
                            @if($tx->type === 'DEPOSIT')
                                <span class="badge bg-emerald-900 bg-opacity-30 text-emerald-400 border border-emerald-900"><i class="fa-solid fa-arrow-down me-1"></i> DEPOSIT</span>
                            @elseif($tx->type === 'WITHDRAW')
                                <span class="badge bg-black bg-opacity-40 text-white opacity-75 border border-emerald-900"><i class="fa-solid fa-arrow-up me-1"></i> WITHDRAW</span>
                            @elseif($tx->type === 'PROFIT')
                                <span class="badge bg-emerald-900 bg-opacity-30 text-emerald-400 border border-emerald-900"><i class="fa-solid fa-chart-line me-1"></i> PROFIT</span>
                            @else
                                <span class="badge bg-secondary">{{ $tx->type }}</span>
                            @endif
                        </td>
                        <td class="px-4 text-end ticker-font fw-bold fs-6">
                            @if($tx->type === 'WITHDRAW')
                                <span class="text-white opacity-75">- {{ number_format($tx->amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-emerald-400">+ {{ number_format($tx->amount, 0, ',', '.') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-white opacity-50">
                            <i class="fa-solid fa-folder-open d-block fs-2 mb-2"></i>
                            Belum ada riwayat transaksi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($('.datatable').length) {
            $('.datatable').DataTable({
                "pageLength": 25,
                "ordering": false,
                "language": {
                    "search": "Cari Transaksi:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            });
        }
    });
</script>
@endsection
