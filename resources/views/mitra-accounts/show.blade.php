@extends('layouts.app')

@section('title', 'Detail Akun Mitra')

@section('content')
<div class="row g-4 mb-5">
    <div class="col-md-8">
        <div class="card stat-node border-0 shadow-lg mb-4">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 mb-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <h5 class="fw-bold text-white ticker-font mb-0 text-center text-sm-start"><i class="fa-solid fa-id-card me-2 text-emerald-500"></i>INFORMASI AKUN</h5>
                <span class="badge {{ $mitraAccount->status == 'aktif' ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' }} px-3 py-2 rounded-pill shadow-sm mb-2 mb-sm-0" style="font-size: 0.65rem;">
                    {{ strtoupper($mitraAccount->status) }}
                </span>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row mb-4">
                    <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">NAMA PEMILIK</div>
                    <div class="col-sm-8 text-white fw-bold">{{ $mitraAccount->owner_name }}</div>
                </div>
                <div class="row mb-4">
                    <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">PLATFORM / USERNAME</div>
                    <div class="col-sm-8">
                        <span class="badge bg-black bg-opacity-40 text-emerald-400 border border-emerald-900 border-opacity-50 px-3 py-2 me-2 shadow-sm">{{ strtoupper($mitraAccount->platform) }}</span>
                        <strong class="text-white ticker-font">{{ $mitraAccount->username }}</strong>
                    </div>
                </div>
                <div class="row mb-4 align-items-center">
                    <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">PASSWORD</div>
                    <div class="col-sm-8">
                        <div class="input-group input-group-sm w-75 shadow-sm">
                            <input type="password" readonly class="form-control bg-black bg-opacity-20 border-emerald-900 border-opacity-50 text-white ticker-font" id="pass_view" value="{{ \Illuminate\Support\Facades\Crypt::decryptString($mitraAccount->password) }}">
                            <button class="btn btn-outline-primary-custom" onclick="togglePassword('pass_view', 'pass_view_icon')">
                                <i id="pass_view_icon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row mb-4 align-items-center">
                    <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">PIN</div>
                    <div class="col-sm-8">
                        @if($mitraAccount->pin)
                        <div class="input-group input-group-sm w-75 shadow-sm">
                            <input type="password" readonly class="form-control bg-black bg-opacity-20 border-emerald-900 border-opacity-50 text-white ticker-font" id="pin_view" value="{{ \Illuminate\Support\Facades\Crypt::decryptString($mitraAccount->pin) }}">
                            <button class="btn btn-outline-primary-custom" onclick="togglePassword('pin_view', 'pin_view_icon')">
                                <i id="pin_view_icon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        @else
                        <span class="text-emerald-500 opacity-50">-</span>
                        @endif
                    </div>
                </div>
                <hr class="border-emerald-900 border-opacity-30">
                <div class="row mb-4">
                    <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">DEVICE TYPE</div>
                    <div class="col-sm-8 text-white fw-bold"><i class="fa-solid fa-mobile-screen-button me-2 opacity-50"></i>{{ $mitraAccount->device ?? '-' }}</div>
                </div>
                <div class="row mb-0">
                    <div class="col-sm-4 text-emerald-500 small fw-bold opacity-75">NIK / RDN RECORD</div>
                    <div class="col-sm-8 text-white">
                        <div class="mb-1"><span class="text-emerald-500 opacity-50 small">NIK:</span> <span class="fw-bold ticker-font">{{ $mitraAccount->nik ?? '-' }}</span></div>
                        <div><span class="text-emerald-500 opacity-50 small">RDN:</span> <span class="fw-bold ticker-font">{{ $mitraAccount->bank_rdn ?? '-' }} ({{ $mitraAccount->rdn_account ?? '-' }})</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card stat-node border-0 shadow-lg mt-4">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 mb-3 px-4">
                <h5 class="fw-bold text-white ticker-font"><i class="fa-solid fa-history me-2 text-emerald-500"></i>RIWAYAT PARTISIPASI IPO</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>TICKER</th>
                                <th>LOT DIDAPAT</th>
                                <th>MODAL TERPAKAI</th>
                                <th>NET PROFIT</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mitraAccount->placements as $placement)
                                @php
                                    $allocation = $placement->allocation;
                                    $sale = $placement->sale;
                                    $profit = 0;
                                    if($allocation && $sale) {
                                        $profit = $sale->net_profit * 0.5; // Mitra Share (50%)
                                    }
                                @endphp
                            <tr class="news-hover">
                                <td>
                                    <span class="badge bg-black bg-opacity-40 border border-emerald-900 text-white ticker-font px-3 py-2 shadow-sm mb-1">{{ $placement->ipo->code }}</span>
                                    <div class="small text-emerald-500 opacity-50 mt-1" style="font-size: 0.65rem;">{{ $placement->ipo->name }}</div>
                                </td>
                                <td>
                                    @if($allocation)
                                        <span class="fw-bold text-white">{{ number_format($allocation->lot_allocated, 0, ',', '.') }}</span> <small class="text-emerald-500 opacity-50">LOT</small>
                                    @else
                                        <span class="text-emerald-500 italic small opacity-50">Pending allotment</span>
                                    @endif
                                </td>
                                <td class="ticker-font">
                                    @if($allocation)
                                        Rp {{ number_format($allocation->total_used, 0, ',', '.') }}
                                    @else
                                        <span class="text-emerald-500 opacity-50">-</span>
                                    @endif
                                </td>
                                <td class="ticker-font">
                                    @if($allocation && $sale)
                                        <span class="fw-bold {{ $profit >= 0 ? 'text-emerald-400' : 'text-danger' }}">
                                            Rp {{ number_format($profit, 0, ',', '.') }}
                                        </span>
                                    @elseif($allocation)
                                        <span class="text-emerald-500 italic small opacity-50">Waiting for sale</span>
                                    @else
                                        <span class="text-emerald-500 opacity-50">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sale)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 0.6rem;">SELESAI (PROFIT)</span>
                                    @elseif($allocation)
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1" style="font-size: 0.6rem;">MENUNGGU JUAL</span>
                                    @else
                                        <span class="badge bg-black bg-opacity-40 text-emerald-500 border border-emerald-900 border-opacity-30 px-2 py-1" style="font-size: 0.6rem;">PLACEMENT</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-emerald-500 opacity-50">
                                    <i class="fa-solid fa-folder-open d-block mb-3 fs-3"></i>
                                    Belum ada riwayat partisipasi IPO.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-node border-0 shadow-lg mb-4">
            <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 px-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center mb-3 gap-3">
                    <h5 class="fw-bold text-white ticker-font mb-0 text-center text-lg-start"><i class="fa-solid fa-chart-line me-2 text-emerald-500"></i>PERTUMBUHAN PROFIT</h5>
                    <div class="btn-group w-100 w-lg-auto" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary-custom chart-filter active" data-range="ALL">ALL</button>
                        <button type="button" class="btn btn-sm btn-outline-primary-custom chart-filter" data-range="1W">1W</button>
                        <button type="button" class="btn btn-sm btn-outline-primary-custom chart-filter" data-range="1M">1M</button>
                        <button type="button" class="btn btn-sm btn-outline-primary-custom chart-filter" data-range="1Y">1Y</button>
                        <button type="button" class="btn btn-sm btn-outline-primary-custom chart-filter" data-range="5Y">5Y</button>
                    </div>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <canvas id="profitChart" height="250"></canvas>
            </div>
        </div>


        <div class="d-grid gap-2">
            <a href="{{ route('mitra-accounts.edit', $mitraAccount) }}" class="btn btn-warning shadow-sm rounded-pill py-2 fw-bold text-dark">
                <i class="fa-solid fa-edit me-2"></i>EDIT AKUN
            </a>
            <a href="{{ route('mitra-accounts.index') }}" class="btn btn-outline-primary-custom shadow-sm rounded-pill py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>KEMBALI KE DAFTAR
            </a>
        </div>


    </div>
