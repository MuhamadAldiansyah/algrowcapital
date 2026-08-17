@extends('layouts.app')

@section('title', 'Alokasi Modal & Bagi Hasil: ' . $ipo->code)

@section('content')
<div class="mb-4">
    <a href="{{ route('ipos.show', $ipo) }}" class="text-decoration-none text-emerald-500 hover-emerald">
        <i class="fa-solid fa-arrow-left me-1"></i> Batal & Kembali
    </a>
</div>

<form action="{{ route('ipos.store-placement', $ipo) }}" method="POST" id="placement-form">
    @csrf
    
    @if($errors->has('error'))
    <div class="alert alert-danger shadow-sm border-0 mb-4 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first('error') }}
    </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card stat-node border-0 shadow-lg mb-5">
                <div class="card-header bg-black bg-opacity-20 pt-4 pb-0 border-bottom border-emerald-900 px-4">
                    <h5 class="fw-bold text-white ticker-font"><i class="fa-solid fa-circle-nodes me-2 text-emerald-500"></i>ALOKASI MODAL & BAGI HASIL</h5>
                    <p class="text-emerald-500 opacity-50 small mb-3">Tentukan siapa yang memodali akun dan berapa porsi keuntungan untuk Mitra & Investor.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-black bg-opacity-20">
                                <tr>
                                    <th style="width: 220px;" class="ps-4">AKUN MITRA</th>
                                    <th style="width: 180px;">JATAH EST. (LOT)</th>
                                    <th class="pe-4">DONOR INVESTOR / BAGI HASIL %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $index => $account)
                                @php
                                    $existing = $ipo->placements->where('mitra_account_id', $account->id)->first();
                                    $existingFundings = $existing ? $existing->fundings : collect();
                                    if($existingFundings->isEmpty()) {
                                        $existingFundings->push(new \App\Models\InvestorFunding(['amount_funded' => 0, 'share_pct' => 50]));
                                    }
                                @endphp
                                <tr class="mitra-row border-bottom border-emerald-900 border-opacity-20" data-index="{{ $index }}">
                                    <td class="ps-4">
                                        <input type="hidden" name="allocations[{{ $index }}][account_id]" value="{{ $account->id }}">
                                        <div class="fw-bold text-white ticker-font mb-1">{{ strtoupper($account->owner_name) }}</div>
                                        <span class="badge bg-black bg-opacity-40 text-emerald-500 border border-emerald-900 border-opacity-30 small fw-normal">{{ strtoupper($account->platform) }}</span>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm mb-2 shadow-sm">
                                            <input type="number" 
                                                   id="lot_{{ $index }}"
                                                   class="form-control lot-input fw-bold text-white ticker-font bg-black bg-opacity-20 border-emerald-900" 
                                                   value="{{ $existing ? $existing->est_lot : 0 }}">
                                            <span class="input-group-text bg-emerald-900 bg-opacity-40 text-emerald-400 border-emerald-900 fw-bold">LOT</span>
                                        </div>
                                        <div class="text-center bg-black bg-opacity-20 p-2 rounded-3 border border-emerald-900 border-opacity-30">
                                            <small class="text-emerald-500 d-block opacity-50" style="font-size: 0.65rem;">TOTAL MODAL</small>
                                            <span class="text-white ticker-font small fw-bold total-capital-label">Rp 0</span>
                                        </div>
                                    </td>

                                    <td class="bg-black bg-opacity-10 p-4 pe-4">
                                        <div class="investor-list" id="investor_list_{{ $index }}">
                                            @foreach($existingFundings as $fIndex => $funding)
                                            <div class="investor-item row g-3 mb-3 align-items-center bg-black bg-opacity-40 p-3 rounded-3 border border-emerald-900 border-opacity-50 shadow-sm mx-0">
                                                <div class="col-md-5">
                                                    <label class="form-label mb-1">INVESTOR</label>
                                                    <select name="allocations[{{ $index }}][investors][{{ $fIndex }}][investor_id]" class="form-select form-select-sm investor-select">
                                                        <option value="">-- PILIH INVESTOR --</option>
                                                        @foreach($investors as $investor)
                                                            <option value="{{ $investor->id }}" 
                                                                    data-balance="{{ $investor->available_balance }}"
                                                                    data-initial-funding="{{ $funding->investor_id == $investor->id ? $funding->amount_funded : 0 }}"
                                                                    {{ $funding->investor_id == $investor->id ? 'selected' : '' }}>
                                                                {{ strtoupper($investor->name) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="balance-label x-small mt-2 px-1"></div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label mb-1">MODAL (RP)</label>
                                                    <input type="number" 
                                                           name="allocations[{{ $index }}][investors][{{ $fIndex }}][capital]" 
                                                           class="form-control form-select-sm capital-input ticker-font fw-bold" 
                                                           value="{{ $funding->amount_funded }}" 
                                                           placeholder="0">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label mb-1">BAGI HASIL</label>
                                                    <select name="allocations[{{ $index }}][investors][{{ $fIndex }}][share_pct]" 
                                                            class="form-select form-select-sm investor-share-input ticker-font">
                                                        @foreach([50, 70, 100] as $pct)
                                                            <option value="{{ $pct }}" {{ $funding->share_pct == $pct ? 'selected' : '' }}>{{ $pct }}%</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-1 d-flex justify-content-end align-items-center pt-3">
                                                    @if($fIndex > 0)
                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-investor rounded-circle"><i class="fa-solid fa-xmark"></i></button>
                                                    @endif
                                                </div>
                                                <div class="col-12 mt-1 validation-msg x-small text-danger fw-bold" style="display:none"></div>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3 ps-1">
                                            <button type="button" class="btn btn-sm btn-link text-decoration-none text-emerald-400 fw-bold add-investor p-0" data-mitra-index="{{ $index }}">
                                                <i class="fa-solid fa-plus-circle me-1"></i> TAMBAH INVESTOR
                                            </button>
                                            
                                            <div class="confirm-actions">
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary-custom px-4 rounded-pill fw-bold btn-confirm-row {{ $existing ? 'd-none' : '' }}" 
                                                        data-mitra-index="{{ $index }}"
                                                        data-account-name="{{ $account->owner_name }}">
                                                    <i class="fa-solid fa-shield-check me-1"></i> KONFIRMASI DANA
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-info px-4 rounded-pill btn-unlock-row {{ $existing ? '' : 'd-none' }}" 
                                                        data-mitra-index="{{ $index }}">
                                                    <i class="fa-solid fa-lock me-1"></i> TERKUNCI (EDIT MODAL)
                                                </button>
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger px-4 rounded-pill btn-reset-row ms-2 {{ $existing ? '' : 'd-none' }}" 
                                                        data-mitra-index="{{ $index }}"
                                                        data-account-id="{{ $account->id }}"
                                                        data-account-name="{{ $account->owner_name }}">
                                                    <i class="fa-solid fa-rotate-left me-1"></i> RESET
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mb-5 d-flex justify-content-center">
                <a href="{{ route('ipos.show', $ipo) }}" class="btn btn-primary-custom px-5 py-3 rounded-pill shadow-lg fw-bold ticker-font">
                    <i class="fa-solid fa-circle-check me-2 fs-5"></i> SELESAI & SIMPAN ALL DASHBOARD
                </a>
            </div>
        </div>
    </div>
</form>

<style>
    .hover-emerald:hover { color: #10b981 !important; }
    .mitra-row:last-child { border-bottom: none !important; }
    .balance-label { font-size: 0.65rem; }
    .investor-item { transition: all 0.3s ease; }
    .investor-item:hover { border-color: rgba(16, 185, 129, 0.4) !important; background: rgba(16, 185, 129, 0.05) !important; }
    .swal2-popup { background: #05160c !important; color: white !important; border: 1px solid #064e3b !important; border-radius: 20px !important; }
    .swal2-title { color: white !important; font-family: 'JetBrains Mono', monospace !important; }
    .swal2-html-container { color: rgba(255, 255, 255, 0.7) !important; }
</style>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const ipoPrice = {{ $ipo->price }};
    const globalInitialFunding = {!! json_encode($totalsByInvestor) !!};
    
    $(document).ready(function() {
        
        // --- Dynamic Row Management ---
        $(document).on('click', '.add-investor', function() {
            const mitraIndex = $(this).data('mitra-index');
            const list = $(`#investor_list_${mitraIndex}`);
            const rowCount = list.find('.investor-item').length;
            
            // Clone first row and clear inputs
            const firstRow = list.find('.investor-item').first();
            const newRow = firstRow.clone();
            
            // Update names for the new row
            newRow.find('select, input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[investors]\[\d+]/, `[investors][${rowCount}]`);
                    $(this).attr('name', newName).val('');
                }
            });
            
            // Add remove button if not exists
            if (newRow.find('.remove-investor').length === 0) {
                newRow.find('.col-md-1').append('<button type="button" class="btn btn-sm btn-outline-danger border-0 remove-investor rounded-circle"><i class="fa-solid fa-xmark"></i></button>');
            }
            
            list.append(newRow);
            checkAllBalances();
        });

        $(document).on('click', '.remove-investor', function() {
            const row = $(this).closest('.investor-item');
            const mitraRow = $(this).closest('.mitra-row');
            row.remove();
            calculateMitraRow(mitraRow.data('index'));
            checkAllBalances();
        });

        // --- Calculation Logic ---

        function calculateMitraRow(index) {
            const row = $(`.mitra-row[data-index="${index}"]`);
            let totalCapital = 0;
            
            row.find('.capital-input').each(function() {
                totalCapital += parseFloat($(this).val()) || 0;
            });
            
            row.find('.total-capital-label').text('Rp ' + totalCapital.toLocaleString('id-ID'));
            
            // Update Lot
            const lot = Math.floor(totalCapital / ipoPrice / 100);
            row.find('.lot-input').val(lot);
        }

        // Init calculations
        $('.mitra-row').each(function() {
            calculateMitraRow($(this).data('index'));
        });

        $(document).on('input', '.capital-input', function() {
            const index = $(this).closest('.mitra-row').data('index');
            calculateMitraRow(index);
        });

        $(document).on('input', '.lot-input', function() {
            const index = $(this).closest('.mitra-row').data('index');
            const lot = parseFloat($(this).val()) || 0;
            const newTotalCapital = lot * ipoPrice * 100;
            
            const firstCapitalInput = $(this).closest('.mitra-row').find('.capital-input').first();
            firstCapitalInput.val(newTotalCapital);
            calculateMitraRow(index);
            checkAllBalances();
        });

        // --- Global Balance Guard Logic ---

        function checkAllBalances() {
            const investorTotals = {};
            const investorPools = {};
            
            $('.investor-item').each(function() {
                const select = $(this).find('.investor-select');
                const input = $(this).find('.capital-input');
                const id = select.val();
                if (!id) return;

                const cap = parseFloat(input.val()) || 0;
                investorTotals[id] = (investorTotals[id] || 0) + cap;

                if (!investorPools[id]) {
                    const selected = select.find('option:selected');
                    const balance = parseFloat(selected.data('balance')) || 0;
                    const initialTotal = parseFloat(globalInitialFunding[id]) || 0;
                    investorPools[id] = balance + initialTotal;
                }
            });

            $('.investor-item').each(function() {
                const select = $(this).find('.investor-select');
                const input = $(this).find('.capital-input');
                const lbl = $(this).find('.balance-label');
                const msg = $(this).find('.validation-msg');
                const id = select.val();

                if (!id) {
                    lbl.text('');
                    msg.hide();
                    input.removeClass('is-invalid');
                    return;
                }

                const totalSpent = investorTotals[id];
                const maxAllowed = investorPools[id];
                const remaining = maxAllowed - totalSpent;

                lbl.html(`SISA SALDO: <span class="fw-bold ticker-font ${remaining < 0 ? 'text-danger' : 'text-emerald-400'}">Rp ${remaining.toLocaleString('id-ID')}</span>`);

                if (totalSpent > maxAllowed) {
                    input.addClass('is-invalid');
                    msg.html(`<i class="fa-solid fa-circle-xmark me-1"></i>TOTAL INVESTOR INI (Rp ${totalSpent.toLocaleString('id-ID')}) MELEBIHI SALDO!`).show();
                } else {
                    input.removeClass('is-invalid');
                    msg.hide();
                }
            });
        }

        $(document).on('change', '.investor-select', function() {
            checkAllBalances();
        });

        $(document).on('input', '.capital-input', function() {
            checkAllBalances();
        });

        checkAllBalances();

        // --- Row Confirmation ---

        $(document).on('click', '.btn-confirm-row', function() {
            const btn = $(this);
            const index = btn.data('mitra-index');
            const accName = btn.data('account-name').toUpperCase();
            const row = $(`.mitra-row[data-index="${index}"]`);
            
            if (row.find('.is-invalid').length > 0) {
                Swal.fire('Error', 'Selesaikan error saldo terlebih dahulu!', 'error');
                return;
            }

            const totalCap = row.find('.total-capital-label').text();
            const estLot = row.find('.lot-input').val();
            const ticker = "{{ $ipo->code }}";

            Swal.fire({
                title: 'KONFIRMASI ALOKASI',
                html: `Apakah anda yakin ingin membeli <b>${ticker}</b> senilai <b>${totalCap}</b> (${estLot} Lot) di akun <b>${accName}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'YA, FIX BELI!',
                cancelButtonText: 'BATAL'
            }).then((result) => {
                if (result.isConfirmed) {
                    saveRow(index);
                }
            });
        });

        $(document).on('click', '.btn-reset-row', function() {
            const btn = $(this);
            const accId = btn.data('account-id');
            const accName = btn.data('account-name').toUpperCase();
            const url = "{{ route('ipos.destroy-row-placement', [$ipo->id, ':accId']) }}".replace(':accId', accId);

            Swal.fire({
                title: 'RESET ALOKASI?',
                text: `Seluruh dana investor untuk akun ${accName} akan dikembalikan ke saldo wallet mereka.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'YA, RESET DATA',
                cancelButtonText: 'BATAL'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            Swal.fire({title:'BERHASIL!', text:response.message, icon:'success', timer:1500, showConfirmButton:false}).then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('GAGAL!', 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });

        function saveRow(index) {
            const row = $(`.mitra-row[data-index="${index}"]`);
            const btn = row.find('.btn-confirm-row');
            
            let totalInvestorShare = 0;
            row.find('.investor-share-input').each(function() {
                totalInvestorShare += parseFloat($(this).val()) || 0;
            });

            if (totalInvestorShare > 100) {
                Swal.fire('Error', 'Total bagi hasil investor tidak boleh melebihi 100%!', 'error');
                return;
            }

            const calculatedMitraShare = 100 - totalInvestorShare;
            
            const formData = {
                _token: "{{ csrf_token() }}",
                account_id: row.find('input[name*="[account_id]"]').val(),
                est_lot: row.find('.lot-input').val(),
                mitra_share_pct: calculatedMitraShare,
                investors: []
            };

            row.find('.investor-item').each(function() {
                formData.investors.push({
                    investor_id: $(this).find('.investor-select').val(),
                    capital: $(this).find('.capital-input').val(),
                    share_pct: $(this).find('.investor-share-input').val()
                });
            });

            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>PROCESSING...');

            $.ajax({
                url: "{{ route('ipos.store-row-placement', $ipo) }}",
                method: "POST",
                data: formData,
                success: function(response) {
                    Swal.fire({title:'TERSIPAN!', text:response.message, icon:'success', timer:1500, showConfirmButton:false}).then(() => {
                        location.reload(); 
                    });
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-shield-check me-1"></i> KONFIRMASI DANA');
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                    Swal.fire('GAGAL!', msg, 'error');
                }
            });
        }

        function lockRow(index) {
            const row = $(`.mitra-row[data-index="${index}"]`);
            row.find('.investor-select, .capital-input, .investor-share-input, .add-investor, .remove-investor').prop('disabled', true);
            row.find('.lot-input').prop('disabled', false);
            row.css('background', 'rgba(16, 185, 129, 0.05)');
        }

        $(document).on('click', '.btn-unlock-row', function() {
            const index = $(this).data('mitra-index');
            const row = $(`.mitra-row[data-index="${index}"]`);
            
            Swal.fire({
                title: 'BUKA KUNCI MODAL?',
                text: "Anda perlu melakukan konfirmasi ulang jika dana diubah.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0ea5e9',
                confirmButtonText: 'YA, BUKA KUNCI'
            }).then((result) => {
                if (result.isConfirmed) {
                    row.find('.investor-select, .capital-input, .investor-share-input, .add-investor, .remove-investor').prop('disabled', false);
                    row.css('background', 'transparent');
                    $(this).addClass('d-none');
                    row.find('.btn-confirm-row').removeClass('d-none');
                }
            });
        });

        $('.mitra-row').each(function() {
            const index = $(this).data('index');
            const hasData = $(this).find('.btn-unlock-row').is(':visible');
            if (hasData) lockRow(index);
        });
    });
</script>
@endsection
@endsection
