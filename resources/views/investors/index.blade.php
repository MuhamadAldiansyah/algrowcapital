@extends('layouts.app')

@section('title', 'Manajemen Investor')

@section('content')
<div class="card stat-node border-0 shadow-lg">
    <div class="card-header bg-black bg-opacity-20 pt-4 pb-3 d-flex flex-column flex-sm-row justify-content-between align-items-center border-bottom border-emerald-900 mb-3 px-4 gap-3">
        <h5 class="fw-bold mb-0 text-white text-center text-sm-start"><i class="fa-solid fa-users me-2 text-emerald-500"></i>DAFTAR INVESTOR</h5>
        <a href="{{ route('investors.create') }}" class="btn btn-primary-custom btn-sm rounded-pill px-4 w-100 w-sm-auto">
            <i class="fa-solid fa-plus me-1"></i> TAMBAH INVESTOR
        </a>
    </div>
    <div class="card-body px-4">
        <div class="table-responsive">
            <table class="table align-middle text-white">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>INVESTOR</th>
                        <th>TOTAL WALLET</th>
                        <th>TOTAL PROFIT</th>
                        <th>MODAL AKTIF</th>
                        <th>SALDO MENGENDAP</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investors as $index => $investor)
                    <tr class="news-hover">
                        <td class="ps-4">{{ $investors->firstItem() + $index }}</td>
                        <td class="ticker-font">
                            <div class="fw-bold text-white">{{ $investor->name }}</div>
                            <small class="text-emerald-500 opacity-50">{{ $investor->fundings->pluck('placement.ipo_id')->unique()->count() }} EMITEN</small>
                        </td>
                        <td class="ticker-font fw-bold">Rp {{ number_format($investor->total_capital, 0, ',', '.') }}</td>
                        <td class="ticker-font fw-bold text-success">
                            @php
                                $displayProfit = $investor->total_profit;
                                if (strtoupper(trim($investor->name)) !== 'MUHAMAD ALDIANSYAH') {
                                    $displayProfit = $investor->total_profit / 2;
                                } else {
                                    $otherProfits = \App\Models\InvestorTransaction::whereHas('investor', function($q) {
                                        $q->whereRaw('UPPER(TRIM(name)) != ?', ['MUHAMAD ALDIANSYAH']);
                                    })->where('type', 'PROFIT')->sum('amount');
                                    $displayProfit = $investor->total_profit + ($otherProfits / 2);
                                }
                            @endphp
                            Rp {{ number_format($displayProfit, 0, ',', '.') }}
                        </td>
                        <td class="ticker-font">
                            @if($investor->active_deployment > 0)
                                <span class="badge bg-black bg-opacity-40 text-warning border border-warning border-opacity-25 fw-bold">
                                    Rp {{ number_format($investor->active_deployment, 0, ',', '.') }}
                                </span>
                                <div class="x-small text-emerald-500 opacity-50 mt-1">SEDANG DI EMITEN</div>
                            @else
                                <span class="text-emerald-500 opacity-50 small">-</span>
                            @endif
                        </td>
                        <td class="text-emerald-400 fw-bold ticker-font">
                            Rp {{ number_format($investor->available_balance, 0, ',', '.') }}
                        </td>
                        <td>
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('investors.show', $investor) }}" class="btn btn-sm btn-outline-info px-2" title="Detail"><i class="fa-solid fa-eye"></i></a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-success btn-deposit px-2" 
                                        title="Top Up / Tambah Saldo"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#depositModalIndex"
                                        data-name="{{ $investor->name }}"
                                        data-url="{{ route('investors.deposit', $investor) }}">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger btn-withdraw px-2" 
                                        title="Tarik / Kembalikan Saldo"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#withdrawModalIndex"
                                        data-id="{{ $investor->id }}"
                                        data-name="{{ $investor->name }}"
                                        data-balance="{{ $investor->available_balance }}"
                                        data-url="{{ route('investors.withdraw', $investor) }}">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <a href="{{ route('investors.edit', $investor) }}" class="btn btn-sm btn-outline-primary px-2" title="Edit"><i class="fa-solid fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger px-2" title="Hapus" onclick="confirmDeleteInvestor({{ $investor->id }}, '{{ addslashes($investor->name) }}')"><i class="fa-solid fa-trash"></i></button>
                                <form id="delete-investor-{{ $investor->id }}" action="{{ route('investors.destroy', $investor->id) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-emerald-500 opacity-50 py-5">
                            <i class="fa-solid fa-user-slash fa-3x mb-3 d-block"></i>
                            BELUM ADA DATA INVESTOR TERDAFTAR.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-3">
            {{ $investors->links() }}
        </div>
    </div>
</div>

