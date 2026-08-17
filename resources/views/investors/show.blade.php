@extends('layouts.app')

@section('title', 'Detail Investor: ' . $investor->name)

@section('content')
<div class="row g-4 mb-5">
    <div class="col-12 col-lg-4">
        <!-- Profile Card -->
        <div class="card stat-node border-0 shadow-lg mb-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <span class="badge badge-glow-emerald px-3 py-2 mb-3">INVESTOR #{{ $investor->id }}</span>
                    <h4 class="fw-bold mb-1 text-white ticker-font text-uppercase">{{ $investor->name }}</h4>
                    <div class="p-3 bg-black bg-opacity-40 rounded-3 border border-emerald-900 mt-3">
                        <small class="text-white d-block mb-1 opacity-75">SALDO WALLET (BASIS)</small>
                        <strong class="fs-4 text-white ticker-font">Rp {{ number_format($investor->total_capital, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <hr class="border-emerald-900 border-opacity-30">
                <div class="d-grid gap-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-primary-custom w-100 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#depositModal">
                                <i class="fa-solid fa-arrow-down me-1"></i> DEPOSIT
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary text-white w-100 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                                <i class="fa-solid fa-arrow-up me-1"></i> TARIK
                            </button>
                        </div>
                    </div>
                    
                    <a href="{{ route('investors.transactions', $investor) }}" class="btn btn-outline-secondary text-white shadow-sm rounded-pill mt-1">
                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> LIHAT MUTASI
                    </a>

                    @if(Auth::user()->role === 'admin' || (Auth::user()->role === 'investor' && Auth::user()->id === $investor->user_id))
                    <button type="button" class="btn btn-outline-secondary shadow-sm rounded-pill mt-1" data-bs-toggle="modal" data-bs-target="#updateAccountModal">
                        <i class="fa-solid fa-user-lock me-1"></i> UBAH AKUN LOGIN
                    </button>
                    @endif
                </div>
            </div>
        </div>




        <!-- Deposit Modal -->
        <div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('investors.deposit', $investor) }}" method="POST">
                    @csrf
                    <div class="modal-content stat-node border-0 shadow-lg" style="background: #05160c !important;">
                        <div class="modal-header border-bottom border-emerald-900 p-4">
                            <h5 class="modal-title fw-bold text-white ticker-font"><i class="fa-solid fa-arrow-down text-emerald-400 me-2"></i>DEPOSIT SALDO</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label text-white opacity-75 small fw-bold">PILIH NOMINAL CEPAT</label>
                                <div class="row g-2 mb-2">
                                    <div class="col-4"><button type="button" class="btn btn-outline-secondary w-100 btn-sm ticker-font" onclick="fillAmount('depositAmount', 1000000)">1 JT</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-outline-secondary w-100 btn-sm ticker-font" onclick="fillAmount('depositAmount', 5000000)">5 JT</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-outline-secondary w-100 btn-sm ticker-font" onclick="fillAmount('depositAmount', 10000000)">10 JT</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-outline-secondary w-100 btn-sm ticker-font" onclick="fillAmount('depositAmount', 25000000)">25 JT</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-outline-secondary w-100 btn-sm ticker-font" onclick="fillAmount('depositAmount', 50000000)">50 JT</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-outline-secondary w-100 btn-sm ticker-font" onclick="fillAmount('depositAmount', 100000000)">100 JT</button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white opacity-75 small fw-bold">NOMINAL DEPOSIT (Rp)</label>
                                <input type="hidden" id="depositAmount" name="amount" required min="1">
                                <input type="text" id="depositAmountDisplay" class="form-control form-control-lg ticker-font fw-bold text-white" required oninput="formatCurrency(this, 'depositAmount')" placeholder="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white opacity-75 small fw-bold">METODE DEPOSIT (BANK)</label>
                                <select id="depositMethodSelect" name="description" class="form-select text-white mb-2" style="background-color: rgba(0,0,0,0.4); border-color: rgba(16, 185, 129, 0.5);" onchange="toggleCustomInput('depositMethodSelect', 'depositCustomMethod')">
                                    <option value="Deposit via BCA">BCA</option>
                                    <option value="Deposit via BNI">BNI</option>
                                    <option value="Deposit via BRI">BRI</option>
                                    <option value="Deposit via BSI">BSI</option>
                                    <option value="Deposit via Mandiri">Mandiri</option>
                                    <option value="Deposit via CIMB Niaga">CIMB Niaga</option>
                                    <option value="Deposit via Jago">Jago</option>
                                    <option value="Deposit Krom">Krom</option>
                                    <option value="Deposit via Permata">Permata Bank</option>
                                    <option value="Deposit via Superbank">Superbank</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <input type="text" id="depositCustomMethod" class="form-control text-white d-none" style="background-color: rgba(0,0,0,0.4); border-color: rgba(16, 185, 129, 0.5);" placeholder="Ketik nama bank/keterangan lain...">
                            </div>
                        </div>
                        <div class="modal-footer border-top border-emerald-900 p-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">BATAL</button>
                            <button type="submit" class="btn btn-primary-custom btn-sm px-4 rounded-pill shadow-sm">PROSES DEPOSIT</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Withdraw Modal -->
        <div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('investors.withdraw', $investor) }}" method="POST">
                    @csrf
                    <div class="modal-content stat-node border-0 shadow-lg" style="background: #05160c !important;">
                        <div class="modal-header border-bottom border-emerald-900 p-4">
                            <h5 class="modal-title fw-bold text-white ticker-font"><i class="fa-solid fa-arrow-up text-white opacity-75 me-2"></i>PENARIKAN SALDO</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="bg-black bg-opacity-40 border border-emerald-900 border-opacity-50 rounded p-3 mb-3 d-flex align-items-center">
                                <i class="fa-solid fa-wallet fs-4 text-emerald-400 me-3"></i>
                                <div>
                                    <small class="d-block text-white opacity-75 fw-bold" style="font-size: 0.7rem;">SALDO TERSEDIA (BISA DITARIK)</small>
                                    <strong class="fs-5 text-white ticker-font">Rp {{ number_format($investor->available_balance, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white opacity-75 small fw-bold">PILIH NOMINAL CEPAT</label>
                                <div class="row g-2 mb-2">
                                    <div class="col-4"><button type="button" class="btn btn-outline-secondary text-white w-100 btn-sm ticker-font" onclick="fillAmount('withdrawAmount', 1000000)">1 JT</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-outline-secondary text-white w-100 btn-sm ticker-font" onclick="fillAmount('withdrawAmount', 5000000)">5 JT</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-outline-secondary text-white w-100 btn-sm ticker-font" onclick="fillAmount('withdrawAmount', 10000000)">10 JT</button></div>
                                    <div class="col-12"><button type="button" class="btn btn-outline-secondary text-white w-100 btn-sm ticker-font fw-bold shadow-sm" onclick="fillAmount('withdrawAmount', {{ $investor->available_balance }})"><i class="fa-solid fa-coins me-1"></i> TARIK SEMUA SALDO TERSEDIA</button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white opacity-75 small fw-bold">NOMINAL PENARIKAN (Rp)</label>
                                <input type="hidden" id="withdrawAmount" name="amount" required min="1" max="{{ $investor->available_balance }}">
                                <input type="text" id="withdrawAmountDisplay" class="form-control form-control-lg ticker-font fw-bold text-white" required oninput="formatCurrency(this, 'withdrawAmount')" placeholder="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white opacity-75 small fw-bold">TUJUAN PENARIKAN (BANK)</label>
                                <select id="withdrawMethodSelect" name="description" class="form-select text-white mb-2" style="background-color: rgba(0,0,0,0.4); border-color: rgba(16, 185, 129, 0.5);" onchange="toggleCustomInput('withdrawMethodSelect', 'withdrawCustomMethod')">
                                    <option value="Withdraw ke BCA">BCA</option>
                                    <option value="Withdraw ke BNI">BNI</option>
                                    <option value="Withdraw ke BRI">BRI</option>
                                    <option value="Withdraw ke BSI">BSI</option>
                                    <option value="Withdraw ke Mandiri">Mandiri</option>
                                    <option value="Withdraw ke CIMB Niaga">CIMB Niaga</option>
                                    <option value="Withdraw ke Jago">Jago</option>
                                    <option value="Withdraw ke Krom">Krom</option>
                                    <option value="Withdraw ke Permata">Permata Bank</option>
                                    <option value="Withdraw ke Superbank">Superbank</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <input type="text" id="withdrawCustomMethod" class="form-control text-white d-none" style="background-color: rgba(0,0,0,0.4); border-color: rgba(16, 185, 129, 0.5);" placeholder="Ketik nama bank/keterangan lain...">
                            </div>
                        </div>
                        <div class="modal-footer border-top border-emerald-900 p-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">BATAL</button>
                            <button type="submit" class="btn btn-danger btn-sm px-4 rounded-pill shadow-sm">PROSES PENARIKAN</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Account Modal -->
        <div class="modal fade" id="updateAccountModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('investors.update-account', $investor) }}" method="POST">
                    @csrf
                    <div class="modal-content stat-node border-0 shadow-lg" style="background: #05160c !important;">
                        <div class="modal-header border-bottom border-emerald-900 p-4">
                            <h5 class="modal-title fw-bold text-white ticker-font">UBAH AKUN LOGIN</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            @if($investor->user)
                            <div class="mb-3">
                                <label class="form-label text-white opacity-75 small fw-bold">USERNAME LOGIN</label>
                                <input type="text" name="username" class="form-control" value="{{ $investor->user->username }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white opacity-75 small fw-bold">PASSWORD BARU</label>
                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                                <small class="text-emerald-100 opacity-50">Minimal 6 karakter.</small>
                            </div>
                            @else
                            <div class="alert alert-warning border-warning border-opacity-50 bg-warning bg-opacity-10 text-warning">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> Investor ini belum ditautkan dengan Akun Login/User.
                            </div>
                            @endif
                        </div>
                        <div class="modal-footer border-top border-emerald-900 p-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">BATAL</button>
                            @if($investor->user)
                            <button type="submit" class="btn btn-primary-custom btn-sm px-4 rounded-pill shadow-sm">SIMPAN PERUBAHAN</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profit Summary -->
        <div class="card stat-node border-0 shadow-lg mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-white mb-3 small text-uppercase opacity-75 text-center">RINCIAN PROFIT BERSIH PER EMITEN</h6>
                @php
                    $profitPerIpo = [];
                    
                    // Parse actual profit from recorded transactions
                    $profitTransactions = $investor->transactions()->where('type', 'PROFIT')->get();
                    foreach($profitTransactions as $tx) {
                        // Extract IPO code from description "Profit Saham [CODE] - ..."
                        preg_match('/Profit Saham ([A-Z0-9]+) -/', $tx->description, $matches);
                        if (count($matches) > 1) {
                            $ipoCode = $matches[1];
                            if(!isset($profitPerIpo[$ipoCode])) {
                                $profitPerIpo[$ipoCode] = [
                                    'profit' => 0,
                                    'modal' => 0
                                ];
                            }
                            $profitPerIpo[$ipoCode]['profit'] += $tx->amount;
                        }
                    }

                    // Get used capital for those IPOs
                    foreach ($investor->fundings as $funding) {
                        $placement = $funding->placement;
                        if ($placement->allocation && isset($profitPerIpo[$placement->ipo->code])) {
                            $iRatio = $funding->amount_funded / $placement->capital_allocated;
                            $profitPerIpo[$placement->ipo->code]['modal'] += ($placement->allocation->total_used * $iRatio);
                        }
                    }
                    
                    $totalDisplayProfit = $investor->transactions()->where('type', 'PROFIT')->sum('amount');
                @endphp

                <ul class="list-group list-group-flush mb-3 bg-transparent">
                    @forelse($profitPerIpo as $code => $data)
                        @if($data['profit'] > 0)
                        <li class="list-group-item bg-transparent px-0 border-emerald-900 border-opacity-30 text-white">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold"><span class="badge bg-emerald-600 bg-opacity-20 text-emerald-400 border border-emerald-800">{{ $code }}</span></span>
                                <span class="ticker-font fw-bold text-emerald-400">Rp {{ number_format($data['profit'], 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-white opacity-75" style="font-size: 0.7rem;">MODAL YANG DIGUNAKAN:</small>
                                <small class="text-white ticker-font opacity-75">Rp {{ number_format($data['modal'], 0, ',', '.') }}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 bg-emerald-900 bg-opacity-20 rounded border border-emerald-900 border-opacity-50 mt-1">
                                <small class="text-emerald-400 fw-bold" style="font-size: 0.7rem;">TOTAL AKUMULASI:</small>
                                <strong class="text-white ticker-font">Rp {{ number_format($data['profit'] + $data['modal'], 0, ',', '.') }}</strong>
                            </div>
                        </li>
                        @endif
                    @empty
                        <li class="list-group-item bg-transparent text-center text-white opacity-50 px-0 border-0">
                            Belum ada profit terealisasi.
                        </li>
                    @endforelse
                </ul>

                <a href="{{ route('investors.export', $investor) }}" class="btn btn-outline-success btn-sm w-100 rounded-pill border-emerald-500 text-emerald-400 mt-2">
                    <i class="fa-solid fa-file-excel me-1"></i> EXPORT RIWAYAT PROFIT
                </a>
            </div>
        </div>

    </div>

    <div class="col-md-8">
        @php
            $ipos = $fundings->groupBy(function($f) {
                return $f->placement->ipo_id;
            });
        @endphp
        
        <h5 class="fw-bold text-white ticker-font mb-4"><i class="fa-solid fa-layer-group me-2 text-white"></i>DAFTAR PARTISIPASI EMITEN (IPO)</h5>
        
        @if($ipos->isEmpty())
        <div class="card stat-node border-0 shadow-lg text-center py-5">
            <div class="card-body">
                <i class="fa-solid fa-box-open d-block mb-3 fs-1 text-white opacity-50"></i>
                <h5 class="text-white opacity-75">Belum ada partisipasi di Emiten manapun.</h5>
            </div>
        </div>
        @else
        <div class="accordion" id="ipoAccordion">
            @foreach($ipos as $ipoId => $ipoFundings)
                @php
                    $ipo = $ipoFundings->first()->placement->ipo;
                    $totalIpoFunded = $ipoFundings->sum('amount_funded');
                    $totalIpoUsed = 0;
                    foreach($ipoFundings as $f) {
                        $totalUsed = $f->placement->allocation ? $f->placement->allocation->total_used : 0;
                        $investorRatio = $f->amount_funded / $f->placement->capital_allocated;
                        $totalIpoUsed += $totalUsed * $investorRatio;
                    }
                    $totalIpoRefund = $totalIpoFunded - $totalIpoUsed;
                    $hasSale = $ipoFundings->filter(fn($f) => $f->placement->sale)->isNotEmpty();
                @endphp
                
                <div class="accordion-item stat-node border border-emerald-900 border-opacity-30 mb-3 shadow-sm rounded overflow-hidden">
                    <h2 class="accordion-header" id="heading-{{ $ipo->id }}">
                        <button class="accordion-button collapsed bg-black bg-opacity-40 text-white ticker-font px-4 py-3 border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $ipo->id }}" aria-expanded="false" aria-controls="collapse-{{ $ipo->id }}">
                            <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                <div>
                                    <span class="badge bg-emerald-600 bg-opacity-20 text-emerald-400 border border-emerald-800 me-2 px-2 py-1">{{ $ipo->code }}</span>
                                    <span class="fw-bold fs-6">{{ $ipo->name }}</span>
                                </div>
                                <div class="text-end d-none d-sm-block">
                                    <small class="text-white opacity-75 d-block" style="font-size: 0.65rem;">TOTAL DANA</small>
                                    <span class="text-white fw-bold">Rp {{ number_format($totalIpoFunded, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-{{ $ipo->id }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $ipo->id }}" data-bs-parent="#ipoAccordion">
                        <div class="accordion-body p-0 border-top border-emerald-900 border-opacity-30 bg-black bg-opacity-20">
                            
                            <!-- RINGKASAN PENDANAAN & MITRA -->
                            <div class="p-4 border-bottom border-emerald-900 border-opacity-20">
                                <h6 class="fw-bold text-white mb-3 small text-uppercase opacity-75"><i class="fa-solid fa-users me-2"></i>Mitra yang Dimodalin</h6>
                                <div class="row g-3">
                                    @foreach($ipoFundings as $f)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="bg-black bg-opacity-40 border border-emerald-900 border-opacity-50 rounded p-3 h-100">
                                                <div class="fw-bold text-white small mb-1">{{ strtoupper($f->placement->mitraAccount->owner_name) }}</div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="badge bg-dark text-white" style="font-size: 0.6rem;">{{ $f->placement->mitraAccount->platform }}</span>
                                                    <span class="text-emerald-400 fw-bold ticker-font small">Rp {{ number_format($f->amount_funded, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- RIWAYAT PENDANAAN -->
                            <div class="p-4 border-bottom border-emerald-900 border-opacity-20">
                                <h6 class="fw-bold text-white mb-3 small text-uppercase opacity-75"><i class="fa-solid fa-money-bill-wave me-2"></i>Status Alokasi & Refund</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead>
                                            <tr class="opacity-75">
                                                <th class="border-emerald-900">AKUN MITRA</th>
                                                <th class="border-emerald-900 text-end">DISETOR</th>
                                                <th class="border-emerald-900 text-end">TERPAKAI (USED)</th>
                                                <th class="border-emerald-900 text-end">SISA (REFUND)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ipoFundings as $f)
                                            @php
                                                $tUsed = $f->placement->allocation ? $f->placement->allocation->total_used : 0;
                                                $iRatio = $f->amount_funded / $f->placement->capital_allocated;
                                                $iUsed = $tUsed * $iRatio;
                                                $iRefund = $f->amount_funded - $iUsed;
                                            @endphp
                                            <tr class="news-hover">
                                                <td class="border-emerald-900 border-opacity-20 text-white">{{ strtoupper($f->placement->mitraAccount->owner_name) }}</td>
                                                <td class="border-emerald-900 border-opacity-20 text-end fw-bold text-white">Rp {{ number_format($f->amount_funded, 0, ',', '.') }}</td>
                                                <td class="border-emerald-900 border-opacity-20 text-end">
                                                    @if($f->placement->allocation)
                                                        <span class="text-white">Rp {{ number_format($iUsed, 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-white italic opacity-50">Pending</span>
                                                    @endif
                                                </td>
                                                <td class="border-emerald-900 border-opacity-20 text-end">
                                                    @if($f->placement->allocation)
                                                        <span class="text-emerald-400 fw-bold">Rp {{ number_format($iRefund, 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-white opacity-50">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        @if($ipoFundings->first()->placement->allocation)
                                        <tfoot>
                                            <tr class="bg-black bg-opacity-40">
                                                <th class="border-0 text-white text-end">TOTAL:</th>
                                                <th class="border-0 text-white text-end">Rp {{ number_format($totalIpoFunded, 0, ',', '.') }}</th>
                                                <th class="border-0 text-white text-end">Rp {{ number_format($totalIpoUsed, 0, ',', '.') }}</th>
                                                <th class="border-0 text-emerald-400 text-end">Rp {{ number_format($totalIpoRefund, 0, ',', '.') }}</th>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <!-- LAPORAN PROFIT -->
                            @if($ipo->profit_distributed_at)
                            <div class="p-4 bg-emerald-900 bg-opacity-10">
                                <h6 class="fw-bold text-white mb-3 small text-uppercase opacity-75"><i class="fa-solid fa-chart-line me-2"></i>Laporan Profit (Bagi Hasil: Mitra {{ $ipo->mitra_fee_pct }}% / Investor {{ $ipo->platform_fee_pct }}%)</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead>
                                            <tr class="opacity-75">
                                                <th class="border-emerald-900">AKUN MITRA</th>
                                                <th class="border-emerald-900 text-end">PROFIT KOTOR</th>
                                                <th class="border-emerald-900 text-end">JATAH MITRA</th>
                                                <th class="border-emerald-900 text-end">PROFIT BERSIH INVESTOR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $totNet = 0; $totGross = 0; $totMitra = 0; @endphp
                                            @foreach($ipoFundings->filter(fn($f) => $f->placement->sale) as $f)
                                            @php
                                                $iRatio = $f->amount_funded / $f->placement->capital_allocated;
                                                $iGross = $f->placement->sale->net_profit * $iRatio;
                                                $iMitra = $iGross * ($ipo->mitra_fee_pct / 100);
                                                $iNet = $iGross * ($ipo->platform_fee_pct / 100);
                                                
                                                $totGross += $iGross;
                                                $totMitra += $iMitra;
                                                $totNet += $iNet;
                                            @endphp
                                            <tr class="news-hover">
                                                <td class="border-emerald-900 border-opacity-20 text-white">{{ strtoupper($f->placement->mitraAccount->owner_name) }}</td>
                                                <td class="border-emerald-900 border-opacity-20 text-end text-emerald-100 fw-bold">Rp {{ number_format($iGross, 0, ',', '.') }}</td>
                                                <td class="border-emerald-900 border-opacity-20 text-end text-danger">- Rp {{ number_format($iMitra, 0, ',', '.') }}</td>
                                                <td class="border-emerald-900 border-opacity-20 text-end fw-bold text-emerald-400">Rp {{ number_format($iNet, 0, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-black bg-opacity-60">
                                                <th class="border-0 text-white text-end">TOTAL PROFIT (EMITEN INI):</th>
                                                <th class="border-0 text-emerald-100 text-end">Rp {{ number_format($totGross, 0, ',', '.') }}</th>
                                                <th class="border-0 text-danger text-end">- Rp {{ number_format($totMitra, 0, ',', '.') }}</th>
                                                <th class="border-0 text-emerald-400 fw-bold fs-6 text-end shadow-sm">Rp {{ number_format($totNet, 0, ',', '.') }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            @else
                            <div class="p-4 bg-black bg-opacity-40 text-center border-top border-emerald-900 border-opacity-20">
                                <span class="text-white opacity-50 small"><i class="fa-solid fa-clock me-1"></i> Profit belum didistribusikan (masih tertahan di dashboard)</span>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    function formatCurrency(input, targetHiddenId) {
        // Hapus karakter selain angka
        let value = input.value.replace(/[^0-9]/g, '');
        
        // Simpan nilai asli ke hidden input untuk disubmit ke database
        document.getElementById(targetHiddenId).value = value;
        
        // Format dengan titik untuk tampilan
        if (value) {
            input.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            input.value = '';
        }
    }

    function fillAmount(targetHiddenId, amount) {
        // Isi hidden input
        document.getElementById(targetHiddenId).value = amount;
        
        // Isi dan format display input
        let displayInput = document.getElementById(targetHiddenId + 'Display');
        if (displayInput) {
            displayInput.value = new Intl.NumberFormat('id-ID').format(amount);
        }
    }
    function toggleCustomInput(selectId, inputId) {
        let select = document.getElementById(selectId);
        let input = document.getElementById(inputId);
        
        if (select.value === 'Lainnya') {
            input.classList.remove('d-none');
            input.setAttribute('name', 'description');
            select.removeAttribute('name');
            input.required = true;
            input.value = ''; // Set to empty string
        } else {
            input.classList.add('d-none');
            select.setAttribute('name', 'description');
            input.removeAttribute('name');
            input.required = false;
        }
    }
</script>
@endsection