</div>

<style>
.hover-emerald:hover {
    background-color: rgba(16, 185, 129, 0.1) !important;
    color: #10b981 !important;
    border-color: #10b981 !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function togglePassword(inputId, iconId) {
        var x = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (x.type === "password") {
            x.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            x.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    const rawData = {!! json_encode($chartDataRaw) !!};
    let profitChart = null;

    function renderChart(range) {
        const now = new Date();
        let startDate = new Date('1970-01-01');

        if (range === '1W') {
            startDate = new Date(now.setDate(now.getDate() - 7));
        } else if (range === '1M') {
            startDate = new Date(now.setMonth(now.getMonth() - 1));
        } else if (range === '1Y') {
            startDate = new Date(now.setFullYear(now.getFullYear() - 1));
        } else if (range === '5Y') {
            startDate = new Date(now.setFullYear(now.getFullYear() - 5));
        }

        let filteredLabels = ['Mulai'];
        let filteredData = [0];
        let cumulative = 0;

        rawData.forEach(item => {
            const itemDate = new Date(item.date);
            if (itemDate >= startDate) {
                cumulative += parseFloat(item.profit);
                filteredLabels.push(item.label);
                filteredData.push(cumulative);
            }
        });

        var ctx = document.getElementById('profitChart').getContext('2d');
        
        // Create Premium Gradient
        var gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.5)'); // Bright emerald
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)'); // Transparent

        if (profitChart) {
            profitChart.destroy();
        }

        profitChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: filteredLabels,
                datasets: [{
                    label: 'Total Profit',
                    data: filteredData,
                    borderColor: '#34d399',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#05160c',
                    pointBorderColor: '#34d399',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#34d399',
                    pointHoverBorderColor: '#ffffff',
                    fill: true,
                    tension: 0.4,
                    shadowColor: 'rgba(52, 211, 153, 0.5)',
                    shadowBlur: 10,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(5, 22, 12, 0.9)',
                        titleColor: '#a7f3d0',
                        bodyColor: '#ffffff',
                        borderColor: 'rgba(16, 185, 129, 0.3)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let val = context.parsed.y;
                                let prefix = val >= 0 ? '+' : '';
                                return prefix + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(16, 185, 129, 0.05)',
                            borderDash: [5, 5]
                        },
                        border: { display: false },
                        ticks: {
                            color: '#6ee7b7',
                            font: { family: "'Courier New', Courier, monospace", size: 10 },
                            callback: function(value) {
                                if(value >= 1000000) return (value / 1000000) + 'M';
                                if(value >= 1000) return (value / 1000) + 'K';
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#6ee7b7',
                            font: { size: 10 },
                            maxRotation: 0
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderChart('ALL');

        const filterBtns = document.querySelectorAll('.chart-filter');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                renderChart(this.getAttribute('data-range'));
            });
        });
    });
</script>
@endsection