<!-- Global Withdraw Modal -->
<div class="modal fade" id="withdrawModalIndex" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <form action="" method="POST" id="withdrawForm" class="w-100">
            @csrf
            <div class="modal-content stat-node border-0 shadow-lg rounded-4" style="background: #05160c !important;">
                <div class="modal-header border-bottom border-emerald-900 pb-3">
                    <h5 class="modal-title fw-bold text-white">TARIK SALDO: <span id="investorName" class="text-emerald-400"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4 text-center bg-black bg-opacity-40 p-3 rounded-3 border border-emerald-900">
                        <small class="text-emerald-500 d-block mb-1 opacity-75">SALDO TERSEDIA</small>
                        <h3 class="fw-bold text-white ticker-font mb-0" id="availableBalanceLabel">Rp 0</h3>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">JUMLAH PENARIKAN (RP)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-black bg-opacity-40 border-emerald-900 text-emerald-500">Rp</span>
                            <input type="text" id="withdrawAmountMaskIdx" class="form-control" placeholder="0" inputmode="numeric" required>
                            <input type="hidden" name="amount" id="withdrawAmountIdx" required min="1">
                        </div>
                    </div>
                    

                </div>
                <div class="modal-footer border-top border-emerald-900 pt-4 pb-3 px-4 d-flex flex-column-reverse flex-sm-row justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill w-100 w-sm-auto mb-0" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-pill fw-bold w-100 w-sm-auto mb-2 mb-sm-0">PROSES PENARIKAN</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Global Deposit Modal -->
<div class="modal fade" id="depositModalIndex" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <form action="" method="POST" id="depositForm" class="w-100">
            @csrf
            <div class="modal-content stat-node border-0 shadow-lg rounded-4" style="background: #05160c !important;">
                <div class="modal-header border-bottom border-emerald-900 pb-3">
                    <h5 class="modal-title fw-bold text-white">TOP UP SALDO: <span id="depositInvestorName" class="text-emerald-400"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-emerald-500 small fw-bold">JUMLAH DEPOSIT / TOP UP (RP)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-black bg-opacity-40 border-emerald-900 text-emerald-500">Rp</span>
                            <input type="text" id="depositAmountMaskIdx" class="form-control" placeholder="1.000.000" inputmode="numeric" required>
                            <input type="hidden" name="amount" id="depositAmountIdx" required min="1">
                        </div>
                        <small class="text-emerald-500 opacity-50">Dana ini akan ditambahkan ke saldo Wallet Investor.</small>
                    </div>
                </div>
                <div class="modal-footer border-top border-emerald-900 pt-4 pb-3 px-4 d-flex flex-column-reverse flex-sm-row justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill w-100 w-sm-auto mb-0" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-success px-4 rounded-pill fw-bold text-white w-100 w-sm-auto mb-2 mb-sm-0">PROSES TOP UP</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        function setupMask(maskId, realId) {
            const maskInput = document.getElementById(maskId);
            const realInput = document.getElementById(realId);
            if(!maskInput) return;
            
            maskInput.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                realInput.value = value;
                if(value) {
                    this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                } else {
                    this.value = '';
                }
            });
            
            if(realInput.value) {
                maskInput.value = realInput.value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        }

        setupMask('depositAmountMaskIdx', 'depositAmountIdx');
        setupMask('withdrawAmountMaskIdx', 'withdrawAmountIdx');

        $('.btn-withdraw').on('click', function() {
            const name = $(this).data('name');
            const balance = parseFloat($(this).data('balance'));
            const url = $(this).data('url');

            $('#withdrawForm').attr('action', url);
            $('#withdrawForm').data('max-balance', balance);
            $('#investorName').text(name);
            $('#availableBalanceLabel').text('Rp ' + balance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
            
            // Reset modal input state when opened
            $('#withdrawAmountMaskIdx').val('');
            $('#withdrawAmountIdx').val('');
        });

        $('#withdrawForm').on('submit', function(e) {
            const withdrawAmount = parseInt($('#withdrawAmountIdx').val(), 10) || 0;
            const maxBalance = parseFloat($(this).data('max-balance')) || 0;
            
            if (withdrawAmount > maxBalance) {
                e.preventDefault();
                const formattedMax = maxBalance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                Swal.fire({
                    icon: 'error',
                    title: 'Saldo Tidak Cukup',
                    text: 'Jumlah penarikan melebihi sisa saldo yang tersedia (Rp ' + formattedMax + ').',
                    background: '#05160c',
                    color: '#ffffff',
                    confirmButtonColor: '#10b981'
                });
            }
        });

        $('.btn-deposit').on('click', function() {
            const name = $(this).data('name');
            const url = $(this).data('url');

            $('#depositForm').attr('action', url);
            $('#depositInvestorName').text(name);
            
            // Reset modal input state when opened
            $('#depositAmountMaskIdx').val('');
            $('#depositAmountIdx').val('');
        });

        window.confirmDeleteInvestor = function(id, name) {
            Swal.fire({
                title: 'Hapus Investor?',
                text: `Anda yakin ingin menghapus investor ${name}? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#05160c',
                color: '#ffffff'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-investor-' + id).submit();
                }
            });
        };
    });
</script>
<style>
    .news-hover { transition: background-color 0.2s; }
    .news-hover:hover { background-color: rgba(16, 185, 129, 0.05); }
    .table > :not(caption) > * > * { color: inherit; }
</style>
@endsection
