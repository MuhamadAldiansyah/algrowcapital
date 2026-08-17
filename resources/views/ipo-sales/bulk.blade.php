@extends('layouts.app')

@section('title', 'Input Jual Sekaligus: ' . $ipo->code)

@section('content')
<div class="mb-4">
    <a href="{{ route('ipos.show', $ipo) }}" class="text-decoration-none text-muted">
        <i class="fa-solid fa-arrow-left me-1"></i> Batal & Kembali
    </a>
</div>

<form action="{{ route('ipo-sales.store', $ipo) }}" method="POST">
    @csrf

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom-0 pt-4">
            <h5 class="fw-bold text-white mb-0">Input Harga Jual per Akun (Realisasi)</h5>
            <p class="text-muted small">Setiap akun bisa memiliki harga jual yang berbeda-beda.</p>
        </div>
        <div class="card-body">
            <!-- Global Setter -->
            <div class="row align-items-end g-3 mb-4 p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(16, 185, 129, 0.1);">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-white">Set Harga Jual Masal (Opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="global_sell_price" class="form-control" placeholder="Isi untuk menyamakan semua...">
                        <button type="button" id="apply_global" class="btn btn-outline-success">Terapkan</button>
                    </div>
                </div>
                <div class="col-md-8 text-md-end">
                    <button type="submit" class="btn btn-primary-custom px-5 py-2 fw-bold shadow-sm">
                        <i class="fa-solid fa-check-circle me-2"></i>Simpan Seluruh Hasil Penjualan
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="text-uppercase small">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Akun Mitra</th>
                            <th>Pemodal / Investor</th>
                            <th>Jatah (Lot)</th>
                            <th>Modal Terpakai (Rp)</th>
                            <th style="width: 200px;">Harga Jual (Rp)</th>
                            <th>Total Return (Rp)</th>
                            <th class="pe-3">Net Profit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 1; @endphp
                        @foreach($placements as $index => $placement)
                        @if($placement->allocation->lot_allocated > 0)
                        <tr class="sale-row" data-index="{{ $index }}" data-cost="{{ $placement->allocation->total_used }}" data-lot="{{ $placement->allocation->lot_allocated }}">
                            <td class="ps-3 text-white-50 fw-bold">{{ $counter++ }}</td>
                            <td>
                                <input type="hidden" name="allocations[{{ $index }}][placement_id]" value="{{ $placement->id }}">
                                <div class="fw-bold text-white">{{ strtoupper($placement->mitraAccount->owner_name) }}</div>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 small fw-normal">{{ strtoupper($placement->mitraAccount->platform) }}</span>
                            </td>
                            <td>
                                @foreach($placement->fundings as $f)
                                    <div class="small mb-1">
                                        <i class="fa-solid fa-user-tag me-1 text-success opacity-75"></i> 
                                        <span class="text-white-50 fw-bold">{{ strtoupper($f->investor->name) }}</span>
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <span class="fw-bold text-white">{{ number_format($placement->allocation->lot_allocated, 0, ',', '.') }}</span> Lot
                            </td>
                            <td>
                                <span class="text-muted small">Rp {{ number_format($placement->allocation->total_used, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <div class="input-group input-group-sm mb-0">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" 
                                           name="allocations[{{ $index }}][sell_price]" 
                                           class="form-control sell-input fw-bold shadow-sm" 
                                           id="sell_price_{{ $index }}"
                                           value="{{ $placement->sale ? $placement->sale->sell_price : '' }}" 
                                           placeholder="0"
                                           required>
                                </div>
                            </td>
                            <td>
                                <span id="return_label_{{ $index }}" class="text-white small">Rp 0</span>
                            </td>
                            <td class="pe-3">
                                <span id="profit_label_{{ $index }}" class="fw-bold">Rp 0</span>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<script>
    $(document).ready(function() {
        function calculateRow(index) {
            const row = $(`.sale-row[data-index="${index}"]`);
            const cost = parseFloat(row.data('cost')) || 0;
            const lot = parseFloat(row.data('lot')) || 0;
            const sellPrice = parseFloat($(`#sell_price_${index}`).val()) || 0;
            
            const totalReturn = lot * 100 * sellPrice;
            const taxFee = totalReturn * 0.0025; // 0.25% pajak final
            const netProfit = totalReturn - cost - taxFee;

            $(`#return_label_${index}`).text('Rp ' + totalReturn.toLocaleString('id-ID'));
            $(`#profit_label_${index}`).text('Rp ' + netProfit.toLocaleString('id-ID'));
            
            if(netProfit < 0) {
                $(`#profit_label_${index}`).addClass('text-danger').removeClass('text-success-custom');
            } else if(netProfit > 0) {
                $(`#profit_label_${index}`).addClass('text-success-custom').removeClass('text-danger');
            } else {
                $(`#profit_label_${index}`).removeClass('text-danger text-success-custom');
            }
        }

        // Init calculations
        $('.sale-row').each(function() {
            calculateRow($(this).data('index'));
        });

        // Event listeners
        $('.sell-input').on('input', function() {
            calculateRow($(this).closest('.sale-row').data('index'));
        });

        // Apply Global Harga Jual
        $('#apply_global').on('click', function() {
            const globalPrice = $('#global_sell_price').val();
            if(globalPrice) {
                let appliedCount = 0;
                let skippedCount = 0;
                
                $('.sell-input').each(function() {
                    const currentValue = $(this).val();
                    // Hanya terapkan jika masih 0 atau kosong (belum diisi mitra)
                    if (currentValue === '' || currentValue === '0' || parseFloat(currentValue) === 0) {
                        $(this).val(globalPrice);
                        calculateRow($(this).closest('.sale-row').data('index'));
                        appliedCount++;
                    } else {
                        skippedCount++;
                    }
                });

                // Tampilkan notifikasi
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terapkan Masal!',
                        text: `Harga jual berhasil diterapkan ke ${appliedCount} akun. ${skippedCount} akun dilewati karena sudah terisi.`,
                        timer: 2500,
                        showConfirmButton: false,
                        background: '#198754',
                        color: '#fff',
                        iconColor: '#fff',
                    });
                }
            }
        });
    });
</script>
@endsection
@endsection
